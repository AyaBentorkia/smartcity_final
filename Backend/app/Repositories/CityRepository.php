<?php
namespace App\Repositories;

use App\Models\City;
use App\Repositories\Contracts\CityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Data access repository for City model.
 */
class CityRepository implements CityRepositoryInterface
{
    /**
     * Retrieve all cities with governorate and country context.
     * @return Collection
     */
    public function getAll(): Collection
    {
        return City::with('governorate.country:id,name,code')->get();
    }

    /**
     * Find a city by ID.
     * @param int $id
     * @return City|null
     */
    public function findById(int $id): ?City
    {
        return City::with('governorate.country:id,name,code')->find($id);
    }

    /**
     * Find a city by name.
     * @param string $name
     * @return City|null
     */
    public function findByName(string $name): ?City
    {
        return City::where('name', $name)->first();
    }

    /**
     * Retrieve cities belonging to a governorate.
     * @param int $governorateId
     * @return Collection
     */
    public function findByGovernorate(int $governorateId): Collection
    {
        // string remplacé par FK int
        return City::where('governorate_id', $governorateId)->get();
    }

    /**
     * Create a new city record.
     * @param array $data
     * @return City
     */
    public function create(array $data): City
    {
        return City::create($data);
    }

    /**
     * Update an existing city.
     * @param City $city
     * @param array $data
     * @return City
     */
    public function update(City $city, array $data): City
    {
        $city->update($data);
        return $city;
    }

    /**
     * Delete a city record.
     * @param City $city
     * @return void
     */
    public function delete(City $city): void
    {
        $city->delete();
    }
}