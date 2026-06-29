<?php

namespace App\Repositories;

use App\Models\Incident;
use App\Models\City;
use App\Repositories\Contracts\IncidentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\Paginatable;


/**
 * IncidentRepository
 *
 * Responsable des opérations de persistence et des requêtes complexes
 * liées au modèle `Incident`. Fournit des méthodes spécialisées pour la
 * pagination, les filtres par zone/municipalité, et les jointures utiles
 * pour l'API (relations préchargées).
 */
class IncidentRepository implements IncidentRepositoryInterface
{
        use Paginatable;

    /**
     * Récupérer tous les incidents avec leurs relations
     */
    public function getAll(): Collection
    {
        return Incident::with(['citizen', 'medias','category'])->get();
    }
     /**
     * Récupérer tous les incidents paginés
     *
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
   // IncidentRepository — remplace paginateQuery par :
public function getAllPaginated(int $perPage = 15, int $page = 1, ?int $municipalityId = null, ?string $search = null): array
{
    $query = Incident::with(['citizen:id,name,phone', 'medias:id,url', 'category:id,name,color']);
 
    // Filtre par municipalité via la relation zone
    if ($municipalityId !== null) {
        $query->whereHas('zone', function ($q) use ($municipalityId) {
            $q->where('municipality_id', $municipalityId);
        });
    }
 
    // Filtre recherche texte (titre ou description)
    if ($search !== null && $search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
 
    $paginator = $query->latest('reported_at')->paginate(perPage: $perPage, page: $page);
 
    return [
        'data' => $paginator->getCollection(),
        'meta' => [
            'current_page'   => $paginator->currentPage(),
            'per_page'       => $paginator->perPage(),
            'total'          => $paginator->total(),
            'last_page'      => $paginator->lastPage(),
            'has_more_pages' => $paginator->hasMorePages(),
        ],
    ];
}

    /**
     * Trouver un incident par son ID
     */
    public function findById(int $id): ?Incident
    {
        return Incident::with(['citizen', 'medias'])->find($id);
    }

    /**
     * Trouver un incident par son ID ou lever une exception 404
     */
    public function findByIdOrFail(int $id): Incident
    {
return Incident::with(['citizen', 'medias', 'zone', 'category'])->findOrFail($id);
    }

    /**
     * Trouver un incident appartenant à un citoyen spécifique
     */
    public function findByIdAndCitizen(int $id, int $citizenId): ?Incident
    {
        return Incident::where('id', $id)
            ->where('citizen_id', $citizenId)
            ->first();
    }

    /**
     * Créer un incident
     */
    public function create(array $data): Incident
    {
        return Incident::create($data);
    }

    /**
     * Mettre à jour un incident
     */
    public function update(Incident $incident, array $data): Incident
    {
        $incident->update($data);
        return $incident->fresh()->load(['citizen', 'medias']);;
    }

    /**
     * Supprimer un incident
     */
    public function delete(Incident $incident): void
    {
        $incident->delete();
    }

    /**
     * Récupérer les incidents d'un citoyen
     */
    public function getByCitizenId(int $citizenId): Collection
    {
        return Incident::where('citizen_id', $citizenId)
            ->with(['citizen', 'manager', 'incidentMedia'])
            ->get();
    }
    /**
     * Récupérer les incidents d'un citoyen paginés
     *
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getByCitizenIdPaginated(int $citizenId, int $perPage = 15, int $page = 1): array
    {
        $query = Incident::where('citizen_id', $citizenId)
            ->with(['medias', 'category']);
 
        return $this->paginateQuery($query, $perPage, $page);
    }

    // /**
    //  * Récupérer les incidents dans une liste de zones (pour admin municipal)
    //  */
    // public function getByZoneIds(array $zoneIds): Collection
    // {
    //     return Incident::with(['category', 'zone', 'medias','citizen', 'city:id,name'])
    // ->whereIn('zone_id', $zoneIds)
    // ->get();
    // }
     /**
     * Récupérer les incidents dans une liste de zones (pour admin municipal)
     */
    public function getByZoneIds(array $zoneIds): Collection
    {
        return Incident::with(['category', 'zone', 'medias','citizen'])
    ->whereIn('zone_id', $zoneIds)
    ->get();
    }
    /**
     * Récupérer les incidents dans une liste de zones paginés — pour admin municipal
     *
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getByZoneIdsPaginated(array $zoneIds, int $perPage = 15, int $page = 1): array
    {
        $query = Incident::with(['category:id,name', 'zone:id,name', 'medias:id,url', 'citizen:id,name,phone,email'])
            ->whereIn('zone_id', $zoneIds);
 
        return $this->paginateQuery($query, $perPage, $page);
    }

    /**
     * Récupérer les incidents par catégorie
     */
    public function getByCategoryId(int $categoryId): Collection
    {
        return Incident::where('category_id', $categoryId)
            ->with(['citizen', 'medias'])
            ->get();
    }

    /**
     * Récupérer les incidents par statut
     */
    public function getByStatus(string $status): Collection
    {
        return Incident::where('status', $status)
            ->with(['citizen', 'medias'])
            ->get();
    }

    /**
     * Récupérer les incidents d'une zone
     */
    public function getByZoneId(int $zoneId): Collection
    {
        return Incident::where('zone_id', $zoneId)
            ->with(['citizen', 'medias'])
            ->get();
    }
    public function getBycitizenIdWithRelations(int $citizenId): Collection
{
    return Incident::where('citizen_id', $citizenId)
        ->with(['medias', 'category'])
        ->get();
}
 /**
     * Récupérer les incidents d'une zone
     */
public function getByCityId(int $cityId): Collection
{
    return Incident::with(['category:id,name', 'zone:id,name', 'medias', 'citizen'])
        ->where('city_id', $cityId)
        ->get();
}
 /**
     * Récupérer les incidents d'une city paginated
     */
public function getByCityIdPaginated(int $cityId, int $perPage = 10, int $page = 1): array
{
    $query = Incident::with(['category:id,name', 'zone:id,name', 'medias', 'citizen', 'city:id,name'])
        ->where('city_id', $cityId);
    return $this->paginateQuery($query, $perPage, $page);
}

// public function getByZoneIdsPaginated(array $zoneIds, int $perPage = 10, int $page = 1): array
// {
//     $paginator = Incident::with(['category:id,name', 'medias:id,url', 'zone:id,name','citizen:id,name,phone,email'])
//         ->whereIn('zone_id', $zoneIds)
//         ->orderBy('reported_at', 'desc')
//         ->paginate(perPage: $perPage, page: $page);

//     return [
//         'data' => $paginator->getCollection(),
//         'meta' => [
//             'current_page'       => $paginator->currentPage(),
//             'per_page'           => $paginator->perPage(),
//             'total'              => $paginator->total(),
//             'last_page'          => $paginator->lastPage(),
//             'from'               => $paginator->firstItem() ?? 0,
//             'to'                 => $paginator->lastItem()  ?? 0,
//             'has_more_pages'     => $paginator->hasMorePages(),
//             'has_previous_pages' => $paginator->currentPage() > 1,  // ← corrige le bug Vue
//         ],
//     ];
// }

}