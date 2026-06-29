<?php

namespace App\Repositories;

use App\Models\Assignment;
use App\Repositories\Contracts\AssignmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\Paginatable;


class AssignmentRepository implements AssignmentRepositoryInterface
{
        use Paginatable;

        /**
     * Relations à eager-loader systématiquement
     * ← corrigé : 'agents' → 'agent' (belongsTo, singulier)
     *    + ajout de incident.zone et incident.category pour éviter le lazy loading
     *    dans AssignmentService quand on accède à $assignment->incident->zone->name
     */

        private array $with = ['incident.zone', 'incident.category', 'agents'];

    /**
     * Récupérer tous les assignments
     */
    public function getAll(): Collection
    {
        // return Assignment::with(['incident', 'agents'])->get();
        return Assignment::with([
    'incident',
    'agents:id,name,status,category_id',
    'agents.category:id,name'
])->get();
    }
     /**
     * Récupérer tous les assignments paginés
     *
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getAllPaginated(int $perPage = 10, int $page = 1): array
    {
        $query = Assignment::with([
            'incident',
            'agents:id,name,status,category_id',
            'agents.category:id,name',
        ]);
 
        return $this->paginateQuery($query, $perPage, $page);
    }
    //  public function getAll(): Collection
    // {
    //     return Assignment::with($this->with)->get();
    // }
// public function findById(int $id): ?Assignment
//     {
//         return Assignment::with($this->with)->find($id);
//     }
 
//     public function getByAgentId(int $agentId): Collection
//     {
//         return Assignment::where('agent_id', $agentId)
//             ->with($this->with)
//             ->get();
//     }
 
//     public function getByAssignedBy(int $adminId): Collection
//     {
//         return Assignment::where('assigned_by', $adminId)
//             ->with($this->with)
//             ->get();
//     }
 
//     public function create(array $data): Assignment
//     {
//         return Assignment::create($data)->load($this->with);
//     }
 
//     public function update(Assignment $assignment, array $data): Assignment
//     {
//         $assignment->update($data);
//         return $assignment->load($this->with);
//     }
 
//     public function delete(Assignment $assignment): void
//     {
//         $assignment->delete();
//     }
    /**
     * Trouver un assignment par son ID avec ses relations
     */
    public function findById(int $id): ?Assignment
    {
        return Assignment::with([
    'incident',
    'agents:id,name,status,category_id',
    'agents.category:id,name'
])->find($id);
    }

    /**
     * Récupérer les assignments d'un agent
     */
    public function getByAgentId(int $agentId): Collection
    {
return Assignment::where('agent_id', $agentId)->with(['incident', 'agents'])->get();    }

/**
     * Récupérer les assignments d'un agent paginés
     *
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getByAgentIdPaginated(int $agentId, int $perPage = 10, int $page = 1): array
    {
        $query = Assignment::where('agent_id', $agentId)
            ->with(['incident', 'agents']);
 
        return $this->paginateQuery($query, $perPage, $page);
    }
    /**
     * Récupérer les assignments créés par un admin municipal
     */
    public function getByAssignedBy(int $adminId): Collection
    {
        return Assignment::where('assigned_by', $adminId)->with(['incident', 'agents'])->get();
    }
     /**
     * Récupérer les assignments créés par un admin municipal paginés
     *
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getByAssignedByPaginated(int $adminId, int $perPage = 10, int $page = 1): array
    {
        $query = Assignment::where('assigned_by', $adminId)
            ->with(['incident', 'agents']);
 
        return $this->paginateQuery($query, $perPage, $page);
    }

    /**
     * Créer un assignment
     */
    public function create(array $data): Assignment
    {
    return Assignment::create($data)->load('incident.zone', 'incident.category', 'agents');
    }

    /**
     * Mettre à jour un assignment
     */
    public function update(Assignment $assignment, array $data): Assignment
    {
        $assignment->update($data);

    return $assignment->load('incident', 'agents','incident.zone','incident.category');
    }

    /**
     * Supprimer un assignment
     */
    public function delete(Assignment $assignment): void
    {
        $assignment->delete();
    }
}