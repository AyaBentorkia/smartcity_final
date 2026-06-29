<?php

namespace App\Http\Controllers;

use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Statistics', description: 'Statistiques du dashboard par rôle')]
class StatisticsController extends Controller
{
    public function __construct(
        private StatisticsService $statisticsService
    ) {}

    /**
 * Get global statistics.
 * Endpoint: GET /admin/statistics | Allowed users: super admin
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/admin/statistics',
        summary: 'Statistiques globales (SuperAdmin)',
        tags: ['Statistics'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Stats globales')]
    )]
    public function globalStats(): JsonResponse
    {
        try {
            $stats = $this->statisticsService->getGlobalStats();
            return response()->json(['message' => 'Global statistics', 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
 * Get municipal statistics.
 * Endpoint: GET /admin_manager/statistics | Allowed users: admin municipal
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/admin_manager/statistics',
        summary: 'Statistiques de ma municipalité (Admin Municipal)',
        tags: ['Statistics'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Stats municipalité')]
    )]
    public function municipalStats(): JsonResponse
    {
        try {
            $stats = $this->statisticsService->getMunicipalStats(auth('api')->user());
            return response()->json(['message' => 'Municipal statistics', 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
 * Get agent statistics.
 * Endpoint: GET /agent/statistics | Allowed users: agent
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/agent/statistics',
        summary: 'Mes statistiques (Agent)',
        tags: ['Statistics'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: "Stats de l'agent")]
    )]
    public function agentStats(): JsonResponse
    {
        try {
            $stats = $this->statisticsService->getAgentStats(auth('api')->user());
            return response()->json(['message' => 'Agent statistics', 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}