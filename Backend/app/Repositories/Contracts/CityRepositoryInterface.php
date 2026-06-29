<?php
namespace App\Repositories\Contracts;

use App\Models\City;
use Illuminate\Database\Eloquent\Collection;

interface CityRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(int $id): ?City;
    public function findByName(string $name): ?City;
    public function findByGovernorate(int $governorateId): Collection;
    // findByGouvernorate(string) supprimé → findByGovernorate(int $id)
    public function create(array $data): City;
    public function update(City $city, array $data): City;
    public function delete(City $city): void;
}