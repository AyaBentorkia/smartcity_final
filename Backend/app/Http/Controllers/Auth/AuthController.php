<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Log;

#[OA\Tag(name: 'Auth', description: 'Authentification des utilisateurs')]
/**
 * AuthController
 *
 * Expose les endpoints d'authentification (inscription, connexion,
 * déconnexion, rafraîchissement de token, profil). Ce controller délègue
 * la logique métier au `AuthService` et se contente de formater les
 * réponses JSON et de gérer les exceptions HTTP.
 */
class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Register endpoint.
     *
     * Endpoint: POST /auth/register
     * Allowed users: public (unauthenticated)
     * Purpose: create a new user and return a JWT token for immediate use.
     *
     * @param StoreUserRequest $request Validated registration payload
     * @return JsonResponse 201 on success with `user` and `token`
     */
    #[OA\Post(
        path: '/auth/register',
        summary: "Inscription d'un nouvel utilisateur",
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'role', 'phone', 'city', 'cin', 'birthdate'],
                properties: [
                    new OA\Property(property: 'name',      type: 'string',  example: 'Jean Dupont'),
                    new OA\Property(property: 'email',     type: 'string',  format: 'email', example: 'jean@example.com'),
                    new OA\Property(property: 'password',  type: 'string',  example: 'password123'),
                    new OA\Property(property: 'role',      type: 'string',  enum: ['citizen', 'agent', 'admin_municipal', 'super admin'], example: 'citizen'),
                    new OA\Property(property: 'phone',     type: 'string',  example: '512345678'),
                    new OA\Property(property: 'city',      type: 'string',  example: 'Sousse'),
                    new OA\Property(property: 'cin',       type: 'string',  example: '12222222'),
                    new OA\Property(property: 'birthdate', type: 'string',  format: 'date', example: '1990-01-15'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Utilisateur créé avec succès'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function register(StoreUserRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'Utilisateur créé',
            'data'    => $result['user'],
            'token'   => $result['token'],
        ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Login endpoint.
     *
     * Endpoint: POST /auth/login
     * Allowed users: public (must provide valid credentials)
     * Purpose: authenticate the user and return JWT token and user data.
     *
     * @param Request $request Contains `email` and `password`
     * @return JsonResponse 200 with token and user data
     */
    #[OA\Post(
        path: '/auth/login',
        summary: 'Connexion utilisateur',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email',    type: 'string', format: 'email', example: 'admin@test.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'secret123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Token retourné avec succès'),
            new OA\Response(response: 401, description: 'Identifiants invalides'),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        try {
            $result = $this->authService->login($credentials);
return response()->json([
            'message' => 'Connecté avec succès',
            'data'    => $result['data'],
            'token' =>$result['token']
        ]);
} catch (\Exception $e) {
                     Log::error('Login error', [
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
    ]);
    return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);

        }
    }

    /**
     * Logout endpoint.
     *
     * Endpoint: POST /logout
     * Allowed users: authenticated users
     * Purpose: invalidate the current JWT so it can no longer be used.
     *
     * @param Request $request
     * @return JsonResponse 200 on success
     */
    #[OA\Post(
        path: '/logout',
        summary: 'Déconnexion',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Déconnexion réussie'),
        ]
    )]
   public function logout(Request $request): JsonResponse
{
    try {
        $this->authService->logout(auth('api')->user());
        return response()->json(['message' => 'Déconnecté avec succès']);
    } catch (\Exception $e) {
        return response()->json([
            'message' => "Impossible d'invalider le token"
        ], 500);
    }
}

    /**
     * Get current user profile.
     *
     * Endpoint: GET /me
     * Allowed users: authenticated users
     * Purpose: return the authenticated user's profile with relations
     * (municipality / category) depending on their role.
     *
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/me',
        summary: 'Récupérer mon profil',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Profil utilisateur'),
        ]
    )]
    public function me(): JsonResponse
    {
        $user = $this->authService->getProfile(auth('api')->user());

        return response()->json([
            'message' => 'Mon profil',
            'data'    => $user,
        ]);
    }

    /**
     * Refresh JWT token.
     *
     * Endpoint: POST /refresh
     * Allowed users: authenticated users
     * Purpose: issue a fresh JWT and set it as an HttpOnly cookie.
     *
     * @return JsonResponse with cookie
     */
    #[OA\Post(
        path: '/refresh',
        summary: 'Rafraîchir le token',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Nouveau token généré'),
        ]
    )]
    public function refresh(): JsonResponse
    {
        try {
            $result = $this->authService->refresh(auth('api')->user());

            return response()->json(['message' => 'Token rafraîchi'])->withCookie($result['cookie']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Impossible de rafraîchir le token',
                'error'   => $e->getMessage()
            ], 401);
        }
    }
}