<?php

namespace App\Services;

use App\Models\User;
use App\Models\Zone;
use App\Repositories\StatisticsRepository;
use Illuminate\Support\Facades\Cache;

class StatisticsService
{
    public function __construct(
        private StatisticsRepository $statsRepo
    ) {}

    /**
 * Get global statistics for the super admin dashboard.
 *
 * Includes incident counters, status/category/municipality distributions,
 * user role breakdown, and monthly trend. Cached for 10 minutes.
 * Allowed users: super admin
 * @return array
 */
    public function getGlobalStats(): array
    {
        return Cache::remember('stats.global', now()->addMinutes(10), function () {
            return [
                // Compteurs
                'total_incidents'     => $this->statsRepo->totalIncidents(),
                'total_municipalities'=> $this->statsRepo->totalMunicipalities(),
                'total_agents'        => $this->statsRepo->totalAgents(),
                'avg_resolution_hours'=> $this->statsRepo->averageResolutionHours(),

                // Distributions
                'incidents_by_status'      => $this->statsRepo->incidentsByStatus(),
                'incidents_by_category'    => $this->statsRepo->incidentsByCategory(),
                'incidents_by_municipality'=> $this->statsRepo->incidentsByMunicipality(),
                'incidents_by_zone'        => $this->statsRepo->incidentsByZone(),
                'users_by_role'            => $this->statsRepo->usersByRole(),

                // Tendance
                'monthly_trend' => $this->statsRepo->monthlyTrend(),
            ];
        });
    }

    /**
 * Get statistics scoped to the authenticated admin's municipality.
 *
 * Includes incident, zone, and agent counters, distributions by status,
 * category, zone, and assignment status, and monthly trend.
 * Cached per municipality for 10 minutes.
 * Allowed users: admin municipal
 * @param User $user
 * @return array
 */
    public function getMunicipalStats(User $user): array
    {
        $municipalityId = $user->municipality_id;

        // Récupère les zone_ids de sa municipalité (déjà fait dans IncidentService)
        $zoneIds = Zone::where('municipality_id', $municipalityId)
            ->pluck('id')
            ->toArray();

        $cacheKey = "stats.municipal.{$municipalityId}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($zoneIds, $municipalityId) {
            return [
                // Compteurs
                'total_incidents'     => $this->statsRepo->totalIncidents($zoneIds),
                'total_zones'         => $this->statsRepo->totalZones($municipalityId),
                'total_agents'        => $this->statsRepo->totalAgents($municipalityId),
                'avg_resolution_hours'=> $this->statsRepo->averageResolutionHours($zoneIds),

                // Distributions incidents
                'incidents_by_status'  => $this->statsRepo->incidentsByStatus($zoneIds),
                'incidents_by_category'=> $this->statsRepo->incidentsByCategory($zoneIds),
                'incidents_by_zone'    => $this->statsRepo->incidentsByZone($zoneIds),

                // Agents
                'agents_by_status'  => $this->statsRepo->agentsByStatus($municipalityId),
                'agents_by_category'=> $this->statsRepo->agentsByCategory($municipalityId),

                // Assignments
                'assignments_by_status' => $this->statsRepo->assignmentsByStatus(),

                // Tendance
                'monthly_trend' => $this->statsRepo->monthlyTrend($zoneIds),
            ];
        });
    }

    /**
 * Get statistics for the authenticated agent.
 *
 * Includes assignment counters, closure times, and monthly assignment trends.
 * Cached for 5 minutes.
 * Allowed users: agent
 * @param User $user
 * @return array
 */
    public function getAgentStats(User $user): array
    {
        $agentId  = $user->id;
        $cacheKey = "stats.agent.{$agentId}";

        // Cache plus court pour l'agent (ses données changent plus souvent)
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($agentId) {
            return [
                // Compteurs
                'total_assignments' => $this->statsRepo->totalAssignments($agentId),
                'avg_closure_hours' => $this->statsRepo->agentAverageClosureHours($agentId),

                // Distributions
                'assignments_by_status' => $this->statsRepo->assignmentsByStatus($agentId),

                // Tendance
                'monthly_assignments' => $this->statsRepo->agentMonthlyAssignments($agentId),
            ];
        });
    }
}