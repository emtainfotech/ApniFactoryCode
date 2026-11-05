<?php

namespace App\Services;

use App\Models\TaxMaster;
use App\Models\TaxMasterAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaxMasterService
{
    /**
     * Create a new GST/HSN tax master record.
     */
    public function createRecord(array $data, int $userId, string $role = 'admin', ?string $actorName = null): TaxMaster
    {
        $hsnCode = trim($data['hsn_code']);

        // 1. Validate HSN/SAC format
        if (!TaxMaster::isValidHsnOrSac($hsnCode)) {
            throw ValidationException::withMessages([
                'hsn_code' => ['Invalid HSN/SAC format. HSN must be 4, 6, or 8 digits; SAC must be 6 digits starting with 99.'],
            ]);
        }

        $effectiveFrom = $data['effective_from'];
        $effectiveTo = $data['effective_to'] ?? null;

        // 2. Prevent overlapping effective date ranges for the same HSN/SAC code
        $this->validateNoDateOverlap($hsnCode, $effectiveFrom, $effectiveTo);

        // 3. Determine initial approval status
        $approvalStatus = ($role === 'admin' && ($data['approval_status'] ?? null) === TaxMaster::STATUS_APPROVED)
            ? TaxMaster::STATUS_APPROVED
            : TaxMaster::STATUS_PENDING;

        return DB::transaction(function () use ($data, $hsnCode, $effectiveFrom, $effectiveTo, $approvalStatus, $userId, $role, $actorName) {
            $taxMaster = TaxMaster::create([
                'hsn_code'            => $hsnCode,
                'description'         => $data['description'],
                'gst_rate'            => (float) $data['gst_rate'],
                'type'                => $data['type'] ?? TaxMaster::TYPE_GOODS,
                'effective_from'      => $effectiveFrom,
                'effective_to'        => $effectiveTo,
                'source_reference'    => $data['source_reference'] ?? null,
                'approval_status'     => $approvalStatus,
                'last_verified_at'    => $approvalStatus === TaxMaster::STATUS_APPROVED ? now() : null,
                'verified_by_user_id' => $approvalStatus === TaxMaster::STATUS_APPROVED ? $userId : null,
                'approved_by_user_id' => $approvalStatus === TaxMaster::STATUS_APPROVED ? $userId : null,
                'created_by_user_id'  => $userId,
                'version'             => 1,
            ]);

            TaxMasterAuditLog::logAction(
                $taxMaster->id,
                'created',
                null,
                $taxMaster->toArray(),
                $userId,
                $actorName ?? 'Admin',
                $role,
                'New tax classification record created.'
            );

            return $taxMaster;
        });
    }

    /**
     * Update or version an existing tax master record.
     */
    public function updateRecord(TaxMaster $record, array $data, int $userId, string $role = 'admin', ?string $actorName = null): TaxMaster
    {
        $hsnCode = trim($data['hsn_code'] ?? $record->hsn_code);

        if (!TaxMaster::isValidHsnOrSac($hsnCode)) {
            throw ValidationException::withMessages([
                'hsn_code' => ['Invalid HSN/SAC format.'],
            ]);
        }

        $effectiveFrom = $data['effective_from'] ?? $record->effective_from->toDateString();
        $effectiveTo = array_key_exists('effective_to', $data) ? $data['effective_to'] : ($record->effective_to ? $record->effective_to->toDateString() : null);

        $this->validateNoDateOverlap($hsnCode, $effectiveFrom, $effectiveTo, $record->id);

        return DB::transaction(function () use ($record, $data, $hsnCode, $effectiveFrom, $effectiveTo, $userId, $role, $actorName) {
            $previousData = $record->toArray();

            // If the record was already approved, create a new version to preserve historical auditability
            if ($record->isApproved() && (isset($data['gst_rate']) && (float)$data['gst_rate'] !== (float)$record->gst_rate)) {
                $newRecord = TaxMaster::create([
                    'hsn_code'            => $hsnCode,
                    'description'         => $data['description'] ?? $record->description,
                    'gst_rate'            => (float) $data['gst_rate'],
                    'type'                => $data['type'] ?? $record->type,
                    'effective_from'      => $effectiveFrom,
                    'effective_to'        => $effectiveTo,
                    'source_reference'    => $data['source_reference'] ?? $record->source_reference,
                    'approval_status'     => ($role === 'admin') ? TaxMaster::STATUS_APPROVED : TaxMaster::STATUS_PENDING,
                    'last_verified_at'    => ($role === 'admin') ? now() : null,
                    'verified_by_user_id' => ($role === 'admin') ? $userId : null,
                    'approved_by_user_id' => ($role === 'admin') ? $userId : null,
                    'created_by_user_id'  => $userId,
                    'version'             => $record->version + 1,
                    'previous_version_id' => $record->id,
                ]);

                // Close effective_to of previous record if not closed
                if (!$record->effective_to || $record->effective_to >= $effectiveFrom) {
                    $record->update(['effective_to' => date('Y-m-d', strtotime($effectiveFrom . ' -1 day'))]);
                }

                TaxMasterAuditLog::logAction(
                    $newRecord->id,
                    'version_created',
                    $previousData,
                    $newRecord->toArray(),
                    $userId,
                    $actorName,
                    $role,
                    "New version {$newRecord->version} created from record #{$record->id}."
                );

                return $newRecord;
            }

            // Otherwise update in-place (e.g. pending/draft updates)
            $record->update([
                'hsn_code'         => $hsnCode,
                'description'      => $data['description'] ?? $record->description,
                'gst_rate'         => isset($data['gst_rate']) ? (float)$data['gst_rate'] : $record->gst_rate,
                'type'             => $data['type'] ?? $record->type,
                'effective_from'   => $effectiveFrom,
                'effective_to'     => $effectiveTo,
                'source_reference' => $data['source_reference'] ?? $record->source_reference,
            ]);

            TaxMasterAuditLog::logAction(
                $record->id,
                'updated',
                $previousData,
                $record->toArray(),
                $userId,
                $actorName,
                $role,
                'Tax classification updated.'
            );

            return $record->fresh();
        });
    }

    /**
     * Approve a pending tax master classification (Privileged Finance/Admin role).
     */
    public function approveRecord(TaxMaster $record, int $adminId, ?string $actorName = null, ?string $notes = null): TaxMaster
    {
        $prev = $record->toArray();

        $record->update([
            'approval_status'     => TaxMaster::STATUS_APPROVED,
            'approved_by_user_id' => $adminId,
            'verified_by_user_id' => $adminId,
            'last_verified_at'    => now(),
            'rejection_reason'    => null,
        ]);

        TaxMasterAuditLog::logAction(
            $record->id,
            'approved',
            $prev,
            $record->toArray(),
            $adminId,
            $actorName ?? 'Finance Admin',
            'admin',
            $notes ?? 'Classification approved by administrator.'
        );

        return $record->fresh();
    }

    /**
     * Reject a pending tax master classification.
     */
    public function rejectRecord(TaxMaster $record, string $reason, int $adminId, ?string $actorName = null): TaxMaster
    {
        $prev = $record->toArray();

        $record->update([
            'approval_status'  => TaxMaster::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        TaxMasterAuditLog::logAction(
            $record->id,
            'rejected',
            $prev,
            $record->toArray(),
            $adminId,
            $actorName ?? 'Finance Admin',
            'admin',
            "Classification rejected: {$reason}"
        );

        return $record->fresh();
    }

    /**
     * Look up the authoritative active tax master record for an HSN code on a given date.
     */
    public function getEffectiveTax(string $hsnCode, ?string $date = null): ?TaxMaster
    {
        $hsnCode = trim($hsnCode);
        $date = $date ? date('Y-m-d', strtotime($date)) : date('Y-m-d');

        return TaxMaster::query()
            ->approved()
            ->effectiveOn($date)
            ->where('hsn_code', $hsnCode)
            ->orderBy('version', 'desc')
            ->first();
    }

    /**
     * Check if a proposed effective date range overlaps with an existing approved/active record for the same HSN.
     */
    public function validateNoDateOverlap(string $hsnCode, string $from, ?string $to, ?int $excludeId = null): void
    {
        $query = TaxMaster::where('hsn_code', $hsnCode)
            ->whereIn('approval_status', [TaxMaster::STATUS_APPROVED, TaxMaster::STATUS_PENDING]);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $query->where(function ($q) use ($from, $to) {
            if ($to) {
                // (ExistingFrom <= NewTo) AND (ExistingTo IS NULL OR ExistingTo >= NewFrom)
                $q->where('effective_from', '<=', $to)
                  ->where(function ($sub) use ($from) {
                      $sub->whereNull('effective_to')
                          ->orWhere('effective_to', '>=', $from);
                  });
            } else {
                // New range has no end date: (ExistingTo IS NULL OR ExistingTo >= NewFrom)
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $from);
            }
        });

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'effective_from' => ["An active or approved tax record for HSN '{$hsnCode}' already overlaps with this date range ({$from} to " . ($to ?? 'Indefinite') . ').'],
            ]);
        }
    }
}

