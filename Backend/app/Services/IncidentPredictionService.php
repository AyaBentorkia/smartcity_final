<?php
// app/Services/IncidentPredictionService.php

namespace App\Services;

use App\Models\Zone;
use App\Models\Category;
use App\Models\IncidentPrediction;
use App\Services\AI\AiService;
use App\Repositories\Contracts\IncidentPredictionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class IncidentPredictionService
{
    public function __construct(
        private IncidentPredictionRepositoryInterface $predictionRepository,
        private AiService $aiService,
    ) {}

    /**
 * Trigger an incident risk prediction for a zone, category, and period.
 *
 * Calls the Python FastAPI AI agent and persists the result.
 * Allowed users: admin municipal
 * @param int $zoneId
 * @param int $categoryId
 * @param string $period  Date string in Y-m-d format
 * @param int $triggeredBy  ID of the user who triggered the prediction
 * @return IncidentPrediction
 */
    public function predict(int $zoneId, int $categoryId, string $period, int $triggeredBy): IncidentPrediction
    {
        $zone     = Zone::findOrFail($zoneId);
        $category = Category::findOrFail($categoryId);

        $payload = $this->buildPayload($zone, $category, $period);

        Log::info('Incident prediction triggered', [
            'zone_id'      => $zoneId,
            'category'     => $category->name,
            'period'       => $period,
            'triggered_by' => $triggeredBy,
        ]);

        // Appel agent Python FastAPI
        $result = $this->aiService->predictIncident($payload);

       return $this->predictionRepository->create([
    'zone_id'      => $zone->id,
    'triggered_by' => $triggeredBy,
    'category'     => $result['category'],
    'period'       => $result['period'],
    'semaine'      => $result['semaine'],
    'probabilite'  => $result['probabilite'],
    'risque'       => $result['risque'],
    'meteo'        => $result['meteo'],
    'est_ferie'    => $result['est_ferie'],   // ← était nb_feries
    'explication'  => $result['explication'],
    'analyzed_at'  => now(),
]);
    }

    /**
 * Build the payload sent to the FastAPI AI agent.
 *
 * @param Zone $zone
 * @param Category $category
 * @param string $period
 * @return array
 */
    private function buildPayload(Zone $zone, Category $category, string $period): array
    {
        return [
            'zone'      => strtoupper($zone->name),
            'category'  => $category->name,
            'period'    => $period,               // YYYY-MM-DD
            'latitude'  => $zone->latitude_center,
            'longitude' => $zone->longitude_center,
        ];
    }
/**
 * Retrieve all predictions for a zone.
 *
 * Allowed users: admin municipal
 * @param int $zoneId
 * @return Collection
 */
    public function getByZone(int $zoneId): Collection
    {
        return $this->predictionRepository->getByZoneId($zoneId);
    }
/**
 * Retrieve the latest prediction for a zone.
 *
 * Allowed users: admin municipal
 * @param int $zoneId
 * @return IncidentPrediction|null
 */
    public function getLatestByZone(int $zoneId): ?IncidentPrediction
    {
        return $this->predictionRepository->getLatestByZoneId($zoneId);
    }
/**
 * Retrieve predictions for a zone filtered by category name.
 *
 * Allowed users: admin municipal
 * @param int $zoneId
 * @param string $category
 * @return Collection
 */
    public function getByZoneAndCategory(int $zoneId, string $category): Collection
    {
        return $this->predictionRepository->getByZoneAndCategory($zoneId, $category);
    }
}