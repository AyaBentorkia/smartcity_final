<?php
namespace App\Repositories;

use App\Models\Country;
use App\Repositories\Contracts\CountryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CountryRepository implements CountryRepositoryInterface
{
    /**
 * Retrieve all countries.
 *
 * @return Collection
 */
    public function getAll(): Collection
    {
        return Country::all();
    }
/**
 * Find a country by its ID.
 *
 * @param int $id
 * @return Country|null
 */
    public function findById(int $id): ?Country
    {
        return Country::find($id);
    }
/**
 * Find a country by its ISO code.
 *
 * @param string $code
 * @return Country|null
 */
    public function findByCode(string $code): ?Country
    {
        return Country::where('code', $code)->first();
    }
/**
 * Create a new country record.
 *
 * @param array $data
 * @return Country
 */
    public function create(array $data): Country
    {
        return Country::create($data);
    }
/**
 * Update an existing country record.
 *
 * @param Country $country
 * @param array $data
 * @return Country
 */
    public function update(Country $country, array $data): Country
    {
        $country->update($data);
        return $country;
    }
/**
 * Delete a country from the database.
 *
 * @param Country $country
 * @return void
 */
    public function delete(Country $country): void
    {
        $country->delete();
    }
}