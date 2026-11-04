<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSellerOwnership
{
    /**
     * Verify the authenticated user belongs to the target seller company
     * before allowing any seller-scoped action.
     * Company ID is always derived from session, never from client input.
     */
    public function handle(Request $request, Closure $next, string $permission = null)
    {
        $user = $request->user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        // System Admin bypasses seller isolation
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Resolve company from session-authenticated user (never from request body)
        $company = $user->resolveSellerCompany();
        if (!$company) {
            return $request->expectsJson()
                ? response()->json(['status' => false, 'message' => 'No seller company found for this user.'], 403)
                : abort(403, 'No seller company found.');
        }

        // If a specific permission is required, check it
        if ($permission && !$user->hasSellerPermission($permission)) {
            return $request->expectsJson()
                ? response()->json(['status' => false, 'message' => "Permission denied: {$permission}"], 403)
                : abort(403, "Permission denied: {$permission}");
        }

        // Share the resolved company with the request attributes for downstream use
        $request->attributes->set('seller_company', $company);
        $request->attributes->set('seller_company_id', $company->id);

        return $next($request);
    }
}

