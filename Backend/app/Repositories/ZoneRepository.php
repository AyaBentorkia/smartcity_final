<?php

namespace App\Repositories;

use App\Models\Zone;
use App\Repositories\Contracts\ZoneRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ZoneRepository implements ZoneRepositoryInterface
{
    /**
     * Récupérer toutes les zones avec leurs municipalités
     */
    public function getAll(): Collection
    {
        // return Zone::with(['municipalities'])->get();
                return Zone::get();

    }

    /**
     * Trouver une zone par son ID
     */
    public function findById(int $id): ?Zone
    {
        return Zone::find($id);
    }

    /**
     * Trouver une zone par son nom
     */
    public function findByName(string $name): ?Zone
    {
        return Zone::where('name', $name)->first();
    }

    /**
     * Récupérer les zones d'une municipalité
     */
    public function getByMunicipalityId(int $municipalityId): Collection
    {
        return Zone::where('municipality_id', $municipalityId)
            ->with(['municipalities:id,name','incidents:id'])
            ->get();
    }

    /**
     * Créer une zone
     */
    public function create(array $data): Zone
    {
        return Zone::create($data);
    }

    /**
     * Mettre à jour une zone
     */
    public function update(Zone $zone, array $data): Zone
    {
        $zone->update($data);
        return $zone->fresh();
    }

    /**
     * Supprimer une zone
     */
    public function delete(Zone $zone): void
    {
        $zone->delete();
    }
}