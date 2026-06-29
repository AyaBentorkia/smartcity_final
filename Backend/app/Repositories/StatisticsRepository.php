<?php

namespace App\Repositories;

use App\Models\Assignment;
use App\Models\Incident;
use App\Models\Municipality;
use App\Models\User;
use App\Models\Zone;
use App\Models\City;
use Illuminate\Support\Facades\DB;

class StatisticsRepository
{
    // ═══════════════════════════════════════════════
    //  INCIDENTS
    // ═══════════════════════════════════════════════
/**
 * Count incidents grouped by status.
 * Pass $zoneIds to scope to a municipality; null returns global counts.
 *
 * @param int[]|null $zoneIds
 * @return array<string, int>
 */
    public function incidentsByStatus(?array $zoneIds = null): array
    {
        return Incident::query()
            ->when($zoneIds, fn($q) => $q->whereIn('zone_id', $zoneIds))
            // ->when($cityId, fn($q) => $q->where('city_id', $cityId))
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

/**
 * Count incidents grouped by category.
 * Pass $zoneIds to scope to a municipality; null returns global counts.
 *
 * @param int[]|null $zoneIds
 * @return array
 */
    public function incidentsByCategory(?array $zoneIds = null): array
    {
        return Incident::query()
            ->when($zoneIds, fn($q) => $q->whereIn('zone_id', $zoneIds))
            // ->when($cityId, fn($q) => $q->where('city_id', $cityId))
            ->join('categories', 'incidents.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('COUNT(*) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get()->toArray();
    }

/**
 * Count incidents grouped by municipality.
 * Only available for SuperAdmin.
 *
 * @return array
 */
    public function incidentsByMunicipality(): array
    {
        return Incident::query()
            ->join('zones',          'incidents.zone_id',      '=', 'zones.id')
            ->join('municipalities', 'zones.municipality_id',  '=', 'municipalities.id')
            ->select('municipalities.name', DB::raw('COUNT(*) as total'))
            ->groupBy('municipalities.id', 'municipalities.name')
            ->orderByDesc('total')
            ->get()->toArray();
    }

/**
 * Count incidents grouped by zone.
 * Pass $zoneIds to scope to a municipality; null returns global counts.
 *
 * @param int[]|null $zoneIds
 * @return array
 */
    public function incidentsByZone(?array $zoneIds = null): array
    {
        return Incident::query()
            ->when($zoneIds, fn($q) => $q->whereIn('zone_id', $zoneIds))
            ->join('zones', 'incidents.zone_id', '=', 'zones.id')
            ->select('zones.name', DB::raw('COUNT(*) as total'))
            ->groupBy('zones.id', 'zones.name')
            ->orderByDesc('total')
            ->get()->toArray();
    }

/**
 * Retrieve the monthly trend of incidents.
 * Pass $zoneIds to scope to a municipality; null returns global counts.
 *
 * @param int[]|null $zoneIds
 * @return array
 */
    public function monthlyTrend(?array $zoneIds = null): array
    {
        return Incident::query()
            ->when($zoneIds, fn($q) => $q->whereIn('zone_id', $zoneIds))
            // ->when($cityId, fn($q) => $q->where('city_id', $cityId))
            ->where('reported_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(reported_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()->toArray();
    }

    /**
 * Calculate the average resolution time in hours.
 * Pass $zoneIds to scope to a municipality; null returns global counts.
 *
 * @param int[]|null $zoneIds
 * @return float|null
 */
    public function averageResolutionHours(?array $zoneIds = null): ?float
    {
        $val = Incident::query()
            // ->when($zoneIds, fn($q) => $q->whereIn('zone_id', $zoneIds))
            ->when($zoneIds, fn($q) => $q->whereIn('zone_id', $zoneIds))
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, reported_at, resolved_at)) as avg_hours')
            ->value('avg_hours');

        return $val ? round((float) $val, 1) : null;
    }

    // ═══════════════════════════════════════════════
    //  ASSIGNMENTS
    // ═══════════════════════════════════════════════

    /**
 * Count assignments grouped by status.
 * Pass $agentId to scope to a specific agent; null returns global counts.
 *
 * @param int|null $agentId
 * @return array<string, int>
 */
    public function assignmentsByStatus(?int $agentId = null): array
    {
        return Assignment::query()
            ->when($agentId, fn($q) => $q->where('agent_id', $agentId))
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    /**
 * Retrieve the monthly trend of assignments for a specific agent.
 * Pass $agentId to scope to a specific agent; null returns global counts.
 *
 * @param int $agentId
 * @return array
 */
    public function agentMonthlyAssignments(int $agentId): array
    {
        return Assignment::query()
            ->where('agent_id', $agentId)
            ->where('start_time', '>=', now()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(start_time, '%Y-%m') as month"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()->toArray();
    }

    /**
 * Calculate the average closure time in hours for a specific agent.
 * Pass $agentId to scope to a specific agent; null returns global counts.
 *
 * @param int $agentId
 * @return float|null
 */
    public function agentAverageClosureHours(int $agentId): ?float
    {
        $val = Assignment::query()
            ->where('agent_id', $agentId)
            ->whereNotNull('end_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, start_time, end_time)) as avg_hours')
            ->value('avg_hours');

        return $val ? round((float) $val, 1) : null;
    }

    // ═══════════════════════════════════════════════
    //  UTILISATEURS
    // ═══════════════════════════════════════════════

    /**
 * Retrieve users grouped by role.
 *
 * @return array
 */
    public function usersByRole(): array
    {
        return User::query()
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();
    }

    /**
 * Retrieve agents of a municipality grouped by status.
 *
 * @param int $municipalityId
 * @return array
 */
    public function agentsByStatus(int $municipalityId): array
    {
        return User::query()
            ->where('municipality_id', $municipalityId)
            ->where('role', 'agent')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    /**
 * Retrieve agents of a municipality grouped by category.
 *
 * @param int $municipalityId
 * @return array
 */
    public function agentsByCategory(int $municipalityId): array
    {
        return User::query()
            ->where('municipality_id', $municipalityId)
            ->where('role', 'agent')
            ->join('categories', 'users.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('COUNT(*) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get()->toArray();
    }

    // ═══════════════════════════════════════════════
    //  COMPTEURS SIMPLES
    // ═══════════════════════════════════════════════

    /**
 * Count total incidents, optionally scoped to a list of zones.
 *
 * @param int[]|null $zoneIds
 * @return int
 */
    public function totalIncidents(?array $zoneIds = null): int
    {
        // return Incident::when($zoneIds, fn($q) => $q->whereIn('zone_id', $zoneIds))->count();
        return Incident::when($zoneIds, fn($q) => $q->whereIn('zone_id', $zoneIds))->count();
    }
/**
 * Count total municipalities.
 *
 * @return int
 */
    public function totalMunicipalities(): int   { return Municipality::count(); }
/**
 * Count total zones, optionally scoped to a municipality.
 *
 * @param int|null $municipalityId
 * @return int
 */
    public function totalZones(?int $municipalityId = null): int
    {
        return Zone::when($municipalityId, fn($q) => $q->where('municipality_id', $municipalityId))->count();
    }
/**
 * Count total agents, optionally scoped to a municipality.
 *
 * @param int|null $municipalityId
 * @return int
 */
    public function totalAgents(?int $municipalityId = null): int
    {
        return User::where('role', 'agent')
            ->when($municipalityId, fn($q) => $q->where('municipality_id', $municipalityId))
            ->count();
    }

    /**
 * Count total assignments, optionally scoped to a specific agent.
 *
 * @param int|null $agentId
 * @return int
 */
    public function totalAssignments(?int $agentId = null): int
    {
        return Assignment::when($agentId, fn($q) => $q->where('agent_id', $agentId))->count();
    }
}