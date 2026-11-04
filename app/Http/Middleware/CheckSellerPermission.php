<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSellerPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->hasSellerPermission($permission)) {
            return $request->expectsJson()
                ? response()->json(['status' => false, 'message' => "Permission denied: {$permission}"], 403)
                : abort(403, "Permission denied: {$permission}");
        }

        return $next($request);
    }
}

