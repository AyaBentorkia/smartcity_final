<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\Assignment;
use App\Models\Incident;
use App\Services\NotificationService;
use App\Models\Notification;
use App\Events\IncidentAssigned;
use App\Models\MunicipalAdmin;
use App\Models\User;
use App\Enums\AssignmentStatus;
use App\Enums\IncidentStatus;
use App\Services\IncidentService;
use App\Repositories\Contracts\AssignmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class AssignmentService
{
    public function __construct(
                private IncidentService $incidentService,
        private AssignmentRepositoryInterface $assignmentRepository
    ) {}

    /**
     * Lister tous les assignments
     */
    public function getAll(): Collection
    {
        return $this->assignmentRepository->getAll();
    }
    /**
     * Lister tous les assignments paginés
     */
    public function getAllPaginated(int $perPage = 10, int $page = 1): array
    {
        return $this->assignmentRepository->getAllPaginated($perPage, $page);
    }

    /**
     * Afficher un assignment avec ses relations
     */
    public function findById(int $id): Assignment
    {
        $assignment = $this->assignmentRepository->findById($id);

        if (!$assignment) {
            throw new \Exception('Assignment non trouvé', 404);
        }

        return $assignment;
    }

    /**
     * Récupérer les assignments de l'agent connecté
     */
    public function getMyAssignmentsByAgent(int $userId): Collection
    {
        return $this->assignmentRepository->getByAgentId($userId);
    }

    /**
     * Récupérer les assignments créés par l'admin municipal connecté
     */
    public function getMyAssignmentsByAdmin(int $userId): Collection
    {
        return $this->assignmentRepository->getByAssignedBy($userId);
    }
    /**
     * Récupérer les assignments de l'agent connecté paginés
     */
    public function getMyAssignmentsByAgentPaginated(int $userId, int $perPage = 10, int $page = 1): array
    {
        return $this->assignmentRepository->getByAgentIdPaginated($userId, $perPage, $page);
    }

    /**
     * Récupérer les assignments créés par l'admin municipal connecté paginés
     */
    public function getMyAssignmentsByAdminPaginated(int $userId, int $perPage = 10, int $page = 1): array
    {
        return $this->assignmentRepository->getByAssignedByPaginated($userId, $perPage, $page);
    }

    /**
     * Assigner un incident à un agent (par un admin municipal)
     * Vérifie que l'agent appartient à la bonne catégorie
     */
    public function assign(Incident $incident, int $agentUserId, int $adminUserId): Assignment
    {
                // ← corrigé : on charge incident.category une seule fois ici
        $incident->loadMissing('category', 'zone');
        // Log::info('incident category ',['incident'=>$incident->category]);

        // Vérifier que l'agent existe, a le bon rôle et la bonne catégorie
        $agent = User::where('id', $agentUserId)
        ->where('category_id', $incident->category_id)
        ->first();

    if (!$agent) {
        throw new \Exception('Agent ne correspond pas à la catégorie de l\'incident', 404);
    }
     

        $assignment = $this->assignmentRepository->create([
            'incident_id' => $incident->id,
            'agent_id'    => $agent->id,
            'assigned_by' => $adminUserId,
            'start_time'  => now(),
            'status' => AssignmentStatus::PENDING->value
        ]);

        if($assignment) $this->incidentService->updateStatus($incident->id,IncidentStatus::IN_PROGRESS->value);

$notification = Notification::create([
        'user_id'       => $agent->id,
        'incident_id'   => $incident->id,
        'assignment_id' => $assignment->id,
        'title'         => 'Incident assigné',
        'body'          => ($incident->category?->name ?? '') . ' — zone : ' . ($incident->zone?->name ?? ''),
        'type'          => 'incident_assigned',
    ]);

    broadcast(new IncidentAssigned($assignment, $agent->id, $notification->id));
        // Log::info('Assignment created: ' . $assignment->id);

        return $assignment;
    }

    /**
     * Réassigner un assignment à un autre agent (par un admin municipal)
     * Vérifie que l'admin est bien le propriétaire de l'assignment
     */
    public function update(Assignment $assignment, int $newAgentUserId, User $authUser): Assignment
    {
        $municipalAdmin = $authUser;

        if ($assignment->assigned_by !== $municipalAdmin->id) {
            throw new \Exception('Non autorisé', 403);
        }
                $assignment->loadMissing('incident.category', 'incident.zone');


        $agentUser = User::where('id', $newAgentUserId)
        ->where('category_id', $assignment->incident->category_id)
            ->first();

        if (!$agentUser) {
            throw new \Exception('Agent non trouvé', 404);
        }

        $updated = $this->assignmentRepository->update($assignment, [
            'agent_id'   => $agentUser->id,
            'start_time' => now(),
            'end_time'   => null,
        ]);
        $notification = Notification::create([
        'user_id'       => $agentUser->id,
        'incident_id'   => $assignment->incident_id,
        'assignment_id' => $assignment->id,
        'title'         => 'Incident réassigné',
        'body'          => ($assignment->incident?->category?->name ?? '') . ' — zone : ' . ($assignment->incident?->zone?->name ?? ''),
        'type'          => 'incident_assigned',
    ]);
    broadcast(new IncidentAssigned(
        $updated,
        $agentUser->id,
        $notification->id
    ));

        Log::info('Assignment updated: ' . $assignment->id);

        return $updated;
    }

    /**
     * Clôturer un assignment (par l'agent concerné)
     * Vérifie que l'agent est bien le titulaire et que l'assignment n'est pas déjà clôturé
     */
    public function close(Assignment $assignment, int $authUserId): Assignment
    {
// \Log::info('Close debug', [
//     'authUserId'         => $authUserId,
//     'agent->user_id'     => $agent?->user_id,
//     'assignment->agent_id' => $assignment->agent_id,
// ]);
       
        
        if ($assignment->agent_id !== $authUserId) {
            throw new \Exception('Non autorisé', 403);
        }

        if ($assignment->end_time) {
            throw new \Exception('Assignment déjà clôturé', 400);
        }

        $closed = $this->assignmentRepository->update($assignment, ['end_time' => now(),'status'   => AssignmentStatus::COMPLETED->value]);

        Log::info('Assignment closed: ' . $assignment->id);

        return $closed;
    }

    /**
     * Supprimer un assignment (par un admin municipal)
     * Vérifie les autorisations et remet l'incident au statut "validé"
     */
    public function delete(Assignment $assignment, User $authUser): void
    {
$municipalAdmin = $authUser;

        // ← corrigé : on charge assignedBy une seule fois
        $assignment->loadMissing('assignedBy');

        if ($assignment->assignedBy->municipality_id !== $municipalAdmin->municipality_id) {
            throw new \Exception('Non autorisé', 403);
        }

        if ($assignment->end_time) {
            throw new \Exception('Impossible de supprimer un assignment déjà clôturé', 400);
        }

        // Remettre l'incident en "validé" car il n'a plus d'agent
        $assignment->incident->update(['status' => 'reported']);

        $this->assignmentRepository->delete($assignment);

        Log::info('Assignment deleted: ' . $assignment->id);
    }
}