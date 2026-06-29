<?php
namespace App\Repositories\Contracts;

use App\Models\Country;
use Illuminate\Database\Eloquent\Collection;

interface CountryRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(int $id): ?Country;
    public function findByCode(string $code): ?Country;
    public function create(array $data): Country;
    public function update(Country $country, array $data): Country;
    public function delete(Country $country): void;
}