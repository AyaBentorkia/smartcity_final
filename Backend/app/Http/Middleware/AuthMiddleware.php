<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserStatus as Status;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        // ── 1. Vérifier l'authentification + blacklist (tymon gère les deux)
        try {
            $user = auth('api')->user();
        } catch (\Tymon\JWTAuth\Exceptions\TokenBlacklistedException $e) {
            return response()->json(['message' => 'Token révoqué, veuillez vous reconnecter'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!$user) {
            return response()->json(['message' => 'Unauthorized: user not found'], 401);
        }

        if ($user->status !== Status::ACTIVE->value) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is deactivated'
            ], 403);
        }

        $request->merge(['_authenticated_user' => $user]);

        return $next($request);
    }
}