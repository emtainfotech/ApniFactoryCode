<?php

namespace App\Services;

use App\Models\FulfilmentClaim;
use App\Models\FulfilmentClaimEvidence;
use App\Models\FulfilmentClaimHistory;
use App\Models\FulfilmentClaimItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\SellerFulfilment;
use App\Models\SellerFulfilmentHistory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BuyerDisputeService
{
    /**
     * Submit a shortage, damage, or other dispute claim for a seller fulfilment order.
     */
    public function submitClaim(
        SellerFulfilment $fulfilment,
        string $claimType,
        string $description,
        int $customerId,
        string $customerName,
        array $items = [],
        array $evidenceFiles = [],
        ?string $idempotencyKey = null
    ): FulfilmentClaim {
        // 1. Authorize: Only the buyer customer or authorized user can submit
        if ($fulfilment->customer_id !== $customerId) {
            throw ValidationException::withMessages([
                'customer' => ['Unauthorized. You can only lodge claims for your own orders.'],
            ]);
        }

        // 2. Order state prerequisite: Goods must have been dispatched
        if (!$fulfilment->isDispatched()) {
            throw ValidationException::withMessages([
                'status' => ['Dispute/claim cannot be lodged before order dispatch. Consignment must be in transit or handed over.'],
            ]);
        }

        // 3. Policy Window Enforcement (Configurable)
        if (config('marketplace_dispute.policy_window_enabled', false)) {
            $windowDays = config('marketplace_dispute.policy_window_days');
            if ($windowDays !== null && $fulfilment->dispatched_at) {
                $deadline = $fulfilment->dispatched_at->copy()->addDays((int)$windowDays);
                if (now()->greaterThan($deadline)) {
                    throw ValidationException::withMessages([
                        'policy_window' => ["The approved policy window of {$windowDays} days for reporting claims expired on {$deadline->format('d M Y, h:i A')}."],
                    ]);
                }
            }
        }

        // 4. Idempotency Check: Prevent duplicate submissions on retries
        if (!empty($idempotencyKey)) {
            $existing = FulfilmentClaim::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing->load(['items', 'evidence', 'history']);
            }
        }

        // 5. Line-Item Level Validation
        $validatedItems = [];
        if (!empty($items)) {
            foreach ($items as $idx => $itemData) {
                $detailId = $itemData['order_detail_id'] ?? null;
                $productName = $itemData['product_name'] ?? 'Consignment Item';
                $orderedQty = (int) ($itemData['ordered_qty'] ?? 1);
                $receivedQty = (int) ($itemData['received_qty'] ?? 0);
                $affectedQty = (int) ($itemData['affected_qty'] ?? 0);

                if ($detailId) {
                    $orderDetail = OrderDetail::find($detailId);
                    if ($orderDetail && $orderDetail->order_id !== $fulfilment->order_id) {
                        throw ValidationException::withMessages([
                            "items.{$idx}" => ['Order detail does not belong to this seller fulfilment.'],
                        ]);
                    }
                    if ($orderDetail) {
                        $productName = $orderDetail->productname ?? $productName;
                    }
                }

                if ($affectedQty <= 0) {
                    throw ValidationException::withMessages([
                        "items.{$idx}.affected_qty" => ['Affected quantity must be greater than zero.'],
                    ]);
                }

                if ($orderedQty > 0 && $affectedQty > $orderedQty) {
                    throw ValidationException::withMessages([
                        "items.{$idx}.affected_qty" => ["Affected quantity ({$affectedQty}) cannot exceed ordered quantity ({$orderedQty})."],
                    ]);
                }

                $validatedItems[] = [
                    'order_detail_id' => $detailId,
                    'product_name'    => $productName,
                    'sku'             => $itemData['sku'] ?? null,
                    'ordered_qty'     => $orderedQty,
                    'received_qty'    => $receivedQty,
                    'affected_qty'    => $affectedQty,
                    'condition_notes' => $itemData['condition_notes'] ?? null,
                ];
            }
        }

        return DB::transaction(function () use (
            $fulfilment,
            $claimType,
            $description,
            $customerId,
            $customerName,
            $validatedItems,
            $evidenceFiles,
            $idempotencyKey
        ) {
            $claimRef = 'CLM-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $claim = FulfilmentClaim::create([
                'claim_ref'            => $claimRef,
                'seller_fulfilment_id' => $fulfilment->id,
                'parent_order_id'      => $fulfilment->parent_order_id,
                'order_id'             => $fulfilment->order_id,
                'seller_user_id'       => $fulfilment->seller_user_id,
                'customer_id'          => $customerId,
                'claim_type'           => $claimType,
                'claim_status'         => FulfilmentClaim::STATUS_SUBMITTED,
                'description'          => $description,
                'idempotency_key'      => $idempotencyKey,
            ]);

            // Save line items
            foreach ($validatedItems as $valItem) {
                $claim->items()->create($valItem);
            }

            // Save evidence attachments
            foreach ($evidenceFiles as $file) {
                if ($file instanceof UploadedFile) {
                    $this->storeEvidenceAttachment($claim, $file, $customerId, 'buyer', 'damage_photo');
                }
            }

            // Update SellerFulfilment dispute foundation
            $statusMap = [
                FulfilmentClaim::TYPE_SHORTAGE   => SellerFulfilment::DISPUTE_SHORTAGE,
                FulfilmentClaim::TYPE_DAMAGE     => SellerFulfilment::DISPUTE_DAMAGE,
                FulfilmentClaim::TYPE_WRONG_ITEM => SellerFulfilment::DISPUTE_DISPUTED,
                FulfilmentClaim::TYPE_OTHER      => SellerFulfilment::DISPUTE_DISPUTED,
            ];
            $fulfilmentDisputeStatus = $statusMap[$claimType] ?? SellerFulfilment::DISPUTE_DISPUTED;

            $fulfilment->update([
                'dispute_status'      => $fulfilmentDisputeStatus,
                'dispute_reason'      => $description,
                'dispute_reported_at' => now(),
            ]);

            // Immutable audit logs
            FulfilmentClaimHistory::logAction(
                $claim,
                'claim_submitted',
                $customerId,
                'buyer',
                $customerName,
                null,
                FulfilmentClaim::STATUS_SUBMITTED,
                [
                    'claim_ref'   => $claimRef,
                    'claim_type'  => $claimType,
                    'items_count' => count($validatedItems),
                ],
                "Buyer lodged {$claimType} claim: #{$claimRef}."
            );

            SellerFulfilmentHistory::logAction(
                $fulfilment->id,
                $fulfilment->order_id,
                'dispute_reported',
                $fulfilment->fulfilment_status,
                $fulfilment->fulfilment_status,
                [
                    'claim_ref'      => $claimRef,
                    'dispute_status' => $fulfilmentDisputeStatus,
                ],
                $customerId,
                $customerName,
                'buyer',
                "Buyer filed dispute: {$description} (Ref: {$claimRef})"
            );

            // Notify seller and operations
            $this->notifyStakeholdersOnClaimSubmission($claim, $fulfilment);

            $this->syncOrderEligibility($fulfilment->order_id, 'claim_submitted', "claim_{$claim->id}");

            return $claim->load(['items', 'evidence', 'history']);
        });
    }

    /**
     * Buyer acknowledges receipt of goods for the seller fulfilment order.
     */
    public function acknowledgeReceipt(
        SellerFulfilment $fulfilment,
        int $customerId,
        string $customerName,
        ?string $notes = null
    ): SellerFulfilment {
        if ($fulfilment->customer_id !== $customerId) {
            throw ValidationException::withMessages([
                'customer' => ['Unauthorized. You do not have permission to acknowledge receipt for this order.'],
            ]);
        }

        if (!$fulfilment->isDispatched()) {
            throw ValidationException::withMessages([
                'status' => ['Order has not been dispatched yet. Cannot acknowledge receipt before dispatch.'],
            ]);
        }

        // Idempotency: If already acknowledged, return gracefully
        if ($fulfilment->isAcknowledged()) {
            return $fulfilment;
        }

        $prevStatus = $fulfilment->fulfilment_status;

        $fulfilment->update([
            'fulfilment_status'           => SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED,
            'buyer_acknowledged_at'       => now(),
            'buyer_acknowledgement_notes' => $notes,
        ]);

        SellerFulfilmentHistory::logAction(
            $fulfilment->id,
            $fulfilment->order_id,
            'buyer_acknowledged_receipt',
            $prevStatus,
            SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED,
            ['notes' => $notes],
            $customerId,
            $customerName,
            'buyer',
            'Buyer confirmed receipt of goods at designated delivery point.'
        );

        $this->syncOrderEligibility($fulfilment->order_id, 'buyer_acknowledged_receipt', "ack_{$fulfilment->id}_" . now()->format('YmdHis'));

        return $fulfilment->refresh();
    }

    /**
     * Seller submits a response/clarification to an open claim.
     */
    public function submitSellerResponse(
        FulfilmentClaim $claim,
        string $responseRemarks,
        int $sellerUserId,
        string $sellerName,
        array $evidenceFiles = []
    ): FulfilmentClaim {
        // Enforce seller tenant isolation
        if ($claim->seller_user_id !== $sellerUserId) {
            throw ValidationException::withMessages([
                'seller' => ['Unauthorized. You can only respond to claims regarding your own seller orders.'],
            ]);
        }

        if (!$claim->isOpen()) {
            throw ValidationException::withMessages([
                'claim' => ['This claim has already been closed and cannot accept further seller responses.'],
            ]);
        }

        return DB::transaction(function () use (
            $claim,
            $responseRemarks,
            $sellerUserId,
            $sellerName,
            $evidenceFiles
        ) {
            $prevStatus = $claim->claim_status;
            $newStatus = ($prevStatus === FulfilmentClaim::STATUS_SUBMITTED)
                ? FulfilmentClaim::STATUS_SELLER_RESPONDED
                : $prevStatus;

            $claim->update([
                'seller_response'            => $responseRemarks,
                'seller_responded_at'        => now(),
                'seller_response_by_user_id' => $sellerUserId,
                'claim_status'               => $newStatus,
            ]);

            // Save seller counter-evidence attachments
            foreach ($evidenceFiles as $file) {
                if ($file instanceof UploadedFile) {
                    $this->storeEvidenceAttachment($claim, $file, $sellerUserId, 'seller', 'seller_response_evidence');
                }
            }

            FulfilmentClaimHistory::logAction(
                $claim,
                'seller_response_submitted',
                $sellerUserId,
                'seller',
                $sellerName,
                $prevStatus,
                $newStatus,
                ['seller_response' => $responseRemarks],
                "Seller submitted response remarks: {$responseRemarks}"
            );

            // Notify buyer and admin
            $this->notifyStakeholdersOnSellerResponse($claim);

            return $claim->load(['items', 'evidence', 'history']);
        });
    }

    /**
     * Admin/Operations review & update claim status.
     */
    public function updateClaimStatusByAdmin(
        FulfilmentClaim $claim,
        string $newStatus,
        int $adminUserId,
        string $adminName,
        ?string $adminNotes = null,
        ?string $resolutionSummary = null
    ): FulfilmentClaim {
        if (!$claim->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot transition claim from '{$claim->claim_status}' to '{$newStatus}'."],
            ]);
        }

        return DB::transaction(function () use (
            $claim,
            $newStatus,
            $adminUserId,
            $adminName,
            $adminNotes,
            $resolutionSummary
        ) {
            $prevStatus = $claim->claim_status;
            $updateData = ['claim_status' => $newStatus];

            if ($adminNotes !== null) {
                $updateData['admin_notes'] = $adminNotes;
            }

            if ($resolutionSummary !== null) {
                $updateData['resolution_summary'] = $resolutionSummary;
            }

            if (in_array($newStatus, [FulfilmentClaim::STATUS_RESOLVED, FulfilmentClaim::STATUS_REJECTED], true)) {
                $updateData['resolved_by_user_id'] = $adminUserId;
                $updateData['resolved_at'] = now();
            }

            $claim->update($updateData);

            FulfilmentClaimHistory::logAction(
                $claim,
                'claim_status_updated',
                $adminUserId,
                'admin',
                $adminName,
                $prevStatus,
                $newStatus,
                [
                    'admin_notes'        => $adminNotes,
                    'resolution_summary' => $resolutionSummary,
                ],
                "Admin transitioned claim status to '{$newStatus}'."
            );

            $this->syncOrderEligibility($claim->order_id, "claim_{$newStatus}", "claim_admin_{$claim->id}_{$newStatus}");

            return $claim->load(['items', 'evidence', 'history']);
        });
    }

    /**
     * Upload and store evidence file with strict validation.
     */
    public function storeEvidenceAttachment(
        FulfilmentClaim $claim,
        UploadedFile $file,
        int $uploaderUserId,
        string $uploaderRole,
        string $documentType
    ): FulfilmentClaimEvidence {
        $allowedMimes = config('marketplace_dispute.allowed_mime_types', [
            'image/jpeg', 'image/png', 'image/webp', 'application/pdf',
        ]);
        $maxSizeKb = config('marketplace_dispute.max_file_size_kb', 5120);

        $mime = $file->getMimeType();
        if (!in_array($mime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'evidence' => ["Invalid evidence document type [{$mime}]. Allowed types: JPEG, PNG, WEBP, PDF."],
            ]);
        }

        if (($file->getSize() / 1024) > $maxSizeKb) {
            throw ValidationException::withMessages([
                'evidence' => ["Evidence document exceeds maximum permitted size of {$maxSizeKb} KB."],
            ]);
        }

        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $safeFileName = 'claim_' . $claim->id . '_' . time() . '_' . Str::random(8) . '.' . $extension;
        $destinationPath = public_path('claim_evidence');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $file->move($destinationPath, $safeFileName);
        $relativePath = 'claim_evidence/' . $safeFileName;

        $evidence = FulfilmentClaimEvidence::create([
            'fulfilment_claim_id' => $claim->id,
            'uploader_user_id'    => $uploaderUserId,
            'uploader_role'       => $uploaderRole,
            'document_type'       => $documentType,
            'file_path'           => $relativePath,
            'file_name'           => $file->getClientOriginalName(),
            'file_size'           => filesize($destinationPath . DIRECTORY_SEPARATOR . $safeFileName),
            'mime_type'           => $mime,
            'created_at'          => now(),
        ]);

        return $evidence;
    }

    /**
     * Retrieve claims for a buyer, with internal admin notes stripped.
     */
    public function getBuyerClaims(int $customerId, ?int $parentOrderId = null)
    {
        $query = FulfilmentClaim::with(['items', 'evidence', 'history', 'sellerFulfilment.company'])
            ->where('customer_id', $customerId)
            ->latest('id');

        if ($parentOrderId) {
            $query->where('parent_order_id', $parentOrderId);
        }

        $claims = $query->paginate(15);
        $claims->getCollection()->transform(function ($claim) {
            unset($claim->admin_notes); // Confidential: never expose to buyer
            return $claim;
        });

        return $claims;
    }

    /**
     * Retrieve claims for a seller, with internal admin notes stripped.
     */
    public function getSellerClaims(int $sellerUserId, ?string $status = null)
    {
        $query = FulfilmentClaim::with(['items', 'evidence', 'history', 'sellerFulfilment'])
            ->where('seller_user_id', $sellerUserId)
            ->latest('id');

        if ($status) {
            $query->where('claim_status', $status);
        }

        $claims = $query->paginate(15);
        $claims->getCollection()->transform(function ($claim) {
            unset($claim->admin_notes); // Confidential: never expose to seller
            return $claim;
        });

        return $claims;
    }

    /**
     * Retrieve claims for admin/operations console with full data and KPI metrics.
     */
    public function getAdminClaims(array $filters = [])
    {
        $query = FulfilmentClaim::with(['items', 'evidence', 'history', 'sellerFulfilment.company', 'seller', 'customer'])
            ->latest('id');

        if (!empty($filters['status'])) {
            $query->where('claim_status', $filters['status']);
        }
        if (!empty($filters['claim_type'])) {
            $query->where('claim_type', $filters['claim_type']);
        }
        if (!empty($filters['seller_id'])) {
            $query->where('seller_user_id', (int)$filters['seller_id']);
        }
        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('claim_ref', 'like', "%{$term}%")
                  ->orWhereHas('order', function ($oq) use ($term) {
                      $oq->where('orderno', 'like', "%{$term}%");
                  });
            });
        }

        return $query->paginate(15)->withQueryString();
    }

    /**
     * Calculate KPI metrics for admin dispute overview.
     */
    public function getAdminMetrics(): array
    {
        return [
            'total_claims'     => FulfilmentClaim::count(),
            'pending_review'   => FulfilmentClaim::whereIn('claim_status', [FulfilmentClaim::STATUS_SUBMITTED, FulfilmentClaim::STATUS_SELLER_RESPONDED, FulfilmentClaim::STATUS_UNDER_REVIEW])->count(),
            'shortage_claims'  => FulfilmentClaim::where('claim_type', FulfilmentClaim::TYPE_SHORTAGE)->count(),
            'damage_claims'    => FulfilmentClaim::where('claim_type', FulfilmentClaim::TYPE_DAMAGE)->count(),
            'resolved_claims'  => FulfilmentClaim::where('claim_status', FulfilmentClaim::STATUS_RESOLVED)->count(),
        ];
    }

    /**
     * Non-blocking notification helpers.
     */
    protected function notifyStakeholdersOnClaimSubmission(FulfilmentClaim $claim, SellerFulfilment $fulfilment): void
    {
        try {
            // Notify Seller
            DB::table('notifications')->insert([
                'user_id'     => $claim->seller_user_id,
                'customer_id' => 0,
                'title'       => "Claim Lodged: #{$claim->claim_ref}",
                'msg'         => "Buyer reported a {$claim->claim_type} issue on order #{$fulfilment->order?->orderno}. Ref: {$claim->claim_ref}.",
                'msgread'     => 0,
                'type'        => 'claim_lodged',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            // Notify Buyer confirmation
            DB::table('notifications')->insert([
                'user_id'     => 0,
                'customer_id' => $claim->customer_id,
                'title'       => "Claim Confirmation: #{$claim->claim_ref}",
                'msg'         => "Your {$claim->claim_type} report has been recorded under reference #{$claim->claim_ref}. Operations and the seller have been notified.",
                'msgread'     => 0,
                'type'        => 'claim_confirmation',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            // Notification failures must not abort claim creation
        }
    }

    protected function notifyStakeholdersOnSellerResponse(FulfilmentClaim $claim): void
    {
        try {
            DB::table('notifications')->insert([
                'user_id'     => 0,
                'customer_id' => $claim->customer_id,
                'title'       => "Seller Responded: #{$claim->claim_ref}",
                'msg'         => "The manufacturer/seller has responded to your claim #{$claim->claim_ref}.",
                'msgread'     => 0,
                'type'        => 'seller_responded_claim',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable $e) {
        }
    }

    protected function syncOrderEligibility(?int $orderId, string $eventType, string $eventRef): void
    {
        try {
            if (!$orderId) {
                return;
            }
            $order = Order::find($orderId);
            if ($order) {
                app(AgentOrderEligibilityService::class)->handleOrderLifecycleEvent($order, $eventType, $eventRef);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Phase 6.2 eligibility sync skipped ({$eventType}): " . $e->getMessage());
        }
    }
}
