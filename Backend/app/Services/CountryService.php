<?php
namespace App\Services;

use App\Models\Country;
use App\Repositories\Contracts\CountryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CountryService
{
    public function __construct(
        private CountryRepositoryInterface $countryRepository
    ) {}
/**
 * Retrieve all countries.
 *
 * Allowed users: public
 * @return Collection
 */
    public function getAll(): Collection
    {
        return Cache::remember('countries.all', now()->addHours(24), function () {
            return $this->countryRepository->getAll();
        });
    }
    
/**
 * Find a country by its ID.
 *
 * Allowed users: public
 * @param int $id
 * @return Country
 * @throws \Exception if the country is not found
 */

    public function findById(int $id): Country
    {
        return Cache::remember("countries.{$id}", now()->addHours(24), function () use ($id) {
            $country = $this->countryRepository->findById($id);
            if (!$country) throw new \Exception('Pays non trouvé', 404);
            return $country;
        });
    }

/**
 * Create a new country.
 *
 * Allowed users: admin
 * @param array $data
 * @return Country
 * @throws \Exception if a country with the same code already exists
 */
    public function create(array $data): Country
    {
        if ($this->countryRepository->findByCode($data['code']))
            throw new \Exception('Un pays avec ce code existe déjà', 422);

        $country = $this->countryRepository->create($data);
        Cache::forget('countries.all');
        return $country;
    }
/**
 * Update an existing country.
 *
 * Allowed users: admin
 * @param Country $country
 * @param array $data
 * @return Country
 */
    public function update(Country $country, array $data): Country
    {
        $updated = $this->countryRepository->update($country, $data);
        Cache::forget('countries.all');
        Cache::forget("countries.{$country->id}");
        return $updated;
    }
/**
 * Delete a country.
 *
 * Allowed users: admin
 * @param Country $country
 * @return void
 */
    public function delete(Country $country): void
    {
        $this->countryRepository->delete($country);
        Cache::forget('countries.all');
        Cache::forget("countries.{$country->id}");
    }
}