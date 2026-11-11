<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureFieldAgent
{
    /**
     * Ensure the authenticated user has a linked Field Sales Agent profile.
     * Note: Agent identity is derived strictly from the authenticated user session/token.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'code'    => 401,
                    'message' => 'Unauthenticated. Field agent authentication required.',
                ], 401);
            }
            return redirect()->route('login');
        }

        if (!$user->isFieldAgent()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'code'    => 403,
                    'message' => 'Forbidden: Field sales agent profile required for this area.',
                ], 403);
            }
            abort(403, 'Forbidden: Field sales agent profile required for this area.');
        }

        return $next($request);
    }
}

