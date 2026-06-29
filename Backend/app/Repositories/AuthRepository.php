<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * AuthRepository
 *
 * Accès aux données pour l'authentification utilisateur. Encapsule
 * les opérations CRUD simples sur le modèle `User` et la gestion des
 * tokens JWT (création, invalidation).
 */
class AuthRepository implements AuthRepositoryInterface
{
    public function createUser(array $data): User
    {
        return User::create($data);
    }

    /**
     * Create a new user in the database.
     *
     * Called by the AuthService during registration. Expects already
     * validated and transformed data (hashed password).
     *
     * @param array $data
     * @return User
     */

    public function createToken(User $user, string $tokenName = 'app-token'): string
    {
        return JWTAuth::fromUser($user);
    }

    /**
     * Create a JWT token for the given user.
     *
     * Used to return a token after registration/login flows.
     *
     * @param User $user
     * @param string $tokenName
     * @return string JWT token
     */

//     public function deleteCurrentToken(User $user): void
//     {
//         try {
//             $token = JWTAuth::getToken();

//             if ($token) {
//                 // Calculer le TTL restant du token
//                 $payload  = JWTAuth::getPayload($token);
//                 $expiresAt = $payload->get('exp');
//                 $ttl      = $expiresAt - time();

//                 if ($ttl > 0) {
//                     // Stocker dans Redis DB 0 avec TTL = durée restante
//                     $key = 'blacklist:' . hash('sha256', (string) $token);
//                     Redis::connection()->setex($key, $ttl, 'true');
//                     Log::info('Token blacklisted', [
//                         'user_id' => $user->id,
//                         'ttl_seconds' => $ttl
//                     ]);
//                 }
//             }

//             JWTAuth::invalidate($token);

//         } catch (\Exception $e) {
//             Log::error('Logout error: ' . $e->getMessage());
//         }
//     }

//     public function deleteAllTokens(User $user): void
//     {
//         $this->deleteCurrentToken($user);
//     }
// }
    /**
     * Invalidate the current token for the given user.
     *
     * Called on logout to prevent reuse of the current JWT.
     *
     * @param User $user
     * @return void
     */
    public function deleteCurrentToken(User $user): void
{
    try {
        JWTAuth::invalidate(JWTAuth::getToken());
        Log::info('Token invalidated', ['user_id' => $user->id]);
    } catch (\Exception $e) {
        Log::error('Logout error: ' . $e->getMessage());
    }
}

    /**
     * Invalidate all tokens for the user.
     *
     * Implementation currently calls `deleteCurrentToken` but can be
     * extended to blacklist multiple tokens in Redis if needed.
     *
     * @param User $user
     * @return void
     */
    public function deleteAllTokens(User $user): void
    {
        $this->deleteCurrentToken($user);
    }
}