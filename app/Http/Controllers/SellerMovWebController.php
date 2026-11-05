<?php

namespace App\Http\Controllers;

use App\Models\CompanyAuditLog;
use Illuminate\Http\Request;

class SellerMovWebController extends Controller
{
    /**
     * GET /seller/settings/mov
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            abort(403, 'No seller company account associated with authenticated user.');
        }

        $auditHistory = CompanyAuditLog::where('company_id', $company->id)
            ->where('action_type', 'min_order_value_change')
            ->orderBy('id', 'desc')
            ->limit(15)
            ->get();

        return view('seller.settings.mov', [
            'company'      => $company,
            'auditHistory' => $auditHistory,
        ]);
    }

    /**
     * POST /seller/settings/mov
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $company = $user ? $user->resolveSellerCompany() : null;

        if (!$company) {
            abort(403, 'No seller company account associated with authenticated user.');
        }

        $isOwner = ($company->user_id === $user->id);
        $isAdmin = (str_contains($user->email, 'admin'));
        $hasPermission = $user->hasSellerPermission('pricing.update');

        if (!$isOwner && !$isAdmin && !$hasPermission) {
            abort(403, 'You do not have permission to update Minimum Order Value settings.');
        }

        $validated = $request->validate([
            'min_order_value' => 'required|numeric|min:0|max:10000000',
            'reason'          => 'nullable|string|max:500',
        ]);

        $oldValue = $company->getRawMinOrderValue();
        $newValue = round((float)$validated['min_order_value'], 2);

        $company->minordervalue = $newValue;
        $company->save();

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

        return redirect()->route('seller.settings.mov.index')
            ->with('success', 'Minimum Order Value updated successfully.');
    }
}
