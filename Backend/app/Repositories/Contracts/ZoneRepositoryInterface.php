<?php

namespace App\Repositories\Contracts;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Collection;

interface ZoneRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?Zone;

    public function findByName(string $name): ?Zone;

    public function getByMunicipalityId(int $municipalityId): Collection;

    public function create(array $data): Zone;

    public function update(Zone $zone, array $data): Zone;

    public function delete(Zone $zone): void;
}