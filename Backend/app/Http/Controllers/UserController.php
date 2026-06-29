<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;
use App\Enums\UserStatus;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;


#[OA\Tag(name: 'Users', description: 'Gestion des utilisateurs')]

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

     /**
     * List users (paginated) for super admin.
     *
     * Endpoint: GET /admin/users
     * Allowed users: super admin
     * Purpose: return paginated list used by admin dashboard.
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/admin/users',
        summary: 'Lister tous les utilisateurs',
        tags: ['Users'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Liste des utilisateurs')]
    )]
   
    public function index(Request $request): JsonResponse
    {
        $result = $this->userService->getAllPaginated(
            perPage: $request->integer('per_page', 10),
            page:    $request->integer('page', 1),
        );

        return response()->json([
            'message' => 'Liste des utilisateurs',
            'data'    => $result['data'],
            'meta'    => $result['meta'],
        ]);
    }
     /**
     * List agents of admin's municipality filtered by category.
     *
     * Endpoint: GET /admin_manager/users/:categoryId
     * Allowed users: admin municipal
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/admin_manager/users/:categoryId',
        summary: 'Lister les utilisateurs de ma municipalité',
        tags: ['Users'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste des utilisateurs de la municipalité'),
            new OA\Response(response: 404, description: 'Municipalité non trouvée'),
        ]
    )]
  
    public function myMunicipalityUsersByCategory(int $categoryId): JsonResponse
    {
        try {
            $users = $this->userService->getMunicipalityUsersByCategory(auth('api')->user(),$categoryId);

            return response()->json([
                'message' => 'agents of my municipality',
                'data'    => $users,
            ]);
        } catch (\Exception $e) {
             Log::error('Erreur dans getNearby', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);

            return response()->json([
                'message' => $e->getMessage()
            ],  500);
        }
    }
     /**
     * List agents of the admin's municipality (paginated).
     *
     * Endpoint: GET /admin_manager/users
     * Allowed users: admin municipal
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/admin_manager/users',
        summary: 'Lister les utilisateurs de ma municipalité',
        tags: ['Users'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste des utilisateurs de la municipalité'),
            new OA\Response(response: 404, description: 'Municipalité non trouvée'),
        ]
    )]
   
public function myMunicipalityUsers(Request $request): JsonResponse
    {
        try {
            $users = $this->userService->getMunicipalityUsersPaginated(
                auth('api')->user(),
                perPage: $request->integer('per_page', 10),
                page:    $request->integer('page', 1),
            
            );

            return response()->json([
                'message' => 'agents of my municipality',
                'data'    => $users['data'],
                'meta'    => $users['meta'],
            ]);
        } catch (\Exception $e) {
             Log::error('Erreur dans getNearby', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);

            return response()->json([
                'message' => $e->getMessage()
            ],  500);
        }
    }

   /**
 * Show a single user.
 *
 * Endpoint: GET /admin/users/{user}
 * Allowed users: super admin
 * @param User $user
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/admin/users/{user}',
        summary: 'Afficher un utilisateur',
        tags: ['Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Utilisateur affiché')]
    )]
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'message' => 'user',
            'data'    => $user,
        ]);
    }

    /**
     * Create a new user (super admin).
     *
     * Endpoint: POST /admin/users
     * Allowed users: super admin
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/admin/users',
        summary: 'Créer un utilisateur',
        tags: ['Users'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'phoneNumber', 'city', 'password', 'cin', 'municipality', 'role'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'responsable A'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'resp1@gmail.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'role', type: 'string', example: 'administarteur de la municipalité'),
                    new OA\Property(property: 'municipality', type: 'string', example: 'Municipalité de Monastir'),
                    new OA\Property(property: 'phoneNumber', type: 'string', example: '9995997'),
                    new OA\Property(property: 'cin', type: 'string', example: '12558122'),
                    new OA\Property(property: 'address', type: 'string', example: '123 Rue de Monastir'),
                    new OA\Property(property: 'service', type: 'string'),
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Utilisateur créé')]
    )]
    
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            if($request['municipality']){
                $data['municipality']=$request['municipality'];
            }
            if($request['service']){
                $data['service']=$request['service'];
            }
            $user = $this->userService->createUser($data);

            return response()->json([
                'message' => 'User created',
                'data'    => $user,
            ], 201);
       } catch (\Exception $e) {
             Log::error('Erreur dans getNearby', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);

            return response()->json([
                'message' => $e->getMessage()
            ],  500);
        }
    }

    /**
     * Create an employee inside the admin's municipality.
     *
     * Endpoint: POST /admin_manager/users
     * Allowed users: admin municipal
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/admin_manager/users',
        summary: "Créer un employé dans ma municipalité",
        tags: ['Users'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'role'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'agent A'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'agentA@gmail.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'phoneNumber', type: 'string', example: '98458988'),
                    new OA\Property(property: 'cin', type: 'string', example: '12578122'),
                    new OA\Property(property: 'role', type: 'string', enum: ['agent'], example: 'agent'),
                    new OA\Property(property: 'service', type: 'string', example: 'Voirie et Travaux'),
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: 'Employé créé')]
    )]
  
    public function storeEmployee(StoreUserRequest $request): JsonResponse
    {
        try {
            Log::info("Enterred");
            $data = $request->validated();
            $user = $this->userService->createEmployee(
                $data,
                auth('api')->user()
            );

            return response()->json([
                'message' => 'User created',
                'data'    => $user,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur dans update status', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
 * Update a user's data.
 *
 * Endpoint: PUT /admin/users/{user}
 * Allowed users: super admin
 * @param UpdateUserRequest $request
 * @param User $user
 * @return JsonResponse
 */
    #[OA\Put(
        path: '/admin/users/{user}',
        summary: 'Mettre à jour un utilisateur',
        tags: ['Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent()),
        responses: [new OA\Response(response: 200, description: 'Utilisateur mis à jour')]
    )]
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            Log::info("Enterred",[
                'user_id' => $user->id,
                'request_data' => $request->validated(),
            ]);
            $updatedUser = $this->userService->updateUser($user, $request->validated());

            return response()->json([
                'message' => 'User updated',
                'data'    => $updatedUser,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans update status', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
 * Update the authenticated user's own profile.
 *
 * Endpoint: PUT /me
 * Allowed users: any authenticated user
 * @param UpdateUserRequest $request
 * @return JsonResponse
 */
    #[OA\Put(
        path: '/me',
        summary: 'Mettre à jour mon profil',
        tags: ['Users'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent()),
        responses: [new OA\Response(response: 200, description: 'Profil mis à jour')]
    )]
    public function updateProfile(UpdateUserRequest $request): JsonResponse
    {
        try {
             Log::info("Enterred",[
                'user_id' => auth('api')->id(),
                'request_data' => $request->validated(),
            ]);
            $updatedUser = $this->userService->updateProfile(
                auth('api')->user(),
                $request->validated()
            );

            return response()->json([
                'message' => 'User updated',
                'data'    => $updatedUser,
            ]);
        } catch (\Exception $e) {
             Log::error('Erreur dans update status', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
 * Update a user's account status.
 *
 * Endpoint: PATCH /admin/users/{user}/status
 * Allowed users: super admin
 * @param Request $request
 * @param User $user
 * @return JsonResponse
 */
    #[OA\Patch(
        path: '/admin/users/{user}/status',
        summary: "Mettre à jour le statut d'un utilisateur",
        tags: ['Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'suspended']),
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: 'Statut mis à jour')]
    )]
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        try {
            Log::info("update status controller");

            $request->validate([
                'status' => ['required', Rule::in(array_map(fn($c) => $c->value, UserStatus::cases()))],
            ]);

            $updatedUser = $this->userService->updateStatus($user, $request->input('status'));

            return response()->json([
                'message' => 'user status updated',
                'data'    => $updatedUser,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans update status', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Delete a user (admin).
     */
    #[OA\Delete(
        path: '/admin/users/{user}',
        summary: 'Supprimer un utilisateur',
        tags: ['Users'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Utilisateur supprimé')]
    )]
    public function destroy(User $user): JsonResponse
    {
        try {
            $this->userService->deleteUser($user);

            return response()->json(['message' => 'User deleted']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}