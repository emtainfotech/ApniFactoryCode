<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Models\Transection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentGatewayService
{
    /**
     * Supported gateway providers.
     */
    public const GATEWAY_RAZORPAY = 'razorpay';
    public const GATEWAY_PAYU     = 'payu';

    /**
     * Internal payment states.
     */
    public const STATUS_INITIATED  = 'initiated';
    public const STATUS_PENDING    = 'pending';
    public const STATUS_SUCCESSFUL = 'successful';
    public const STATUS_FAILED     = 'failed';
    public const STATUS_CANCELLED  = 'cancelled';
    public const STATUS_REFUNDED   = 'refunded';

    /**
     * Verification states.
     */
    public const VERIFY_UNVERIFIED = 'unverified';
    public const VERIFY_VERIFIED   = 'verified';
    public const VERIFY_FAILED     = 'failed';
    public const VERIFY_TAMPERED   = 'tampered';

    /**
     * Reconciliation states.
     */
    public const RECON_PENDING       = 'pending';
    public const RECON_MATCHED       = 'matched';
    public const RECON_MISMATCH      = 'mismatch';
    public const RECON_MANUAL_REVIEW = 'manual_review';

    /**
     * Initiate payment for a parent order using authoritative backend data.
     */
    public function initiatePayment(
        Order $order,
        string $gatewayName = self::GATEWAY_RAZORPAY,
        ?string $idempotencyKey = null,
        ?int $customerId = null
    ): array {
        // 1. Verify eligibility
        if ($order->isPaid()) {
            throw ValidationException::withMessages([
                'order' => ["Order #{$order->orderno} is already paid."],
            ]);
        }

        // 2. Revalidate required payable amount using trusted backend data
        $amount = round((float)$order->grandtotal, 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Payable amount must be greater than zero.'],
            ]);
        }

        return DB::transaction(function () use ($order, $gatewayName, $idempotencyKey, $customerId, $amount) {
            // 3. Check for existing idempotent transaction
            if ($idempotencyKey) {
                $existingTxn = Transection::where('idempotency_key', $idempotencyKey)->first();
                if ($existingTxn) {
                    if ($existingTxn->isSuccessful()) {
                        throw ValidationException::withMessages([
                            'idempotency' => ['A successful payment has already been recorded for this transaction.'],
                        ]);
                    }
                    return $this->formatInitiationResponse($existingTxn, $order);
                }
            }

            // Check if there is already a recent pending transaction for this order
            $activeTxn = Transection::where('order_id', $order->id)
                ->where('status', self::STATUS_PENDING)
                ->where('created_at', '>=', now()->subMinutes(15))
                ->first();

            if ($activeTxn && !$idempotencyKey) {
                return $this->formatInitiationResponse($activeTxn, $order);
            }

            $paymentRef = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));
            $gatewayOrderId = null;
            $gatewayParams = [];

            // 4. Create gateway order / payment request
            if ($gatewayName === self::GATEWAY_RAZORPAY) {
                $key = config('services.razorpay.key') ?: env('RAZORPAY_KEY', 'rzp_test_placeholder');
                $secret = config('services.razorpay.secret') ?: env('RAZORPAY_SECRET', 'secret_placeholder');

                $gatewayOrderId = 'order_' . strtoupper(Str::random(14));

                // If live/configured credentials exist, call Razorpay API
                if (!str_contains($key, 'placeholder') && !str_contains($key, 'test')) {
                    try {
                        $response = Http::withBasicAuth($key, $secret)
                            ->post('https://api.razorpay.com/v1/orders', [
                                'amount'   => (int)round($amount * 100), // paise
                                'currency' => 'INR',
                                'receipt'  => $order->orderno,
                                'notes'    => [
                                    'order_id'    => $order->id,
                                    'payment_ref' => $paymentRef,
                                ],
                            ]);

                        if ($response->successful()) {
                            $gatewayOrderId = $response->json('id') ?? $gatewayOrderId;
                        }
                    } catch (\Throwable $e) {
                        Log::error("Razorpay order creation failed: " . $e->getMessage());
                    }
                }

                $gatewayParams = [
                    'key'              => $key,
                    'amount'           => (int)round($amount * 100),
                    'currency'         => 'INR',
                    'name'             => 'ApniFactory B2B Marketplace',
                    'description'      => "Order #{$order->orderno}",
                    'order_id'         => $gatewayOrderId,
                    'prefill'          => [
                        'name'    => $order->customer ? $order->customer->name : 'Contractor Buyer',
                        'email'   => $order->customer ? $order->customer->email : 'buyer@apnifactory.com',
                        'contact' => $order->customer ? $order->customer->mobile : '',
                    ],
                ];
            } elseif ($gatewayName === self::GATEWAY_PAYU) {
                $merchantKey = config('services.payu.key') ?: env('PAYU_KEY', 'Ks0wGV');
                $salt = config('services.payu.salt') ?: env('PAYU_SALT', 'payu_salt_placeholder');
                $txnid = $order->orderno . '_' . Str::random(4);
                $gatewayOrderId = $txnid;

                // PayU Hash calculation: sha512(key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT)
                $productInfo = "Order_{$order->orderno}";
                $firstName = $order->customer ? $order->customer->name : 'Buyer';
                $email = $order->customer ? $order->customer->email : 'buyer@apnifactory.com';
                $hashString = "{$merchantKey}|{$txnid}|{$amount}|{$productInfo}|{$firstName}|{$email}|||||||||||{$salt}";
                $hash = hash('sha512', $hashString);

                $gatewayParams = [
                    'key'         => $merchantKey,
                    'txnid'       => $txnid,
                    'amount'      => $amount,
                    'productinfo' => $productInfo,
                    'firstname'   => $firstName,
                    'email'       => $email,
                    'phone'       => $order->customer ? $order->customer->mobile : '',
                    'hash'        => $hash,
                ];
            }

            // 5. Create immutable payment record
            $transaction = Transection::create([
                'payment_ref'          => $paymentRef,
                'order_id'             => $order->id,
                'parent_order_id'      => $order->parent_order_id ?: $order->id,
                'order_no'             => $order->orderno,
                'customer_id'          => $customerId ?: $order->customer_id,
                'user_id'              => $order->user_id,
                'gateway_name'         => $gatewayName,
                'gateway_order_id'     => $gatewayOrderId,
                'amount'               => $amount,
                'currency'             => 'INR',
                'status'               => self::STATUS_PENDING,
                'verification_status'  => self::VERIFY_UNVERIFIED,
                'reconciliation_status'=> self::RECON_PENDING,
                'initiated_at'         => now(),
                'idempotency_key'      => $idempotencyKey,
                'txnid'                => $gatewayOrderId,
                'txnmethod'            => $gatewayName,
                'txndetail'            => json_encode([
                    'gateway_params' => $gatewayParams,
                    'initiated_at'   => now()->toIso8601String(),
                ]),
            ]);

            PaymentAuditLog::logAction(
                $transaction->id,
                $order->id,
                'payment_initiated',
                null,
                self::STATUS_PENDING,
                [
                    'amount'           => $amount,
                    'currency'         => 'INR',
                    'gateway_name'     => $gatewayName,
                    'gateway_order_id' => $gatewayOrderId,
                ],
                $customerId ?: $order->customer_id,
                'Buyer',
                'buyer'
            );

            return $this->formatInitiationResponse($transaction, $order, $gatewayParams);
        });
    }

    /**
     * Format initiation response payload.
     */
    protected function formatInitiationResponse(Transection $txn, Order $order, array $gatewayParams = []): array
    {
        return [
            'payment_ref'      => $txn->payment_ref,
            'transaction_id'   => $txn->id,
            'order_id'         => $order->id,
            'order_no'         => $order->orderno,
            'amount'           => (float)$txn->amount,
            'currency'         => $txn->currency,
            'gateway_name'     => $txn->gateway_name,
            'gateway_order_id' => $txn->gateway_order_id,
            'status'           => $txn->status,
            'initiated_at'     => $txn->initiated_at ? $txn->initiated_at->toIso8601String() : null,
            'gateway_payload'  => $gatewayParams ?: json_decode($txn->txndetail, true)['gateway_params'] ?? [],
        ];
    }

    /**
     * Cryptographically verify Razorpay callback signature.
     */
    public function verifyRazorpaySignature(
        string $orderId,
        string $paymentId,
        string $signature,
        ?string $secret = null
    ): bool {
        $secret = $secret ?: config('services.razorpay.secret', env('RAZORPAY_SECRET', 'secret_placeholder'));
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Cryptographically verify PayU callback reverse hash.
     * Formula: sha512(SALT|status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|KEY)
     */
    public function verifyPayuHash(array $params, ?string $salt = null): bool
    {
        $salt = $salt ?: config('services.payu.salt', env('PAYU_SALT', 'payu_salt_placeholder'));
        $key = $params['key'] ?? '';
        $txnid = $params['txnid'] ?? '';
        $amount = $params['amount'] ?? '';
        $productinfo = $params['productinfo'] ?? '';
        $firstname = $params['firstname'] ?? '';
        $email = $params['email'] ?? '';
        $status = $params['status'] ?? '';
        $postedHash = $params['hash'] ?? '';

        $udf1 = $params['udf1'] ?? '';
        $udf2 = $params['udf2'] ?? '';
        $udf3 = $params['udf3'] ?? '';
        $udf4 = $params['udf4'] ?? '';
        $udf5 = $params['udf5'] ?? '';

        $reverseString = "{$salt}|{$status}||||||{$udf5}|{$udf4}|{$udf3}|{$udf2}|{$udf1}|{$email}|{$firstname}|{$productinfo}|{$amount}|{$txnid}|{$key}";
        $expectedHash = hash('sha512', $reverseString);

        return hash_equals(strtolower($expectedHash), strtolower($postedHash));
    }

    /**
     * Process an incoming payment callback / webhook with strict idempotency and cryptographic verification.
     */
    public function processCallback(
        string $gatewayName,
        array $payload,
        ?string $signature = null,
        ?string $eventId = null
    ): array {
        return DB::transaction(function () use ($gatewayName, $payload, $signature, $eventId) {
            // 1. Resolve transaction identifiers
            $entity = $payload['payload']['payment']['entity'] ?? $payload;
            $gatewayOrderId = $entity['order_id'] ?? $payload['razorpay_order_id'] ?? $payload['order_id'] ?? $payload['txnid'] ?? null;
            $gatewayPaymentId = $entity['id'] ?? $payload['razorpay_payment_id'] ?? $payload['mihpayid'] ?? $payload['payment_id'] ?? null;
            $reportedAmount = isset($entity['amount']) ? (float)$entity['amount'] : (isset($payload['amount']) ? (float)$payload['amount'] : null);
            $reportedStatus = strtolower($entity['status'] ?? $payload['status'] ?? 'pending');
            $currency = $entity['currency'] ?? $payload['currency'] ?? 'INR';

            // 2. Lock transaction record for update
            $query = Transection::where(function ($q) use ($gatewayOrderId, $gatewayPaymentId) {
                if ($gatewayOrderId) {
                    $q->where('gateway_order_id', $gatewayOrderId)
                      ->orWhere('order_no', $gatewayOrderId);
                }
                if ($gatewayPaymentId) {
                    $q->orWhere('gateway_payment_id', $gatewayPaymentId)
                      ->orWhere('txnid', $gatewayPaymentId);
                }
            })->lockForUpdate();

            $txn = $query->first();

            if (!$txn) {
                return [
                    'success' => false,
                    'message' => "Transaction record not found for gateway reference '{$gatewayOrderId}'.",
                    'code'    => 'TRANSACTION_NOT_FOUND',
                ];
            }

            // Razorpay amount normalization: if amount is provided in paise (e.g. 2500000 for ₹25,000),
            // or if reported amount / 100 matches the expected order amount, convert to rupees.
            if ($gatewayName === self::GATEWAY_RAZORPAY && $reportedAmount !== null) {
                if (isset($payload['payload']['payment']['entity']) ||
                    isset($payload['amount_in_paise']) ||
                    abs(($reportedAmount / 100) - (float)$txn->amount) < 0.01) {
                    $reportedAmount = round($reportedAmount / 100, 2);
                }
            }

            // 3. Idempotency Check: Event already processed
            if ($eventId && $txn->gateway_event_id === $eventId) {
                return [
                    'success'     => true,
                    'message'     => 'Idempotent response: Event already processed.',
                    'transaction' => $txn,
                    'is_repeat'   => true,
                ];
            }

            // If already successful, do not downgrade due to out-of-order callback
            if ($txn->isSuccessful() && $reportedStatus !== 'success' && $reportedStatus !== 'captured') {
                return [
                    'success'     => true,
                    'message'     => 'Transaction is already verified successful. Delayed non-success event ignored.',
                    'transaction' => $txn,
                    'is_repeat'   => true,
                ];
            }

            // 4. Cryptographic Signature Verification
            $isSignatureValid = false;
            if ($gatewayName === self::GATEWAY_RAZORPAY) {
                $sig = $signature ?: ($payload['razorpay_signature'] ?? null);
                if ($sig && $gatewayOrderId && $gatewayPaymentId) {
                    $isSignatureValid = $this->verifyRazorpaySignature($gatewayOrderId, $gatewayPaymentId, $sig);
                }
            } elseif ($gatewayName === self::GATEWAY_PAYU) {
                $isSignatureValid = $this->verifyPayuHash($payload);
            }

            $verificationStatus = $isSignatureValid ? self::VERIFY_VERIFIED : self::VERIFY_TAMPERED;

            if (!$isSignatureValid) {
                $txn->update([
                    'verification_status'   => self::VERIFY_TAMPERED,
                    'reconciliation_status' => self::RECON_MISMATCH,
                    'failure_code'          => 'SIGNATURE_VERIFICATION_FAILED',
                    'failure_reason'        => 'Cryptographic callback signature or hash mismatch.',
                    'callback_received_at'  => now(),
                ]);

                PaymentAuditLog::logAction(
                    $txn->id,
                    $txn->order_id,
                    'callback_tampered_signature_failed',
                    $txn->status,
                    $txn->status,
                    ['payload' => $payload, 'signature' => $signature],
                    null,
                    'Gateway Callback',
                    'gateway'
                );

                return [
                    'success' => false,
                    'message' => 'Cryptographic callback verification failed. Signature mismatch.',
                    'code'    => 'INVALID_SIGNATURE',
                ];
            }

            // 5. Amount and Currency Reconciliation Check
            $expectedAmount = (float)$txn->amount;
            $isAmountMatched = ($reportedAmount === null) || (abs($reportedAmount - $expectedAmount) < 0.01);
            $isCurrencyMatched = (strtoupper($currency) === strtoupper($txn->currency));

            if (!$isAmountMatched || !$isCurrencyMatched) {
                $reason = !$isAmountMatched
                    ? "Amount mismatch: expected ₹{$expectedAmount}, received ₹{$reportedAmount}"
                    : "Currency mismatch: expected {$txn->currency}, received {$currency}";

                $txn->update([
                    'verification_status'   => self::VERIFY_VERIFIED,
                    'reconciliation_status' => self::RECON_MISMATCH,
                    'failure_code'          => 'RECONCILIATION_MISMATCH',
                    'failure_reason'        => $reason,
                    'callback_received_at'  => now(),
                ]);

                PaymentAuditLog::logAction(
                    $txn->id,
                    $txn->order_id,
                    'reconciliation_amount_mismatch',
                    $txn->status,
                    $txn->status,
                    ['expected_amount' => $expectedAmount, 'received_amount' => $reportedAmount],
                    null,
                    'Gateway Callback',
                    'gateway'
                );

                return [
                    'success' => false,
                    'message' => "Reconciliation failed: {$reason}.",
                    'code'    => 'AMOUNT_MISMATCH',
                ];
            }

            // 6. Process State Transition
            $previousStatus = $txn->status;
            $newStatus = in_array($reportedStatus, ['success', 'captured', 'paid'])
                ? self::STATUS_SUCCESSFUL
                : self::STATUS_FAILED;

            $txn->update([
                'status'                => $newStatus,
                'verification_status'   => self::VERIFY_VERIFIED,
                'reconciliation_status' => ($newStatus === self::STATUS_SUCCESSFUL) ? self::RECON_MATCHED : self::RECON_PENDING,
                'gateway_payment_id'    => $gatewayPaymentId ?: $txn->gateway_payment_id,
                'gateway_event_id'      => $eventId ?: $txn->gateway_event_id,
                'gateway_signature'     => $signature ?: ($payload['razorpay_signature'] ?? null),
                'gateway_captured_at'   => ($newStatus === self::STATUS_SUCCESSFUL) ? now() : null,
                'callback_received_at'  => now(),
                'verified_at'           => now(),
                'reconciled_at'         => ($newStatus === self::STATUS_SUCCESSFUL) ? now() : null,
                'txnid'                 => $gatewayPaymentId ?: $txn->txnid,
                'txnresponse'           => json_encode($payload),
            ]);

            // 7. Update Order Payment Status
            $order = Order::find($txn->order_id);
            if ($order && $newStatus === self::STATUS_SUCCESSFUL) {
                $order->markAsPaid($txn);

                // Clear buyer cart upon verified payment
                DB::table('cart')->where('customer_id', $order->customer_id)->delete();
                DB::table('cartattribute')->where('customer_id', $order->customer_id)->delete();

                // Phase 4.4: Record financial ledger and settlement entries
                try {
                    $ledgerService = app(FinancialLedgerService::class);
                    $ledgerService->recordOrderPayment($order, $txn);
                } catch (\Throwable $e) {
                    Log::error("Failed to record financial ledger for order #{$order->id}: " . $e->getMessage());
                }

                // Phase 6.2: Trigger non-blocking eligible-order evaluation on verified payment only
                try {
                    app(AgentOrderEligibilityService::class)->handleOrderLifecycleEvent(
                        $order,
                        'payment_verified',
                        $txn->gateway_event_id ?: $txn->payment_ref,
                        ['transaction_id' => $txn->id, 'gateway' => $txn->gateway_name]
                    );
                } catch (\Throwable $e) {
                    Log::error("Phase 6.2 payment_verified hook failed for order #{$order->id}: " . $e->getMessage());
                }

                // Phase 7.1: Record non-auto-retryable financial outbox notification event
                try {
                    app(CrossPlatformSyncService::class)->recordAndDispatchSyncEvent(
                        CrossPlatformSyncService::CHANNEL_CRM_EXTERNAL,
                        'payment',
                        $order->id,
                        $order->orderno,
                        'payment_verified',
                        ['order_id' => $order->id, 'order_no' => $order->orderno, 'payment_ref' => $txn->payment_ref, 'amount' => (float)$txn->amount],
                        "pay_verified:{$txn->payment_ref}"
                    );
                } catch (\Throwable $e) {
                    Log::warning("Phase 7.1 payment outbox hook skipped for order #{$order->id}: " . $e->getMessage());
                }
            }

            PaymentAuditLog::logAction(
                $txn->id,
                $txn->order_id,
                ($newStatus === self::STATUS_SUCCESSFUL) ? 'payment_captured_success' : 'payment_failed',
                $previousStatus,
                $newStatus,
                [
                    'gateway_payment_id' => $gatewayPaymentId,
                    'amount'             => $expectedAmount,
                    'reconciliation'     => $txn->reconciliation_status,
                ],
                null,
                'Gateway Callback',
                'gateway'
            );

            return [
                'success'               => true,
                'message'               => ($newStatus === self::STATUS_SUCCESSFUL) ? 'Payment verified and captured successfully.' : 'Payment failure recorded.',
                'payment_ref'           => $txn->payment_ref,
                'status'                => $newStatus,
                'reconciliation_status' => $txn->reconciliation_status,
                'transaction'           => $txn->fresh(),
            ];
        });
    }

    /**
     * Manually reconcile a payment transaction (Finance / Admin role).
     */
    public function manualReconciliation(
        Transection $txn,
        string $reconciliationStatus,
        string $notes,
        int $userId,
        string $userName
    ): Transection {
        if (!in_array($reconciliationStatus, [self::RECON_MATCHED, self::RECON_MISMATCH, self::RECON_MANUAL_REVIEW])) {
            throw new \InvalidArgumentException("Invalid reconciliation status: '{$reconciliationStatus}'.");
        }

        $prevStatus = $txn->reconciliation_status;

        $txn->update([
            'reconciliation_status' => $reconciliationStatus,
            'reconciliation_notes'  => $notes,
            'reconciled_at'         => now(),
            'reconciled_by_user_id' => $userId,
        ]);

        PaymentAuditLog::logAction(
            $txn->id,
            $txn->order_id,
            'manual_reconciliation_override',
            $prevStatus,
            $reconciliationStatus,
            ['notes' => $notes],
            $userId,
            $userName,
            'finance'
        );

        return $txn->fresh();
    }
}

