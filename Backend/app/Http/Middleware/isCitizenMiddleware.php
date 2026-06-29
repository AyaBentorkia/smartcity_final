<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;
use App\Models\Role;

class IsCitizenMiddleware
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
    $user = $request->get('_authenticated_user') ?? auth('api')->user();

        if (!$user) {
            // \Log::info('IsCitizenMiddleware: no authenticated user', ['url' => $request->fullUrl()]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // \Log::info('IsAdminMiddleware: user role value', ['user_id' => $user->id, 'roleValue' => $roleValue]);

        if ($user->role !== UserRole::CITIZEN) {
            // \Log::warning('IsAdminMiddleware: forbidden - not admin', ['user_id' => $user->id, 'roleValue' => $role]);
            return response()->json(['message' => 'Forbidden: citizen only'], 403);
        }

        return $next($request);
    }
}
