<?php
namespace App\Repositories;

use App\Models\Municipality;
use App\Repositories\Contracts\MunicipalityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\Paginatable;

class MunicipalityRepository implements MunicipalityRepositoryInterface
{
    use Paginatable;
/**
 * Retrieve all municipalities with their full geographic hierarchy.
 *
 * @return Collection
 */
    public function getAll(): Collection
    {
        // governorate.country remontent via les relations
        return Municipality::with('city.governorate.country')->get();
    }

    /**
 * Retrieve all municipalities with their full geographic hierarchy, paginated.
 *
 * @param int $perPage
 * @param int $page
 * @return array
 */
    public function getAllPaginated(int $perPage = 10, int $page = 1): array
    {
        $query = Municipality::with('city.governorate.country');
        return $this->paginateQuery($query, $perPage, $page);
    }

/**
 * Find a municipality by its ID with its full geographic hierarchy.
 *
 * @param int $id
 * @return Municipality|null
 */

    public function findById(int $id): ?Municipality
    {
        return Municipality::with('city.governorate.country')->find($id);
    }
/**
 * Find a municipality by its name.
 *
 * @param string $name
 * @return Municipality|null
 */
    public function findByName(string $name): ?Municipality
    {
        return Municipality::where('name', $name)->first();
    }
/**
 * Create a new municipality record.
 *
 * @param array $data
 * @return Municipality
 */
    public function create(array $data): Municipality
    {
        return Municipality::create($data);
    }
/**
 * Update an existing municipality record.
 *
 * @param Municipality $municipality
 * @param array $data
 * @return Municipality
 */
    public function update(Municipality $municipality, array $data): Municipality
    {
        $municipality->update($data);
        return $municipality;
    }
/**
 * Delete a municipality from the database.
 *
 * @param Municipality $municipality
 * @return void
 */
    public function delete(Municipality $municipality): void
    {
        $municipality->delete();
    }
}