<?php
// app/Http/Controllers/IncidentPredictionController.php

namespace App\Http\Controllers;

use App\Services\IncidentPredictionService;
use App\Http\Resources\IncidentPredictionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncidentPredictionController extends Controller
{
    public function __construct(
        private IncidentPredictionService $predictionService,
    ) {}

    /**
 * Manually trigger a prediction for a zone and category.
 * Endpoint: POST /admin_manager/zones/{zone_id}/predict | Allowed users: admin municipal
 * @param Request $request
 * @param int $zone_id
 * @return JsonResponse
 */
    public function predict(Request $request, int $zone_id): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'period'      => 'required|date_format:Y-m-d',
        ]);

        try {
            $prediction = $this->predictionService->predict(
                zoneId:      $zone_id,
                categoryId:  $request->category_id,
                period:      $request->period,
                triggeredBy: $request->user()->id,
            );

            return response()->json([
                'message' => 'Prédiction effectuée avec succès',
                'data'    => new IncidentPredictionResource($prediction),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Incident prediction failed', [
                'zone_id' => $zone_id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erreur lors de la prédiction : ' . $e->getMessage(),
            ], 500);
        }
    }

   /**
 * List prediction history for a zone.
 * Endpoint: GET /admin_manager/zones/{zone_id}/predictions | Allowed users: admin municipal
 * @param int $zone_id
 * @return JsonResponse
 */
    public function index(int $zone_id): JsonResponse
    {
        try {
            $predictions = $this->predictionService->getByZone($zone_id);

            return response()->json([
                'data' => IncidentPredictionResource::collection($predictions),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
 * Get the latest prediction for a zone.
 * Endpoint: GET /admin_manager/zones/{zone_id}/predictions/latest | Allowed users: admin municipal
 * @param int $zone_id
 * @return JsonResponse
 */
    public function latest(int $zone_id): JsonResponse
    {
        try {
            $prediction = $this->predictionService->getLatestByZone($zone_id);

            if (!$prediction) {
                return response()->json([
                    'message' => 'Aucune prédiction disponible pour cette zone',
                ], 404);
            }

            return response()->json([
                'data' => new IncidentPredictionResource($prediction),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
 * Get predictions for a zone filtered by category.
 * Endpoint: GET /admin_manager/zones/{zone_id}/predictions?category=... | Allowed users: admin municipal
 * @param Request $request
 * @param int $zone_id
 * @return JsonResponse
 */
    public function byCategory(Request $request, int $zone_id): JsonResponse
    {
        try {
            $predictions = $this->predictionService->getByZoneAndCategory(
                $zone_id,
                $request->query('category')
            );

            return response()->json([
                'data' => IncidentPredictionResource::collection($predictions),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }
}