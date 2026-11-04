<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCatalogueAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'code' => 403,
                    'message' => 'Forbidden: Administrator privileges required for this action.',
                ], 403);
            }
            abort(403, 'Forbidden: Administrator privileges required for this action.');
        }

        return $next($request);
    }
}

