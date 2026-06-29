<?php
// app/Repositories/IncidentPredictionRepository.php

namespace App\Repositories;

use App\Models\IncidentPrediction;
use App\Repositories\Contracts\IncidentPredictionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class IncidentPredictionRepository implements IncidentPredictionRepositoryInterface
{
    /**
 * Create a new incident prediction record.
 *
 * @param array $data
 * @return IncidentPrediction
 */
    public function create(array $data): IncidentPrediction
    {
        return IncidentPrediction::create($data);
    }
/**
 * Find a prediction by its ID with zone and triggering user relations.
 *
 * @param int $id
 * @return IncidentPrediction|null
 */
    public function findById(int $id): ?IncidentPrediction
    {
        return IncidentPrediction::with(['zone', 'triggeredBy'])->find($id);
    }
/**
 * Find a prediction by its ID or throw a ModelNotFoundException.
 *
 * @param int $id
 * @return IncidentPrediction
 */
    public function findByIdOrFail(int $id): IncidentPrediction
    {
        return IncidentPrediction::with(['zone', 'triggeredBy'])->findOrFail($id);
    }
/**
 * Retrieve all predictions for a zone, ordered by most recent.
 *
 * @param int $zoneId
 * @return Collection
 */
    public function getByZoneId(int $zoneId): Collection
    {
        return IncidentPrediction::with('triggeredBy')
            ->where('zone_id', $zoneId)
            ->latest()
            ->get();
    }
/**
 * Retrieve the latest prediction for a zone.
 *
 * @param int $zoneId
 * @return IncidentPrediction|null
 */
    public function getLatestByZoneId(int $zoneId): ?IncidentPrediction
    {
        return IncidentPrediction::where('zone_id', $zoneId)
            ->latest()
            ->first();
    }
/**
 * Retrieve predictions for a zone filtered by category name, ordered by most recent.
 *
 * @param int $zoneId
 * @param string $category
 * @return Collection
 */
    public function getByZoneAndCategory(int $zoneId, string $category): Collection
    {
        return IncidentPrediction::with('triggeredBy')
            ->where('zone_id', $zoneId)
            ->where('category', $category)
            ->latest()
            ->get();
    }
}