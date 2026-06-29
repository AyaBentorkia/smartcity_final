<?php
namespace App\Repositories;

use App\Models\Governorate;
use App\Repositories\Contracts\GovernorateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GovernorateRepository implements GovernorateRepositoryInterface
{
    /**
 * Retrieve all governorates with their country.
 *
 * @return Collection
 */
    public function getAll(): Collection
    {
        return Governorate::with('country:id,name,code')->get();
    }
/**
 * Find a governorate by its ID with its country.
 *
 * @param int $id
 * @return Governorate|null
 */
    public function findById(int $id): ?Governorate
    {
        return Governorate::with('country:id,name,code')->find($id);
    }
/**
 * Retrieve all governorates belonging to a given country.
 *
 * @param int $countryId
 * @return Collection
 */
    public function findByCountry(int $countryId): Collection
    {
        return Governorate::where('country_id', $countryId)->get();
    }
/**
 * Create a new governorate record.
 *
 * @param array $data
 * @return Governorate
 */
    public function create(array $data): Governorate
    {
        return Governorate::create($data);
    }
/**
 * Update an existing governorate record.
 *
 * @param Governorate $governorate
 * @param array $data
 * @return Governorate
 */
    public function update(Governorate $governorate, array $data): Governorate
    {
        $governorate->update($data);
        return $governorate;
    }
/**
 * Delete a governorate from the database.
 *
 * @param Governorate $governorate
 * @return void
 */
    public function delete(Governorate $governorate): void
    {
        $governorate->delete();
    }
}