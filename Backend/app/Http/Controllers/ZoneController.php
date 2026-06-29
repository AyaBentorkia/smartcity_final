<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Models\Zone;
use App\Services\ZoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Zones', description: 'Gestion des zones par les super admins')]
class ZoneController extends Controller
{
    public function __construct(
        private ZoneService $zoneService
    ) {}

    /**
     * Lister toutes les zones
     */
    #[OA\Get(
        path: '/zones',
        summary: 'Lister tous les zones',
        tags: ['Zones'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste de tous les zones'),
        ]
    )]
    public function index(): JsonResponse
    {
        $zones = $this->zoneService->getAll();

        return response()->json(['data' => $zones]);
    }

    /**
     * Lister les zones de la municipalité de l'admin connecté
     */
    #[OA\Get(
        path: '/admin_manager/zones/nearby',
        summary: 'Lister les zones de ma municipalité',
        tags: ['Zones'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste des zones de ma municipalité'),
            new OA\Response(response: 404, description: 'Admin municipal non trouvé'),
        ]
    )]
    // ZoneController.php
public function zonesNearby(): JsonResponse
{
    try {
        $zones = $this->zoneService->getZonesNearby(auth('api')->user()); // ← user complet
        return response()->json(['data' => $zones]);
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

    /**
     * Afficher une zone spécifique
     */
    #[OA\Get(
        path: '/zones/{zone}',
        summary: 'Voir une zone spécifique',
        tags: ['Zones'],
        parameters: [
            new OA\Parameter(name: 'zone', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Détails de la zone'),
            new OA\Response(response: 404, description: 'Zone non trouvée'),
        ]
    )]
    public function show(Zone $zone): JsonResponse
    {
        // Note: ton code original injectait Municipality $zone par erreur — corrigé en Zone $zone
        return response()->json([
            'message' => 'Zone retrieved successfully',
            'data'    => $zone,
        ]);
    }

    /**
     * Créer une zone (super admin)
     */
    #[OA\Post(
        path: '/admin_manager/zones',
        summary: 'Créer une zone (super admin)',
        tags: ['Zones'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'latitude_center', 'longitude_center', 'rayon_km', 'city'],
                properties: [
                    new OA\Property(property: 'name',              type: 'string',  example: 'Zone Nord'),
                    new OA\Property(property: 'latitude_center',   type: 'number',  format: 'float', example: 35.7725),
                    new OA\Property(property: 'longitude_center',  type: 'number',  format: 'float', example: 10.7550),
                    new OA\Property(property: 'rayon_km',          type: 'number',  format: 'float', example: 2.5),
                    // new OA\Property(property: 'city',              type: 'string',  example: 'Sousse'),
                    new OA\Property(property: 'description',       type: 'string',  nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Zone créée avec succès'),
            new OA\Response(response: 404, description: 'Municipalité non trouvée'),
            new OA\Response(response: 409, description: 'Zone déjà existante'),
        ]
    )]
    public function store(StoreZoneRequest $request): JsonResponse
    {
        try {

            $zone = $this->zoneService->create($request->validated(),auth('api')->user());

            return response()->json([
                'message' => 'Zone created successfully',
                'data'    => $zone,
            ], 201);
        } catch (\Exception $e) {
 Log::error('Erreur dans create zone', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        }
    }

    /**
     * Mettre à jour une zone (super admin)
     */
    #[OA\Put(
        path: '/admin/zones/{id}',
        summary: 'Mettre à jour une zone (Super admin)',
        tags: ['Zones'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent()),
        responses: [
            new OA\Response(response: 200, description: 'Zone mise à jour'),
            new OA\Response(response: 404, description: 'Zone non trouvée'),
        ]
    )]
    public function update(UpdateZoneRequest $request, int $id): JsonResponse
    {
        try {
            $zone = $this->zoneService->update($id, $request->validated());

            return response()->json([
                'message' => 'Zone updated successfully',
                'data'    => $zone,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Supprimer une zone (super admin)
     */
    #[OA\Delete(
        path: '/admin/zones/{zone}',
        summary: 'Supprimer une zone',
        tags: ['Zones'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'zone', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Zone supprimée'),
            new OA\Response(response: 404, description: 'Zone non trouvée'),
        ]
    )]
    public function destroy(Zone $zone): JsonResponse
    {
        try {
            $this->zoneService->delete($zone);

            return response()->json(['message' => 'Zone deleted']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}