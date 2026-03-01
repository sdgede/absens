<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        Log::channel('auth_log')->info('Login attempt', [
            'email' => $credentials['email'],
            'ip' => $request->ip(),
        ]);

        $token = auth('api')->attempt($credentials);

        if (!$token) {
            Log::channel('auth_error')->warning('Login failed', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Kredensial tidak valid'], 401);
        }

        $user = auth('api')->user();

        $user->update([
            'last_login_at' => now(),
            'fcm_token'     => $request->fcm_token ?? $user->fcm_token,
        ]);

        Log::channel('auth_log')->info('Login success', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return response()->json([
            'user'          => $user,
            'access_token'  => $token,
            'refresh_token' => null,
            'token_type'    => 'bearer',
            'expires_in'    => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        if ($user) {
            Log::channel('auth_log')->info('Logout', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            $user->update(['fcm_token' => null]);
        }

        auth('api')->invalidate(true);

        return response()->json(['message' => 'Successfully logged out']);
    }


    public function tokens(Request $request): JsonResponse
    {
        $payload = auth('api')->payload();

        return response()->json([
            'tokens' => [
                [
                    'jti'        => $payload->get('jti'),
                    'issued_at'  => date('Y-m-d H:i:s', $payload->get('iat')),
                    'expires_at' => date('Y-m-d H:i:s', $payload->get('exp')),
                    'device'     => auth('api')->user()->fcm_token,
                ],
            ],
        ]);
    }

    /**
     * Get current authenticated user with relations.
     */
    public function me(Request $request): JsonResponse
    {
        $user = auth('api')->user()
            ->load(['tenant', 'branch', 'roles', 'faceEmbedding']);

        Log::channel('auth_log')->info('Fetch user profile', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);

        $user->has_face_registered = $user->faceEmbedding !== null;

        return response()->json(['user' => $user]);
    }
}
