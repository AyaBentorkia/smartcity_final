<?php

namespace App\Repositories\Contracts;

use App\Models\Municipality;
use Illuminate\Database\Eloquent\Collection;

interface MunicipalityRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?Municipality;

    public function findByName(string $name): ?Municipality;

    public function create(array $data): Municipality;

    public function update(Municipality $municipality, array $data): Municipality;

    public function delete(Municipality $municipality): void;
}