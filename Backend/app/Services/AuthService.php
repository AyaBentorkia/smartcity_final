<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Role;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * AuthService
 *
 * Service responsable des opérations d'authentification et de gestion
 * des sessions utilisateur (inscription, connexion, déconnexion, rafraîchissement de token).
 *
 * Rôle principal :
 * - Valider/transformer les données d'entrée (hash mot de passe)
 * - Orchestrer les appels vers le repository d'auth (persist / invalidation de token)
 * - Construire des réponses adaptées (token, cookie HttpOnly)
 */
class AuthService
{
    public function __construct(
        private AuthRepositoryInterface $authRepository
    ) {}

    /**
     * Register a new user.
     *
     * Endpoint: POST /auth/register
     * Allowed users: unauthenticated visitors (public)
     * Purpose: create a new `User` record, hash the password and return
     * a JWT token for immediate authentication.
     *
     * @param array $data Incoming validated request data (name, email, password, ...)
     * @return array{user: \App\Models\User, token: string}
     * @throws \Exception on failure
     */
    public function register(array $data): array
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->authRepository->createUser($data);

        // Générer le token JWT comme login
    $token = Auth::guard('api')->login($user);

    Log::info('User registered: ' . $user->id);

    return [
        'user'  => $user,
        'token' => $token,
    ];
    }

    /**
     * Authenticate a user and return a JWT token.
     *
     * Endpoint: POST /auth/login
     * Allowed users: unauthenticated visitors with valid credentials
     * Purpose: verify credentials, load role-related relations and
     * return token + user payload used by the client app.
     *
     * @param array $credentials ['email' => string, 'password' => string]
     * @return array{token: string, token_type: string, data: \App\Models\User}
     * @throws \Exception when authentication fails
     */
    public function login(array $credentials): array
    {

    Log::info('Attempting login for: ' . $credentials['email']);
 
$token = Auth::guard('api')->attempt($credentials);
if (!$token) {
    throw new \Exception('Identifiants invalides', 401);
} 
        
 $user = Auth::guard('api')->user();

// $user;

// Log::info('rolename de login : ', ['user' => $user]);

$roleName = $user->role;
switch ($roleName) {
    case UserRole::ADMIN_MUNICIPAL->value:
        $user = $user->load('municipality');
        break;
    case UserRole::AGENT->value:
        $user = $user->load('municipality', 'category');
        break;
    default:
        $user = $user;
}
// Log::info('User logged in: ' . $user->id . ' with role: ' . $roleName);
        return [
            'token' => $token,
            'token_type'   => 'bearer',
            'data'         => $user,
        ];
    }

    /**
     * Logout the current user by invalidating the current token.
     *
     * Endpoint: POST /logout
     * Allowed users: authenticated users (must provide a valid JWT)
     * Purpose: revoke/invalidate the current JWT so it can no longer be used.
     *
     * @param User $user The user to logout
     * @return void
     */
    public function logout(User $user): void
    {
        $this->authRepository->deleteCurrentToken($user);
    }

    /**
     * Return the authenticated user's profile with relevant relations.
     *
     * Endpoint: GET /me
     * Allowed users: authenticated users
     * Purpose: provide the client with the full user profile (municipality,
     * category depending on role) used by the app to render UI and permissions.
     *
     * @param User $user Authenticated user instance
     * @return User Loaded user model with relations
     */
    public function getProfile(User $user): User
    {
        $user = $user;
$roleName = $user->role;
    //     switch ($roleName) {
    // case UserRole::ADMIN_MUNICIPAL->value:
    //     return $user->load( 'municipality');

    // case UserRole::AGENT->value:
    //     return $user->load( 'municipality', 'category');

    // default:
        return $user->load( 'municipality', 'category');
}
//$user->load('role', 'municipality', 'category'); // charger d'abord
    // Log::info('Fetching profile for user:', ['user' => $user]);

        
    

    /**
     * Refresh the JWT token and return a cookie wrapper.
     *
     * Endpoint: POST /refresh
     * Allowed users: authenticated users (with an existing token)
     * Purpose: issue a fresh JWT and return it as an HttpOnly cookie.
     *
     * @param User $user Authenticated user
     * @return array{cookie: \Symfony\Component\HttpFoundation\Cookie}
     * @throws \Exception on refresh failure
     */
    public function refresh(User $user): array
    {
         try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            throw new \Exception('Impossible de rafraîchir le token', 401);
        }
 
        $cookie = $this->buildCookie($newToken);
 
        return ['cookie' => $cookie];
    }
    /**
     * Build an HttpOnly cookie containing the JWT token.
     *
     * Internal helper used by `refresh()` to create a secure cookie
     * that the frontend can use for authenticated requests.
     *
     * @param string $token JWT token string
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    private function buildCookie(string $token): \Symfony\Component\HttpFoundation\Cookie
    {
        return Cookie::make(
            name:     'access_token',
        value:    $token,
        minutes:  60 * 24,
        path:     '/',
        domain:   null,
        secure:   false,   // ← false en dev (HTTP)
        httpOnly: true,
        sameSite: 'Lax'
        );
    }
}