<?php
namespace App\Repositories\Contracts;

use App\Models\Governorate;
use Illuminate\Database\Eloquent\Collection;

interface GovernorateRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(int $id): ?Governorate;
    public function findByCountry(int $countryId): Collection;
    public function create(array $data): Governorate;
    public function update(Governorate $governorate, array $data): Governorate;
    public function delete(Governorate $governorate): void;
}