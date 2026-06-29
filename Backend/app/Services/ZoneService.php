<?php

namespace App\Services;

use App\Models\Municipality;
use App\Models\Zone;
use App\Models\User;
use App\Repositories\Contracts\ZoneRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ZoneService
{
    public function __construct(
        private ZoneRepositoryInterface $zoneRepository,
        private UserRepositoryInterface $userRepository
    ) {}
/**
 * Retrieve all zones.
 *
 * Allowed users: authenticated
 * @return Collection
 */
    public function getAll(): Collection
    {
        return Cache::remember('zones.all', now()->addHours(6), function () {
            return $this->zoneRepository->getAll();
        });
    }
/**
 * Retrieve zones belonging to the authenticated admin's municipality.
 *
 * Allowed users: admin municipal
 * @param User $user
 * @return Collection
 */
    public function getZonesNearby(User $user): Collection
    {
        $key = "zones.municipality.{$user->municipality_id}";
        return Cache::remember($key, now()->addHours(6), function () use ($user) {
            return $this->zoneRepository->getByMunicipalityId($user->municipality_id);
        });
    }
/**
 * Create a new zone linked to the authenticated admin's municipality.
 *
 * Allowed users: admin municipal
 * @param array $data
 * @param User $authenticatedUser
 * @return Zone
 * @throws \Exception if a zone with the same name already exists or the user has no municipality
 */
    public function create(array $data, User $authenticatedUser): Zone
    {
        if ($this->zoneRepository->findByName($data['name'])) {
            throw new \Exception('Zone already exists', 409);
        }

        if (!$authenticatedUser->municipality_id) {
            throw new \Exception('The specified municipality was not found.', 404);
        }

        $data['municipality_id'] = $authenticatedUser->municipality_id;
        $zone = $this->zoneRepository->create($data);

        Cache::forget('zones.all');
        Cache::forget("zones.municipality.{$authenticatedUser->municipality_id}");

        Log::info('Zone created: ' . $zone->id);
        return $zone;
    }
/**
 * Update an existing zone.
 *
 * Allowed users: super admin
 * @param int $id
 * @param array $data
 * @return Zone
 * @throws \Exception if the zone or the provided municipality is not found
 */
    public function update(int $id, array $data): Zone
    {
        $zone = $this->zoneRepository->findById($id);

        if (!$zone) {
            throw new \Exception('Zone non trouvée', 404);
        }

        if (!empty($data['municipality'])) {
            $municipality = Municipality::find($data['municipality']);
            if (!$municipality) {
                throw new \Exception('Municipalité non trouvée', 404);
            }
            $data['municipality_id'] = $municipality->id;
        }

        $updated = $this->zoneRepository->update($zone, $data);

        Cache::forget('zones.all');
        Log::info('Zone updated: ' . $zone->id);
        return $updated;
    }

    /**
 * Delete a zone.
 *
 * Allowed users: super admin
 * @param Zone $zone
 * @return void
 */
    public function delete(Zone $zone): void
    {
        $this->zoneRepository->delete($zone);
        Cache::forget('zones.all');
        Log::info('Zone deleted: ' . $zone->id);
    }
}