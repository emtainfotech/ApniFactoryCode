<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerMovApiController extends Controller
{
    /**
     * GET /api/v2/seller/mov
     * Retrieve authenticated seller company's current MOV configuration and history.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            return response()->json([
                'status'  => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        if (!$user->hasSellerPermission('pricing.view') && $company->user_id !== $user->id && !str_contains($user->email, 'admin')) {
            return response()->json([
                'status'  => false,
                'message' => 'You do not have permission to view seller commercial settings.',
            ], 403);
        }

        $auditHistory = CompanyAuditLog::where('company_id', $company->id)
            ->where('action_type', 'min_order_value_change')
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'company_id'       => $company->id,
                'company_name'     => $company->name,
                'min_order_value'  => $company->getRawMinOrderValue(),
                'status'           => $company->status,
                'audit_history'    => $auditHistory,
            ],
        ]);
    }

    /**
     * PUT /api/v2/seller/mov
     * Update authenticated seller company's Minimum Order Value (MOV).
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            return response()->json([
                'status'  => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        // Authorization: Company owner, admin, or staff with pricing.update
        $isOwner = ($company->user_id === $user->id);
        $isAdmin = (str_contains($user->email, 'admin'));
        $hasPermission = $user->hasSellerPermission('pricing.update');

        if (!$isOwner && !$isAdmin && !$hasPermission) {
            return response()->json([
                'status'  => false,
                'message' => 'You do not have permission to update Minimum Order Value settings.',
            ], 403);
        }

        $validated = $request->validate([
            'min_order_value' => 'required|numeric|min:0|max:10000000',
            'reason'          => 'nullable|string|max:500',
        ]);

        $oldValue = $company->getRawMinOrderValue();
        $newValue = round((float)$validated['min_order_value'], 2);

        $company->minordervalue = $newValue;
        $company->save();

        // Record structured audit trail
        CompanyAuditLog::logChange(
            $company->id,
            'min_order_value_change',
            "Updated Minimum Order Value to ₹" . number_format($newValue, 2),
            $validated['reason'] ?? "Minimum Order Value updated from ₹" . number_format($oldValue, 2) . " to ₹" . number_format($newValue, 2),
            ['minordervalue' => $oldValue],
            ['minordervalue' => $newValue],
            $user->id,
            $user->name,
            $isAdmin ? 'admin' : 'seller'
        );

        return response()->json([
            'status'  => true,
            'message' => 'Minimum Order Value (MOV) updated successfully.',
            'data'    => [
                'company_id'      => $company->id,
                'company_name'    => $company->name,
                'old_mov'         => $oldValue,
                'min_order_value' => $newValue,
                'updated_at'      => $company->updated_at->toIso8601String(),
            ],
        ]);
    }
}
