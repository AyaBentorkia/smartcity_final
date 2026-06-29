<?php
namespace App\Services;

use App\Models\Municipality;
use App\Models\User;
use App\Repositories\Contracts\MunicipalityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MunicipalityService
{
    public function __construct(
        private MunicipalityRepositoryInterface $municipalityRepository
    ) {}
/**
 * Retrieve all municipalities.
 *
 * Allowed users: public
 * @return Collection
 */
    public function getAll(): Collection
    {
        return Cache::remember('municipalities.all', now()->addHours(24), function () {
            return $this->municipalityRepository->getAll();
        });
    }
/**
 * Find a municipality by its ID.
 *
 * Allowed users: public
 * @param int $id
 * @return Municipality
 * @throws \Exception if the municipality is not found
 */
    public function findById(int $id): Municipality
    {
        return Cache::remember("municipalities.{$id}", now()->addHours(6), function () use ($id) {
            $municipality = $this->municipalityRepository->findById($id);
            if (!$municipality) throw new \Exception('Municipalité non trouvée', 404);
            return $municipality;
        });
    }
/**
 * Retrieve all municipalities (paginated).
 *
 * Allowed users: super admin
 * @param int $perPage
 * @param int $page
 * @return array
 */
    public function getAllPaginated(int $perPage = 10, int $page = 1): array
    {
        return $this->municipalityRepository->getAllPaginated($perPage, $page);
    }
/**
 * Get the municipality of the authenticated user (with full geographic relations).
 *
 * Allowed users: admin municipal / agent
 * @param User $user
 * @return Municipality
 * @throws \Exception if the user has no associated municipality
 */
    public function getMyMunicipality(User $user): Municipality
    {
        if (!$user?->municipality_id)
            throw new \Exception('Aucune municipalité trouvée', 404);

        return $user->load('municipality.city.governorate.country')->municipality;
    }

/**
 * Create a new municipality.
 *
 * Allowed users: super admin
 * @param array $data
 * @return Municipality
 */
    public function create(array $data): Municipality
    {
        // 'governorate' et 'country' ne sont plus dans $data
        if (isset($data['city'])) {
            $data['city_id'] = $data['city'];
            unset($data['city']);
        }

        $municipality = $this->municipalityRepository->create($data);
        Cache::forget('municipalities.all');
        Log::info('Municipality created: ' . $municipality->id);
        return $municipality->load('city.governorate.country');
    }
/**
 * Update an existing municipality.
 *
 * Allowed users: super admin
 * @param Municipality $municipality
 * @param array $data
 * @return Municipality
 */
    public function update(Municipality $municipality, array $data): Municipality
    {
        if (isset($data['city'])) {
            $data['city_id'] = $data['city'];
            unset($data['city']);
        }

        $updated = $this->municipalityRepository->update($municipality, $data);
        Cache::forget('municipalities.all');
        Cache::forget("municipalities.{$municipality->id}");
        return $updated->load('city.governorate.country');
    }
/**
 * Update the municipality managed by the authenticated admin municipal.
 *
 * Allowed users: admin municipal
 * @param int $userId  ID of the authenticated admin
 * @param array $data
 * @return Municipality
 */
    public function updateByMunicipalAdmin(int $userId, array $data): Municipality
    {
        $manager = User::with('municipality')->findOrFail($userId);
        $updated = $this->municipalityRepository->update($manager->municipality, $data);
        Cache::forget('municipalities.all');
        Cache::forget("municipalities.{$manager->municipality->id}");
        return $updated->load('city.governorate.country');
    }
/**
 * Delete a municipality.
 *
 * Allowed users: super admin
 * @param Municipality $municipality
 * @return void
 */
    public function delete(Municipality $municipality): void
    {
        $this->municipalityRepository->delete($municipality);
        Cache::forget('municipalities.all');
        Cache::forget("municipalities.{$municipality->id}");
    }
}