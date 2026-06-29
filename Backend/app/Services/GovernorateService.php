<?php
namespace App\Services;

use App\Models\Governorate;
use App\Repositories\Contracts\GovernorateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GovernorateService
{
    public function __construct(
        private GovernorateRepositoryInterface $governorateRepository
    ) {}
/**
 * Retrieve all governorates.
 *
 * Allowed users: public
 * @return Collection
 */
    public function getAll(): Collection
    {
        Log::info('Fetching all governorates from service');
        return Cache::remember('governorates.all', now()->addHours(24), function () {
            return $this->governorateRepository->getAll();
        });
    }
    /**
 * Retrieve all governorates belonging to a country.
 *
 * Allowed users: public
 * @param int $countryId
 * @return Collection
 */

    public function getByCountry(int $countryId): Collection
    {
        return Cache::remember("governorates.country.{$countryId}", now()->addHours(24), function () use ($countryId) {
            return $this->governorateRepository->findByCountry($countryId);
        });
    }
/**
 * Find a governorate by its ID.
 *
 * Allowed users: public
 * @param int $id
 * @return Governorate
 * @throws \Exception if the governorate is not found
 */
    public function findById(int $id): Governorate
    {
        return Cache::remember("governorates.{$id}", now()->addHours(24), function () use ($id) {
            $gov = $this->governorateRepository->findById($id);
            if (!$gov) throw new \Exception('Gouvernorat non trouvé', 404);
            return $gov;
        });
    }

/**
 * Create a new governorate.
 *
 * Allowed users: admin
 * @param array $data
 * @return Governorate
 */
    public function create(array $data): Governorate
    {
        $gov = $this->governorateRepository->create($data);
        Cache::forget('governorates.all');
        Cache::forget("governorates.country.{$data['country_id']}");
        return $gov;
    }
/**
 * Update an existing governorate.
 *
 * Allowed users: admin
 * @param Governorate $governorate
 * @param array $data
 * @return Governorate
 */
    public function update(Governorate $governorate, array $data): Governorate
    {
        $updated = $this->governorateRepository->update($governorate, $data);
        Cache::forget('governorates.all');
        Cache::forget("governorates.{$governorate->id}");
        Cache::forget("governorates.country.{$governorate->country_id}");
        return $updated;
    }
/**
 * Delete an existing governorate.
 *
 * Allowed users: admin
 * @param Governorate $governorate
 * @return void
 */
    public function delete(Governorate $governorate): void
    {
        $this->governorateRepository->delete($governorate);
        Cache::forget('governorates.all');
        Cache::forget("governorates.{$governorate->id}");
        Cache::forget("governorates.country.{$governorate->country_id}");
    }
}