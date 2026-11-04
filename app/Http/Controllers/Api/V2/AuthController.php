<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseApiController
{
    /**
     * POST /api/v2/auth/login
     * Issues Sanctum token for Admin or Seller portal users.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors()->toArray(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->sendError('Invalid credentials.', [], 401);
        }

        $deviceName = $request->device_name ?: 'apnifactory-api-client';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->isAdmin(),
                'is_seller' => $user->isSeller(),
                'company_id' => $user->company->id ?? null,
                'company_name' => $user->company->name ?? null,
            ],
        ], 'Authenticated successfully.');
    }

    /**
     * GET /api/v2/auth/user
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->sendError('Unauthenticated.', [], 401);
        }

        return $this->sendResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->isAdmin(),
            'is_seller' => $user->isSeller(),
            'company_id' => $user->company->id ?? null,
            'company_name' => $user->company->name ?? null,
        ], 'User profile retrieved.');
    }

    /**
     * POST /api/v2/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return $this->sendResponse([], 'Logged out successfully.');
    }
}
