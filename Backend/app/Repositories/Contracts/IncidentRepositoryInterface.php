<?php

namespace App\Repositories\Contracts;

use App\Models\Incident;
use Illuminate\Database\Eloquent\Collection;

interface IncidentRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?Incident;

    public function findByIdOrFail(int $id): Incident;

    public function findByIdAndCitizen(int $id, int $citizenId): ?Incident;

    public function create(array $data): Incident;

    public function update(Incident $incident, array $data): Incident;

    public function delete(Incident $incident): void;

    public function getByCitizenId(int $citizenId): Collection;

    public function getByZoneIds(array $zoneIds): Collection;

    public function getByCategoryId(int $categoryId): Collection;

    public function getByStatus(string $status): Collection;

    public function getByZoneId(int $zoneId): Collection;
    
    public function getByZoneIdsPaginated(array $zoneIds, int $perPage = 10, int $page = 1): array;

}