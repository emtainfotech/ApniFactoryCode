<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Notification;
use App\Models\Order;
use App\Models\SellerFulfilment;
use App\Models\SellerFulfilmentHistory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SellerFulfilmentService
{
    /**
     * Get or initialize independent fulfilment record for a seller fulfilment order.
     */
    public function getOrCreateFulfilment(Order $order): SellerFulfilment
    {
        $existing = SellerFulfilment::where('order_id', $order->id)->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($order) {
            $sellerUserId = (int) $order->user_id;
            $company = Company::where('user_id', $sellerUserId)->first();

            $fulfilmentRef = 'FULFIL-' . date('Y') . '-' . strtoupper(Str::random(8));

            // Default dispatch origin snapshot from verified seller company address
            $dispatchOriginName = $company ? $company->name : 'Seller #' . $sellerUserId . ' Works';
            $dispatchOriginCity = $company ? $company->state : null;
            $dispatchOriginState = $company ? $company->state : null;
            $dispatchOriginPincode = $company ? $company->pincode : null;
            $dispatchOriginContact = $company ? $company->mobile : null;
            $dispatchOriginAddress = $company
                ? "{$company->city}, {$company->state} - {$company->pincode}"
                : "Seller Factory Gate, {$dispatchOriginState}";

            // Default delivery handover point from buyer order address
            $handoverAddress = $order->address;
            $handoverCity = null;
            $handoverState = null;
            $handoverPincode = null;

            if ($handoverAddress && preg_match('/(\d{6})/', $handoverAddress, $matches)) {
                $handoverPincode = $matches[1];
            }

            $fulfilment = SellerFulfilment::create([
                'fulfilment_ref'          => $fulfilmentRef,
                'parent_order_id'         => $order->parent_order_id ?: $order->id,
                'order_id'                => $order->id,
                'seller_user_id'          => $sellerUserId,
                'customer_id'             => (int) $order->customer_id,
                'company_id'              => $company ? $company->id : null,
                'dispatch_origin_type'    => 'factory',
                'dispatch_origin_name'    => $dispatchOriginName,
                'dispatch_origin_address' => $dispatchOriginAddress,
                'dispatch_origin_city'    => $dispatchOriginCity,
                'dispatch_origin_state'   => $dispatchOriginState,
                'dispatch_origin_pincode' => $dispatchOriginPincode,
                'dispatch_origin_contact' => $dispatchOriginContact,
                'handover_type'           => 'transport_nagar',
                'handover_location_name'  => 'Designated Transport Nagar / Godown Point',
                'handover_address'        => $handoverAddress,
                'handover_city'           => $handoverCity,
                'handover_state'          => $handoverState,
                'handover_pincode'        => $handoverPincode,
                'freight_terms'           => SellerFulfilment::DEFAULT_FREIGHT_TERMS,
                'fulfilment_status'       => SellerFulfilment::STATUS_PENDING_ACCEPTANCE,
            ]);

            SellerFulfilmentHistory::logAction(
                $fulfilment->id,
                $order->id,
                'fulfilment_initialized',
                null,
                SellerFulfilment::STATUS_PENDING_ACCEPTANCE,
                ['order_id' => $order->id, 'orderno' => $order->orderno],
                null,
                'System',
                'system',
                "Independent fulfilment record initialized for order #{$order->orderno}."
            );

            return $fulfilment;
        });
    }

    /**
     * Seller selects or updates the dispatch origin (Factory / Warehouse).
     */
    public function updateDispatchOrigin(
        SellerFulfilment $fulfilment,
        array $originData,
        int $sellerId,
        string $actorName
    ): SellerFulfilment {
        $this->authorizeSeller($fulfilment, $sellerId);

        if ($fulfilment->isDispatched()) {
            throw ValidationException::withMessages([
                'dispatch_origin' => ['Cannot alter dispatch origin after goods have been dispatched.'],
            ]);
        }

        $prev = [
            'type'    => $fulfilment->dispatch_origin_type,
            'name'    => $fulfilment->dispatch_origin_name,
            'address' => $fulfilment->dispatch_origin_address,
        ];

        $fulfilment->update([
            'dispatch_origin_type'    => $originData['type'] ?? $fulfilment->dispatch_origin_type,
            'dispatch_origin_name'    => $originData['name'] ?? $fulfilment->dispatch_origin_name,
            'dispatch_origin_address' => $originData['address'] ?? $fulfilment->dispatch_origin_address,
            'dispatch_origin_city'    => $originData['city'] ?? $fulfilment->dispatch_origin_city,
            'dispatch_origin_state'   => $originData['state'] ?? $fulfilment->dispatch_origin_state,
            'dispatch_origin_pincode' => $originData['pincode'] ?? $fulfilment->dispatch_origin_pincode,
            'dispatch_origin_contact' => $originData['contact'] ?? $fulfilment->dispatch_origin_contact,
        ]);

        SellerFulfilmentHistory::logAction(
            $fulfilment->id,
            $fulfilment->order_id,
            'dispatch_origin_updated',
            $fulfilment->fulfilment_status,
            $fulfilment->fulfilment_status,
            ['previous' => $prev, 'updated' => $originData],
            $sellerId,
            $actorName,
            'seller',
            'Dispatch origin location snapshot updated.'
        );

        return $fulfilment->fresh();
    }

    /**
     * Capture or update the Delivery / Hand-Over Point (Transport Nagar / Godown).
     */
    public function updateDeliveryHandoverPoint(
        SellerFulfilment $fulfilment,
        array $handoverData,
        int $actorId,
        string $actorRole,
        string $actorName
    ): SellerFulfilment {
        // Enforce access: must be seller of this order or the buyer customer or admin
        if ($actorRole === 'seller' && $fulfilment->seller_user_id !== $actorId) {
            throw ValidationException::withMessages(['handover' => ['Unauthorized seller access.']]);
        }
        if ($actorRole === 'buyer' && $fulfilment->customer_id !== $actorId) {
            throw ValidationException::withMessages(['handover' => ['Unauthorized buyer access.']]);
        }

        if ($fulfilment->isDispatched()) {
            throw ValidationException::withMessages([
                'handover' => ['Cannot alter delivery / hand-over point after goods have been dispatched.'],
            ]);
        }

        $prev = [
            'type'     => $fulfilment->handover_type,
            'name'     => $fulfilment->handover_location_name,
            'address'  => $fulfilment->handover_address,
        ];

        $fulfilment->update([
            'handover_type'          => $handoverData['type'] ?? $fulfilment->handover_type,
            'handover_location_name' => $handoverData['name'] ?? $fulfilment->handover_location_name,
            'handover_address'       => $handoverData['address'] ?? $fulfilment->handover_address,
            'handover_city'          => $handoverData['city'] ?? $fulfilment->handover_city,
            'handover_state'         => $handoverData['state'] ?? $fulfilment->handover_state,
            'handover_pincode'       => $handoverData['pincode'] ?? $fulfilment->handover_pincode,
            'handover_instructions'  => $handoverData['instructions'] ?? $fulfilment->handover_instructions,
        ]);

        SellerFulfilmentHistory::logAction(
            $fulfilment->id,
            $fulfilment->order_id,
            'handover_point_updated',
            $fulfilment->fulfilment_status,
            $fulfilment->fulfilment_status,
            ['previous' => $prev, 'updated' => $handoverData],
            $actorId,
            $actorName,
            $actorRole,
            'Delivery / Transport Nagar hand-over point updated.'
        );

        return $fulfilment->fresh();
    }

    /**
     * Update Transporter details for the seller fulfilment order.
     */
    public function updateTransporterDetails(
        SellerFulfilment $fulfilment,
        array $transporterData,
        int $sellerId,
        string $actorName
    ): SellerFulfilment {
        $this->authorizeSeller($fulfilment, $sellerId);

        $fulfilment->update([
            'transporter_name'        => $transporterData['name'] ?? $fulfilment->transporter_name,
            'transporter_contact'     => $transporterData['contact'] ?? $fulfilment->transporter_contact,
            'transporter_tracking_no' => $transporterData['tracking_no'] ?? $fulfilment->transporter_tracking_no,
        ]);

        SellerFulfilmentHistory::logAction(
            $fulfilment->id,
            $fulfilment->order_id,
            'transporter_details_saved',
            $fulfilment->fulfilment_status,
            $fulfilment->fulfilment_status,
            $transporterData,
            $sellerId,
            $actorName,
            'seller',
            'Transporter information updated.'
        );

        return $fulfilment->fresh();
    }

    /**
     * Upload LR / Bilty document.
     * CRITICAL BRD RULE: LR upload is dispatch evidence, NOT proof of delivery or buyer acceptance.
     * Uploading an LR does not automatically mark an order delivered.
     */
    public function uploadLrDocument(
        SellerFulfilment $fulfilment,
        UploadedFile $file,
        ?string $lrNumber,
        int $sellerId,
        string $actorName
    ): SellerFulfilment {
        $this->authorizeSeller($fulfilment, $sellerId);

        // 1. Validate file format and size (max 5MB, images/pdf)
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw ValidationException::withMessages([
                'lr_document' => ['Invalid document format. Only JPG, PNG, WEBP, and PDF documents are allowed.'],
            ]);
        }

        if ($file->getSize() > 5 * 1024 * 1024) { // 5MB limit
            throw ValidationException::withMessages([
                'lr_document' => ['File size exceeds the 5MB limit.'],
            ]);
        }

        // 2. Store securely
        $fileName = 'LR_' . $fulfilment->order_id . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('dispatch_documents/' . date('Ym'), $fileName, 'public');

        $fulfilment->update([
            'lr_number'              => $lrNumber ?? $fulfilment->lr_number,
            'lr_document_path'       => $path,
            'lr_document_name'       => $file->getClientOriginalName(),
            'lr_file_size'           => $file->getSize(),
            'lr_mime_type'           => $file->getMimeType(),
            'lr_uploaded_at'         => now(),
            'lr_uploaded_by_user_id' => $sellerId,
        ]);

        SellerFulfilmentHistory::logAction(
            $fulfilment->id,
            $fulfilment->order_id,
            'lr_document_uploaded',
            $fulfilment->fulfilment_status,
            $fulfilment->fulfilment_status, // Status remains in current stage; NOT marked delivered
            [
                'lr_number' => $fulfilment->lr_number,
                'path'      => $path,
                'size'      => $file->getSize(),
            ],
            $sellerId,
            $actorName,
            'seller',
            'LR / Bilty document uploaded as dispatch evidence.'
        );

        return $fulfilment->fresh();
    }

    /**
     * Record final dispatch details and transition status to 'dispatched'.
     */
    public function recordDispatch(
        SellerFulfilment $fulfilment,
        array $dispatchData,
        int $sellerId,
        string $actorName
    ): SellerFulfilment {
        $this->authorizeSeller($fulfilment, $sellerId);

        // Required dispatch data validation
        $transporterName = $dispatchData['transporter_name'] ?? $fulfilment->transporter_name;
        $lrNumber = $dispatchData['lr_number'] ?? $fulfilment->lr_number;
        $lrDocPath = $fulfilment->lr_document_path;

        if (empty($transporterName)) {
            throw ValidationException::withMessages([
                'transporter_name' => ['Transporter name is required before dispatching.'],
            ]);
        }

        if (empty($lrNumber) && empty($lrDocPath)) {
            throw ValidationException::withMessages([
                'lr_number' => ['An LR / Bilty number or uploaded document is required to confirm dispatch.'],
            ]);
        }

        $prevStatus = $fulfilment->fulfilment_status;
        $dispatchedAt = !empty($dispatchData['dispatched_at']) ? date('Y-m-d H:i:s', strtotime($dispatchData['dispatched_at'])) : now();

        $fulfilment->update([
            'transporter_name'        => $transporterName,
            'transporter_contact'     => $dispatchData['transporter_contact'] ?? $fulfilment->transporter_contact,
            'transporter_tracking_no' => $dispatchData['transporter_tracking_no'] ?? $fulfilment->transporter_tracking_no,
            'lr_number'               => $lrNumber,
            'dispatched_at'           => $dispatchedAt,
            'package_count'           => (int) ($dispatchData['package_count'] ?? $fulfilment->package_count),
            'weight_volume'           => $dispatchData['weight_volume'] ?? $fulfilment->weight_volume,
            'vehicle_number'          => $dispatchData['vehicle_number'] ?? $fulfilment->vehicle_number,
            'estimated_delivery_date' => $dispatchData['estimated_delivery_date'] ?? $fulfilment->estimated_delivery_date,
            'fulfilment_status'       => SellerFulfilment::STATUS_DISPATCHED,
        ]);

        SellerFulfilmentHistory::logAction(
            $fulfilment->id,
            $fulfilment->order_id,
            'order_dispatched',
            $prevStatus,
            SellerFulfilment::STATUS_DISPATCHED,
            $dispatchData,
            $sellerId,
            $actorName,
            'seller',
            "Order dispatched via {$transporterName} under LR #{$fulfilment->lr_number}."
        );

        // Dispatch in-app notification to buyer
        $this->notifyBuyerOfDispatch($fulfilment);

        $this->triggerEligibilitySync($fulfilment, 'fulfilment_dispatched');

        return $fulfilment->fresh();
    }

    /**
     * Update permitted seller fulfilment status.
     */
    public function updateStatus(
        SellerFulfilment $fulfilment,
        string $newStatus,
        int $actorId,
        string $actorRole,
        string $actorName,
        ?string $notes = null
    ): SellerFulfilment {
        if ($actorRole === 'seller') {
            $this->authorizeSeller($fulfilment, $actorId);
        }

        if (!$fulfilment->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot transition fulfilment from '{$fulfilment->fulfilment_status}' to '{$newStatus}'."],
            ]);
        }

        // Additional validation when moving to dispatched
        if ($newStatus === SellerFulfilment::STATUS_DISPATCHED && empty($fulfilment->transporter_name)) {
            throw ValidationException::withMessages([
                'status' => ['Cannot mark as dispatched without entering transporter details.'],
            ]);
        }

        $prevStatus = $fulfilment->fulfilment_status;
        $fulfilment->update([
            'fulfilment_status' => $newStatus,
            'notes'             => $notes ? ($fulfilment->notes . "\n" . $notes) : $fulfilment->notes,
        ]);

        SellerFulfilmentHistory::logAction(
            $fulfilment->id,
            $fulfilment->order_id,
            'status_transition_' . $newStatus,
            $prevStatus,
            $newStatus,
            ['notes' => $notes],
            $actorId,
            $actorName,
            $actorRole,
            $notes ?? "Status updated to {$newStatus}."
        );

        $this->triggerEligibilitySync($fulfilment, "fulfilment_{$newStatus}");

        return $fulfilment->fresh();
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
                'customer' => ['You do not have permission to acknowledge receipt for this order.'],
            ]);
        }

        if (!$fulfilment->isDispatched()) {
            throw ValidationException::withMessages([
                'status' => ['Order has not been dispatched yet. Cannot acknowledge receipt before dispatch.'],
            ]);
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

        $this->triggerEligibilitySync($fulfilment, 'buyer_acknowledged_receipt');

        return $fulfilment->fresh();
    }

    /**
     * Buyer reports shortage, damage, or dispute for a seller fulfilment order.
     */
    public function reportDispute(
        SellerFulfilment $fulfilment,
        string $disputeType,
        string $reason,
        int $customerId,
        string $customerName
    ): SellerFulfilment {
        if ($fulfilment->customer_id !== $customerId) {
            throw ValidationException::withMessages([
                'customer' => ['Unauthorized customer.'],
            ]);
        }

        if (!in_array($disputeType, [
            SellerFulfilment::DISPUTE_SHORTAGE,
            SellerFulfilment::DISPUTE_DAMAGE,
            SellerFulfilment::DISPUTE_DISPUTED,
        ])) {
            throw ValidationException::withMessages([
                'dispute_type' => ['Invalid dispute category.'],
            ]);
        }

        $fulfilment->update([
            'dispute_status'      => $disputeType,
            'dispute_reason'      => $reason,
            'dispute_reported_at' => now(),
        ]);

        SellerFulfilmentHistory::logAction(
            $fulfilment->id,
            $fulfilment->order_id,
            'dispute_reported_' . $disputeType,
            $fulfilment->fulfilment_status,
            $fulfilment->fulfilment_status,
            ['dispute_type' => $disputeType, 'reason' => $reason],
            $customerId,
            $customerName,
            'buyer',
            "Buyer reported {$disputeType}: {$reason}"
        );

        $this->triggerEligibilitySync($fulfilment, 'fulfilment_dispute_reported');

        return $fulfilment->fresh();
    }

    /**
     * Aggregate multi-seller parent order logistics progress.
     */
    public function getParentOrderLogisticsSummary(Order $parentOrder): array
    {
        // Child seller fulfilment orders
        $childOrders = $parentOrder->childOrders()->with(['fulfilment.company', 'fulfilment.seller', 'user'])->get();
        if ($childOrders->isEmpty()) {
            $childOrders = collect([$parentOrder]);
        }

        $totalSellers = $childOrders->count();
        $dispatchedCount = 0;
        $acknowledgedCount = 0;
        $sellersSummary = [];

        foreach ($childOrders as $order) {
            $fulfilment = $order->fulfilment ?: $this->getOrCreateFulfilment($order);

            if ($fulfilment->isDispatched()) {
                $dispatchedCount++;
            }
            if ($fulfilment->isAcknowledged()) {
                $acknowledgedCount++;
            }

            $sellersSummary[] = [
                'order_id'          => $order->id,
                'order_no'          => $order->orderno,
                'seller_id'         => $fulfilment->seller_user_id,
                'seller_name'       => $fulfilment->company?->name ?? $fulfilment->seller?->name ?? 'Seller #' . $fulfilment->seller_user_id,
                'fulfilment_ref'    => $fulfilment->fulfilment_ref,
                'fulfilment_status' => $fulfilment->fulfilment_status,
                'freight_terms'     => $fulfilment->freight_terms,
                'transporter_name'  => $fulfilment->transporter_name,
                'lr_number'         => $fulfilment->lr_number,
                'dispatch_origin'   => [
                    'name'    => $fulfilment->dispatch_origin_name,
                    'address' => $fulfilment->dispatch_origin_address,
                    'city'    => $fulfilment->dispatch_origin_city,
                    'state'   => $fulfilment->dispatch_origin_state,
                ],
                'handover_point'    => [
                    'type'         => $fulfilment->handover_type,
                    'name'         => $fulfilment->handover_location_name,
                    'address'      => $fulfilment->handover_address,
                    'instructions' => $fulfilment->handover_instructions,
                ],
                'transporter'       => [
                    'name'        => $fulfilment->transporter_name,
                    'contact'     => $fulfilment->transporter_contact,
                    'tracking_no' => $fulfilment->transporter_tracking_no,
                ],
                'dispatch_details'  => [
                    'dispatched_at'           => $fulfilment->dispatched_at ? $fulfilment->dispatched_at->toIso8601String() : null,
                    'lr_number'               => $fulfilment->lr_number,
                    'lr_document_path'        => $fulfilment->lr_document_path ? asset('storage/' . $fulfilment->lr_document_path) : null,
                    'package_count'           => $fulfilment->package_count,
                    'vehicle_number'          => $fulfilment->vehicle_number,
                    'estimated_delivery_date' => $fulfilment->estimated_delivery_date ? $fulfilment->estimated_delivery_date->toDateString() : null,
                ],
                'buyer_acknowledgement' => [
                    'acknowledged_at' => $fulfilment->buyer_acknowledged_at ? $fulfilment->buyer_acknowledged_at->toIso8601String() : null,
                    'notes'           => $fulfilment->buyer_acknowledgement_notes,
                    'dispute_status'  => $fulfilment->dispute_status,
                    'dispute_reason'  => $fulfilment->dispute_reason,
                ],
            ];
        }

        // Aggregate parent status
        $parentStatus = 'pending';
        if ($acknowledgedCount === $totalSellers && $totalSellers > 0) {
            $parentStatus = 'completed';
        } elseif ($dispatchedCount === $totalSellers && $totalSellers > 0) {
            $parentStatus = 'all_dispatched';
        } elseif ($dispatchedCount > 0) {
            $parentStatus = 'partially_dispatched';
        }

        return [
            'parent_order_id'       => $parentOrder->id,
            'parent_order_no'       => $parentOrder->orderno,
            'total_sellers'         => $totalSellers,
            'total_seller_orders'   => $totalSellers,
            'dispatched_count'      => $dispatchedCount,
            'acknowledged_count'    => $acknowledgedCount,
            'aggregated_status'     => $parentStatus,
            'freight_terms'         => SellerFulfilment::DEFAULT_FREIGHT_TERMS,
            'sellers'               => $sellersSummary,
        ];
    }

    /**
     * Ensure the seller only mutates their own fulfilment record.
     */
    protected function authorizeSeller(SellerFulfilment $fulfilment, int $sellerId): void
    {
        if ($fulfilment->seller_user_id !== $sellerId) {
            throw ValidationException::withMessages([
                'seller' => ['Unauthorized. You can only manage dispatch details for your own seller orders.'],
            ]);
        }
    }

    /**
     * Dispatch in-app notification to buyer on dispatch.
     */
    protected function notifyBuyerOfDispatch(SellerFulfilment $fulfilment): void
    {
        try {
            DB::table('notifications')->insert([
                'customer_id' => $fulfilment->customer_id,
                'title'       => 'Order Dispatched: #' . ($fulfilment->order?->orderno ?? $fulfilment->order_id),
                'message'     => "Your order has been dispatched via {$fulfilment->transporter_name}. LR/Bilty #{$fulfilment->lr_number}. Freight is To-Pay (Buyer-Paid).",
                'type'        => 'order_dispatched',
                'order_id'    => $fulfilment->order_id,
                'read_status' => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            // Notification failure must not roll back a valid dispatch record
        }
    }

    /**
     * Trigger Phase 6.2 eligible-order evaluation safely on fulfilment transitions.
     */
    protected function triggerEligibilitySync(SellerFulfilment $fulfilment, string $eventType): void
    {
        try {
            $order = $fulfilment->order()->first();
            if ($order) {
                app(AgentOrderEligibilityService::class)->handleOrderLifecycleEvent(
                    $order,
                    $eventType,
                    "fulfil_{$fulfilment->id}_{$eventType}_" . now()->format('YmdHis')
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Phase 6.2 eligibility sync skipped ({$eventType}): " . $e->getMessage());
        }
    }
}
