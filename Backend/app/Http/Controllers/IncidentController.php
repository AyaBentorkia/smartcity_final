<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Log;
use App\Models\Incident;
use Illuminate\Support\Facades\DB;


#[OA\Tag(name: 'Incidents', description: 'Gestion des incidents signalés par les citoyens')]
class IncidentController extends Controller
{
    public function __construct(
        private IncidentService $incidentService
    ) {}

    
/**
 * List all incidents (paginated).
 *
 * Endpoint: GET /incidents
 * Allowed users: public / authenticated
 * @param Request $request
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/incidents',
        summary: 'Lister tous les incidents',
        tags: ['Incidents'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste de tous les incidents'),
        ]
    )]
    public function index(Request $request): JsonResponse
{
    $result = $this->incidentService->getAllPaginated(
        perPage:        $request->integer('per_page', 10),
        page:           $request->integer('page', 1),
        municipalityId: $request->integer('municipality_id') ?: null,
        search:         $request->string('search')->toString() ?: null,
    );
 
    return response()->json($result);
}
    // public function index(): JsonResponse
    // {
    //     $incidents = $this->incidentService->getAll();

    //     return response()->json(['data' => $incidents]);
    // }

    /**
 * Show a single incident.
 *
 * Endpoint: GET /incidents/{id}
 * Allowed users: authenticated
 * @param int $id
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/incidents/{id}',
        summary: 'Voir un incident spécifique',
        tags: ['Incidents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200,  description: "Détails de l'incident"),
            new OA\Response(response: 404, description: 'Incident non trouvé'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $incident = $this->incidentService->findById($id);

            return response()->json(['data' => $incident]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
/**
 * Get incident counts grouped by municipality (super admin dashboard).
 *
 * Endpoint: GET /admin/incidents/by-municipality
 * Allowed users: super admin
 * @return JsonResponse
 */
public function getByMunicipality(): JsonResponse
{
     try {
$data = DB::table('incidents_per_municipality')->get();
Log::info('Incidents per Municipality', ['data' => $data]);
return response()->json(['data' => $data]);
} catch (\Exception $e) {
    // Log::error('Error fetching incidents per municipality', [
    //     'message' => $e.message,
    //     'file'    => $e.file,
    //     'line'    => $e.line,
    //     'trace'   => $e.trace,
    // ]);
    return response()->json(['error' => $e->getMessage()], 500);
  }

}
    /**
     * Créer un incident (citoyen) — sans détection de zone
     */
    #[OA\Post(
        path: '/citizen/incidents/{category_id}',
        summary: 'Créer un incident (Citizen)',
        tags: ['Incidents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'category_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: 'ID de la catégorie'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'description', 'latitude', 'longitude'],
                properties: [
                    new OA\Property(property: 'title',        type: 'string', example: 'Nid de poule'),
                    new OA\Property(property: 'description',  type: 'string', example: 'Grand nid de poule dangereux'),
                    new OA\Property(property: 'latitude',     type: 'number', format: 'float', example: 33.5731),
                    new OA\Property(property: 'longitude',    type: 'number', format: 'float', example: -7.5898),
                    new OA\Property(property: 'address_text', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Incident créé avec succès'),
            new OA\Response(response: 422, description: 'Coordonnées GPS requises'),
        ]
    )]
    public function store(StoreIncidentRequest $request, int $category_id): JsonResponse
    {
        try {
            $incident = $this->incidentService->create(
                data:       $request->validated(),
            citizenId:  auth('api')->id(),
            categoryId: $category_id,
            image:      $request->file('image'),
            
            );

            return response()->json([
                'message' => 'Incident created successfully',
                'data'    => $incident,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating incident', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
 * Create a new incident with automatic GPS zone detection.
 *
 * Endpoint: POST /citizen/incidents/{category_id}/zone
 * Allowed users: citizen
 * @param StoreIncidentRequest $request
 * @param int $category_id
 * @return JsonResponse
 */
    #[OA\Post(
        path: '/citizen/incidents/{category_id}/zone',
        summary: 'Créer un incident avec détection de zone automatique (Citizen)',
        tags: ['Incidents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'category_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'description', 'latitude', 'longitude'],
                properties: [
                    new OA\Property(property: 'title',       type: 'string', example: 'Nid de poule'),
                    new OA\Property(property: 'description', type: 'string', example: 'Grand nid de poule dangereux'),
                    new OA\Property(property: 'latitude',    type: 'number', format: 'float', example: 33.5731),
                    new OA\Property(property: 'longitude',   type: 'number', format: 'float', example: -7.5898),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Incident créé avec zone détectée'),
            new OA\Response(response: 422, description: 'Coordonnées GPS invalides ou zone introuvable'),
        ]
    )]
    public function storeWithZone(StoreIncidentRequest $request, int $category_id): JsonResponse
    {
        try {
            \Log::info('Received request to create incident with zone');
                                \Log::info('GPS Coordinates', ['long' => $request->validated()]);

            $incident = $this->incidentService->createWithZone(
    $request->validated(),
    auth('api')->id(),
    $category_id,
    $request->file('image')
            );

            return response()->json([
                'message' => 'Incident created successfully',
                'data'    => $incident,
            ], 201);
        } catch (\Exception $e) {
                Log::error('Error creating incident with zone', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => $e->getTraceAsString(),
                ]);
            return response()->json(['message' => $e->getMessage()],(int) $e->getCode() ?: 500);
        }
    }

    /**
 * Update an incident (citizen owner).
 *
 * Endpoint: PUT /citizen/incidents/{id}
 * Allowed users: citizen
 * @param UpdateIncidentRequest $request
 * @param int $id
 * @return JsonResponse
 */
    #[OA\Put(
        path: '/citizen/incidents/{id}',
        summary: 'Mettre à jour un incident (Citizen)',
        tags: ['Incidents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent()),
        responses: [
            new OA\Response(response: 200, description: 'Incident mis à jour'),
            new OA\Response(response: 400, description: 'Statut invalide pour modification'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 404, description: 'Incident non trouvé'),
        ]
    )]
    public function update(UpdateIncidentRequest $request, int $id): JsonResponse
    {
        try {
            $incident = $this->incidentService->updateByCitizen(
                $id,
                auth('api')->id(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Incident updated',
                'data'    => $incident,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
 * Delete an incident.
 *
 * Endpoint: DELETE /citizen/incidents/{id}
 * Allowed users: citizen / admin
 * @param int $id
 * @return JsonResponse
 */
    #[OA\Delete(
        path: '/incidents/{id}',
        summary: 'Supprimer un incident',
        tags: ['Incidents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Incident supprimé avec succès'),
            new OA\Response(response: 404, description: 'Incident non trouvé'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->incidentService->delete($id);

            return response()->json(['message' => 'Incident deleted']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
 * Get incidents near the connected admin's municipality (paginated).
 *
 * Endpoint: GET /admin_manager/incidents/nearby
 * Allowed users: admin municipal
 * @param Request $request
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/admin_manager/incidents/nearby',
        summary: 'Lister les incidents proches de ma municipalité',
        tags: ['Incidents'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste des incidents proches'),
        ]
    )]
     public function getNearby(Request $request): JsonResponse
    {
        try {
            $result = $this->incidentService->getNearbyForAdminPaginated(
                user:  auth('api')->user(),
                perPage: $request->integer('per_page', 10),
                page:    $request->integer('page', 1),
            );
 
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Erreur dans getNearby', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
 
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
    // public function getNearby(): JsonResponse
    // {
    //     try {
    //     //  Log::info('in incident nearby controller');

    //         $incidents = $this->incidentService->getNearbyForAdmin(auth('api')->id());

    //         return response()->json([
    //             'message' => 'Nearby incidents',
    //             'data'    => $incidents,
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Erreur dans getNearby', [
    //         'message' => $e->getMessage(),
    //         'file'    => $e->getFile(),
    //         'line'    => $e->getLine(),
    //         'trace'   => $e->getTraceAsString(),
    //     ]);

    //         return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
    //     }
    // }

    /**
 * Get incidents filtered by category type.
 *
 * Endpoint: GET /incidents/type/{type}
 * Allowed users: authenticated
 * @param string $type
 * @return JsonResponse
 */
    public function getByType(string $type): JsonResponse
    {
        try {
            $incidents = $this->incidentService->getByType($type);

            return response()->json(['data' => $incidents]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
 * Get incidents filtered by status.
 *
 * Endpoint: GET /incidents/status/{status}
 * Allowed users: authenticated
 * @param string $status
 * @return JsonResponse
 */
    public function getByStatus(string $status): JsonResponse
    {
        $incidents = $this->incidentService->getByStatus($status);

        return response()->json(['data' => $incidents]);
    }

    /**
     * Get incidents by zone.
     *
     * Endpoint: GET /incidents/zone/{zoneId}
     * Allowed users: authenticated
     * @param int $zoneId
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/incidents/zone/{zoneId}',
        summary: 'Récupérer les incidents par zone',
        tags: ['Incidents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'zoneId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des incidents de la zone'),
            new OA\Response(response: 404, description: 'Zone non trouvée'),
        ]
    )]
    public function getByZone(int $zoneId): JsonResponse
    {
        try {
            $incidents = $this->incidentService->getByZone($zoneId);

            return response()->json(['data' => $incidents]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
 * Get all incidents reported by a specific citizen (admin view).
 *
 * Endpoint: GET /incidents/citizen/{citizenId}
 * Allowed users: admin
 * @param int $citizenId
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/incidents/citizen/{citizenId}',
        summary: "Récupérer les incidents d'un citoyen",
        tags: ['Incidents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'citizenId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des incidents du citoyen'),
        ]
    )]
    public function getByCitizen(int $citizenId): JsonResponse
    {
        $incidents = $this->incidentService->getByCitizen($citizenId);

        return response()->json(['data' => $incidents]);
    }

    /**
 * Get the authenticated citizen's own incidents.
 *
 * Endpoint: GET /citizen/my-incidents
 * Allowed users: citizen
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/citizen/my-incidents',
        summary: 'Mes incidents (Citizen)',
        tags: ['Incidents'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste de mes incidents'),
        ]
    )]
    public function getMyIncidents(): JsonResponse
    {
        try {
        $incidents = $this->incidentService->getMyIncidents(auth('api')->id());

        return response()->json(['data' => $incidents]);
        }
           catch (\Exception $e) {
             Log::error('Erreur dans update status', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);


            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
 * Update the status of an incident.
 *
 * Endpoint: PATCH /admin_manager/incidents/{id}/status
 * Allowed users: admin municipal
 * @param Request $request
 * @param int $id
 * @return JsonResponse
 */
    #[OA\Patch(
        path: '/admin_manager/incidents/{id}/status',
        summary: "Mettre à jour le statut d'un incident",
        tags: ['Incidents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['signalé', 'en cours', 'résolu']),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Statut mis à jour'),
            new OA\Response(response: 404, description: 'Incident non trouvé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            Log::info("in update status controller",["status"=>$request['status']]);

            $incident = $this->incidentService->updateStatus($id, $request['status']);

            return response()->json([
                'message' => 'Status updated',
                'data'    => $incident,
            ]);
        } catch (\Exception $e) {
             Log::error('Erreur dans update status', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);


            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}