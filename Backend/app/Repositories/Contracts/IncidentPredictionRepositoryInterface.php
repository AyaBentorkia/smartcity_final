<?php
// app/Repositories/Contracts/IncidentPredictionRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Models\IncidentPrediction;
use Illuminate\Database\Eloquent\Collection;

interface IncidentPredictionRepositoryInterface
{
    public function create(array $data): IncidentPrediction;
    public function findById(int $id): ?IncidentPrediction;
    public function findByIdOrFail(int $id): IncidentPrediction;
    public function getByZoneId(int $zoneId): Collection;
    public function getLatestByZoneId(int $zoneId): ?IncidentPrediction;
    public function getByZoneAndCategory(int $zoneId, string $category): Collection;
}