<?php
namespace App\Services;

use App\Models\City;
use App\Repositories\Contracts\CityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * City business logic service.
 *
 * Manages city retrieval, caching, validation, and persistence.
 */
class CityService
{
    public function __construct(
        private CityRepositoryInterface $cityRepository
    ) {}

    /**
     * Get all cities with governorate and country relationships.
     *
     * Allowed users: public
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Cache::remember('cities.all', now()->addHours(24), function () {
            return $this->cityRepository->getAll();
        });
    }

    /**
     * Get cities by governorate.
     *
     * Allowed users: public
     * @param int $governorateId
     * @return Collection
     */
    public function getByGovernorate(int $governorateId): Collection
    {
        return Cache::remember("cities.governorate.{$governorateId}", now()->addHours(24), function () use ($governorateId) {
            return $this->cityRepository->findByGovernorate($governorateId);
        });
    }

    /**
     * Find a city by ID, throwing if not found.
     *
     * Allowed users: public
     * @param int $id
     * @return City
     * @throws \Exception
     */
    public function findById(int $id): City
    {
        return Cache::remember("cities.{$id}", now()->addHours(24), function () use ($id) {
            $city = $this->cityRepository->findById($id);
            if (!$city) throw new \Exception('Ville non trouvée', 404);
            return $city;
        });
    }

    /**
     * Create a new city after checking uniqueness.
     *
     * Allowed users: admin
     * @param array $data
     * @return City
     * @throws \Exception
     */
    public function create(array $data): City
    {
        if ($this->cityRepository->findByName($data['name']))
            throw new \Exception('Une ville avec ce nom existe déjà', 422);

        $city = $this->cityRepository->create($data);
        Cache::forget('cities.all');
        Cache::forget("cities.governorate.{$data['governorate_id']}");
        return $city;
    }

    /**
     * Update a city and clear its cache.
     *
     * Allowed users: admin
     * @param City $city
     * @param array $data
     * @return City
     */
    public function update(City $city, array $data): City
    {
        $updated = $this->cityRepository->update($city, $data);
        Cache::forget('cities.all');
        Cache::forget("cities.{$city->id}");
        Cache::forget("cities.governorate.{$city->governorate_id}");
        return $updated;
    }

    /**
     * Delete a city and remove stale cache.
     *
     * Allowed users: admin
     * @param City $city
     * @return void
     */
    public function delete(City $city): void
    {
        $this->cityRepository->delete($city);
        Cache::forget('cities.all');
        Cache::forget("cities.{$city->id}");
        Cache::forget("cities.governorate.{$city->governorate_id}");
    }
}