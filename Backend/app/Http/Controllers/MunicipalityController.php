<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMunicipalityRequest;
use App\Http\Requests\UpdateMunicipalityRequest;
use App\Models\Municipality;
use App\Services\MunicipalityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Log;

#[OA\Tag(name: 'Municipalities', description: 'Gestion des municipalités')]
class MunicipalityController extends Controller
{
    public function __construct(
        private MunicipalityService $municipalityService
    ) {}

    /**
 * List all municipalities (paginated).
 * Endpoint: GET /municipalities | Allowed users: public
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/municipalities',
        summary: 'Lister toutes les municipalités',
        tags: ['Municipalities'],
        responses: [
            new OA\Response(response: 200, description: 'Liste des municipalités'),
        ]
    )]
   public function index(): JsonResponse
{
    $municipalities = Municipality::with('city.governorate')->paginate(10);
    return response()->json([
        'message' => 'Municipalities retrieved successfully',
        'data'    => $municipalities,
    ]);
}

    /**
 * Show a single municipality.
 * Endpoint: GET /municipalities/{municipality} | Allowed users: public
 * @param Municipality $municipality
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/municipalities/{municipality}',
        summary: 'Voir une municipalité spécifique',
        tags: ['Municipalities'],
        parameters: [
            new OA\Parameter(name: 'municipality', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails de la municipalité'),
            new OA\Response(response: 404, description: 'Municipalité non trouvée'),
        ]
    )]
    public function show(Municipality $municipality): JsonResponse
{
    return response()->json([
        'message' => 'Municipality retrieved successfully',
        'data'    => $municipality->load('city.governorate'),
    ]);
}

    /**
 * Get the authenticated user's own municipality.
 * Endpoint: GET /admin_manager/my-municipality | Allowed users: admin municipal / agent
 * @param Request $request
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/admin_manager/my-municipality',
        summary: 'Récupérer ma municipalité',
        tags: ['Municipalities'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Ma municipalité'),
            new OA\Response(response: 404, description: 'Municipalité non trouvée'),
        ]
    )]
    public function GetMyMunicipality(Request $request): JsonResponse
{
    $municipality = $request->user()
        ->municipality
        ->load('city.governorate.country');
 
    return response()->json(['data' => $municipality]);
}

    /**
 * Create a new municipality.
 * Endpoint: POST /admin/municipalities | Allowed users: super admin
 * @param StoreMunicipalityRequest $request
 * @return JsonResponse
 */
    #[OA\Post(
        path: '/admin/municipalities',
        summary: 'Créer une municipalité',
        tags: ['Municipalities'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'governorate', 'address', 'city'],
                properties: [
                    new OA\Property(property: 'name',        type: 'string', example: 'Municipalité de Monastir'),
                    new OA\Property(property: 'governorate', type: 'string', example: 'Monastir'),
                    new OA\Property(property: 'phone',       type: 'string', example: '522123456',   nullable: true),
                    new OA\Property(property: 'address',     type: 'string', example: 'Rue principale', nullable: true),
                    new OA\Property(property: 'email',       type: 'string', example: 'monastir@example.com', nullable: true),
                    new OA\Property(property: 'city',        type: 'string', example: 'Monastir',    nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Municipalité créée'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function store(StoreMunicipalityRequest $request): JsonResponse
    {
        try {
            // Log::info('Entered  to create municipality') ;
            // \Log::info('Attempting to create municipality', ['request_data' => $request->validated()]);
            $municipality = $this->municipalityService->create($request->validated());
             
            return response()->json([
                'message' => 'Municipality created successfully',
                'data'    => $municipality,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating municipality', [
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
 * Update a municipality.
 * Endpoint: PUT /admin/municipalities/{municipality} | Allowed users: super admin
 * @param UpdateMunicipalityRequest $request
 * @param Municipality $municipality
 * @return JsonResponse
 */
    #[OA\Put(
        path: '/admin/municipalities/{municipality}',
        summary: 'Mettre à jour une municipalité',
        tags: ['Municipalities'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'municipality', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent()),
        responses: [
            new OA\Response(response: 200, description: 'Municipalité mise à jour'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function update(UpdateMunicipalityRequest $request, Municipality $municipality): JsonResponse
    {
        try {
            $updated = $this->municipalityService->update($municipality, $request->validated());

            return response()->json([
                'message' => 'Municipality updated successfully',
                'data'    => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
 * Update the authenticated admin's own municipality.
 * Endpoint: PUT /admin_manager/municipalities/my-municipality | Allowed users: admin municipal
 * @param UpdateMunicipalityRequest $request
 * @return JsonResponse
 */
    #[OA\Put(
        path: '/admin_manager/municipalities/my-municipality',
        summary: 'Mettre à jour ma municipalité (Admin Manager)',
        tags: ['Municipalities'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent()),
        responses: [
            new OA\Response(response: 200, description: 'Municipalité mise à jour'),
            new OA\Response(response: 403, description: 'Admin municipal non trouvé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function updateByMunicipalAdmin(UpdateMunicipalityRequest $request): JsonResponse
    {
        try {
            $updated = $this->municipalityService->updateByMunicipalAdmin(
                auth('api')->id(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Municipality updated successfully',
                'data'    => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
 * Delete a municipality.
 * Endpoint: DELETE /admin/municipalities/{municipality} | Allowed users: super admin
 * @param Municipality $municipality
 * @return JsonResponse
 */
    #[OA\Delete(
        path: '/admin/municipalities/{municipality}',
        summary: 'Supprimer une municipalité',
        tags: ['Municipalities'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'municipality', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Municipalité supprimée'),
            new OA\Response(response: 404, description: 'Municipalité non trouvée'),
        ]
    )]
    public function destroy(Municipality $municipality): JsonResponse
    {
        try {
            $this->municipalityService->delete($municipality);

            return response()->json(['message' => 'Municipality deleted']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}