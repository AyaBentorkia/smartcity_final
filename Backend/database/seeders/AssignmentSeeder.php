<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Incident;
use App\Models\User;
use App\Models\Municipality;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

/**
 * AssignmentSeeder — v2.0
 *
 * Stratégie : pour chaque incident présent en base (hors MassIncidentSeeder),
 * on trouve automatiquement un agent compatible (même municipality, même catégorie)
 * et on crée l'assignation.
 *
 * Section 1 : assignations manuelles nommées (incidents de IncidentSeeder).
 * Section 2 : auto-assignation des incidents non encore assignés.
 */
class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->manualAssignments();
            $this->autoAssignRemaining();
        });

        $total = Assignment::count();
        $this->command->info("✅ Assignations créées/vérifiées : {$total} au total.");
    }

    // ─────────────────────────────────────────────────────────────
    // SECTION 1 — Assignations manuelles (incidents nommés)
    // ─────────────────────────────────────────────────────────────
    private function manualAssignments(): void
    {
        // Résolution rapide des admins
        $adm = fn(string $email) => User::where('email', $email)->value('id');
        // Résolution agent par slug de catégorie + muni slug + numéro
        $agt = fn(string $catSlug, int $n, string $muniSlug)
            => User::where('email', "agent.{$catSlug}.{$n}.{$muniSlug}@baladia.tn")->value('id');

        // Incidents par titre partiel
        $inc = fn(string $pattern)
            => Incident::where('title', 'LIKE', $pattern)->value('id');

        $rows = [
            // ── Monastir ─────────────────────────────────────────
            [
                'incident_id'  => $inc('Nid-de-poule%Monastir%'),
                'agent_id'     => $agt('voirie', 1, 'monastir'),
                'assigned_by'  => $adm('admin.monastir@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(4),
                'end_time'     => null,
            ],
            [
                'incident_id'  => $inc('Lampadaire%Monastir-Est%'),
                'agent_id'     => $agt('eclairage', 1, 'monastir'),
                'assigned_by'  => $adm('admin.monastir@baladia.tn'),
                'status'       => 'completed',
                'start_time'   => now()->subDays(2),
                'end_time'     => now()->subDays(1),
            ],
            [
                'incident_id'  => $inc('Dépôt sauvage plage Monastir%'),
                'agent_id'     => $agt('dechets', 1, 'monastir'),
                'assigned_by'  => $adm('admin.monastir@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(6),
                'end_time'     => null,
            ],
            [
                'incident_id'  => $inc('Arbre dangereux Monastir-Nord%'),
                'agent_id'     => $agt('espaces', 1, 'monastir'),
                'assigned_by'  => $adm('admin.monastir@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(1),
                'end_time'     => null,
            ],
            // ── Moknine ───────────────────────────────────────────
            [
                'incident_id'  => $inc('Trottoir dégradé Moknine%'),
                'agent_id'     => $agt('voirie', 1, 'moknine'),
                'assigned_by'  => $adm('admin.moknine@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(3),
                'end_time'     => null,
            ],
            [
                'incident_id'  => $inc('Poubelles pleines Moknine%'),
                'agent_id'     => $agt('dechets', 1, 'moknine'),
                'assigned_by'  => $adm('admin.moknine@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(5),
                'end_time'     => null,
            ],
            // ── Ksar Hellal ───────────────────────────────────────
            [
                'incident_id'  => $inc("Fuite d'eau Ksar Hellal%"),
                'agent_id'     => $agt('eau', 1, 'ksarhellal'),
                'assigned_by'  => $adm('admin.ksarhellal@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subHours(12),
                'end_time'     => null,
            ],
            // ── Téboulba ──────────────────────────────────────────
            [
                'incident_id'  => $inc('Bancs vandalisés Téboulba%'),
                'agent_id'     => $agt('mobilier', 1, 'teboulba'),
                'assigned_by'  => $adm('admin.teboulba@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(2),
                'end_time'     => null,
            ],
            // ── Jemmal ────────────────────────────────────────────
            [
                'incident_id'  => $inc('Route abîmée Jemmal%'),
                'agent_id'     => $agt('voirie', 1, 'jemmal'),
                'assigned_by'  => $adm('admin.jemmal@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(2),
                'end_time'     => null,
            ],
            // ── Sayada ────────────────────────────────────────────
            [
                'incident_id'  => $inc('Éclairage défaillant Sayada%'),
                'agent_id'     => $agt('eclairage', 1, 'sayada'),
                'assigned_by'  => $adm('admin.sayada@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(7),
                'end_time'     => null,
            ],
            // ── Sousse Médina ─────────────────────────────────────
            [
                'incident_id'  => $inc('Caniveau bouché Sousse Médina%'),
                'agent_id'     => $agt('eau', 1, 'sousse.medina'),
                'assigned_by'  => $adm('admin.sousse.medina@baladia.tn'),
                'status'       => 'completed',
                'start_time'   => now()->subDays(14),
                'end_time'     => now()->subDays(10),
            ],
            [
                'incident_id'  => $inc('Éclairage défaillant Sousse corniche%'),
                'agent_id'     => $agt('eclairage', 1, 'sousse.medina'),
                'assigned_by'  => $adm('admin.sousse.medina@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(7),
                'end_time'     => null,
            ],
            // ── Sousse Riadh ──────────────────────────────────────
            [
                'incident_id'  => $inc('Trottoir effondré Sousse Riadh%'),
                'agent_id'     => $agt('voirie', 1, 'sousse.riadh'),
                'assigned_by'  => $adm('admin.sousse.riadh@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(9),
                'end_time'     => null,
            ],
            // ── Hammam Sousse ─────────────────────────────────────
            [
                'incident_id'  => $inc('Dépôt sauvage plage Hammam Sousse%'),
                'agent_id'     => $agt('dechets', 1, 'hammam'),
                'assigned_by'  => $adm('admin.hammamsousse@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(3),
                'end_time'     => null,
            ],
            // ── Sousse Jawhara ────────────────────────────────────
            [
                'incident_id'  => $inc("Fuite d'eau Sousse Jawhara%"),
                'agent_id'     => $agt('eau', 1, 'sousse.jawhara'),
                'assigned_by'  => $adm('admin.sousse.jawhara@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subHours(6),
                'end_time'     => null,
            ],
            // ── Akouda ────────────────────────────────────────────
            [
                'incident_id'  => $inc('Poubelles pleines Akouda%'),
                'agent_id'     => $agt('dechets', 1, 'akouda'),
                'assigned_by'  => $adm('admin.akouda@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(4),
                'end_time'     => null,
            ],
            // ── M'saken ───────────────────────────────────────────
            [
                'incident_id'  => $inc("Arbre dangereux M'saken%"),
                'agent_id'     => $agt('espaces', 1, 'msaken'),
                'assigned_by'  => $adm('admin.msaken@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(1),
                'end_time'     => null,
            ],
            // ── Enfida ────────────────────────────────────────────
            [
                'incident_id'  => $inc('Route endommagée Enfida%'),
                'agent_id'     => $agt('voirie', 1, 'enfida'),
                'assigned_by'  => $adm('admin.enfida@baladia.tn'),
                'status'       => 'pending',
                'start_time'   => now()->subDays(2),
                'end_time'     => null,
            ],
        ];

        foreach ($rows as $data) {
            if (!$data['incident_id'] || !$data['agent_id'] || !$data['assigned_by']) {
                continue;
            }
            Assignment::firstOrCreate(
                ['incident_id' => $data['incident_id'], 'agent_id' => $data['agent_id']],
                $data
            );
        }
    }

    // ─────────────────────────────────────────────────────────────
    // SECTION 2 — Auto-assignation des incidents sans assignation
    // ─────────────────────────────────────────────────────────────
    private function autoAssignRemaining(): void
    {
        // Carte : municipality_id + category_id → [agent_id, admin_id]
        $agentMap  = [];
        $adminMap  = [];

        $agents = User::where('role', 'agent')
            ->whereNotNull('municipality_id')
            ->whereNotNull('category_id')
            ->get(['id', 'municipality_id', 'category_id']);

        foreach ($agents as $agent) {
            $key = "{$agent->municipality_id}_{$agent->category_id}";
            $agentMap[$key][] = $agent->id;
        }

        $admins = User::where('role', 'municipal admin')
            ->whereNotNull('municipality_id')
            ->get(['id', 'municipality_id']);

        foreach ($admins as $admin) {
            $adminMap[$admin->municipality_id] = $admin->id;
        }

        // Incidents sans assignation (hors mass seeder)
        $unassigned = Incident::whereDoesntHave('assignments')
            ->whereNull('seeder_batch')
            ->with('zone')
            ->get();

        $roundRobin = [];

        foreach ($unassigned as $incident) {
            $muniId = optional($incident->zone)->municipality_id;
            $catId  = $incident->category_id;

            if (!$muniId || !$catId) continue;

            $key = "{$muniId}_{$catId}";
            if (empty($agentMap[$key])) continue;

            // Round-robin sur les agents disponibles
            if (!isset($roundRobin[$key])) $roundRobin[$key] = 0;
            $agentId   = $agentMap[$key][$roundRobin[$key] % count($agentMap[$key])];
            $roundRobin[$key]++;

            $adminId = $adminMap[$muniId] ?? null;
            if (!$adminId) continue;

            $status    = $incident->status === 'resolved' ? 'completed' : 'pending';
            $startTime = $incident->reported_at
                ? \Carbon\Carbon::parse($incident->reported_at)->addHours(rand(1, 24))
                : now()->subDays(rand(1, 30));
            $endTime   = $status === 'completed' ? $startTime->copy()->addDays(rand(1, 5)) : null;

            Assignment::firstOrCreate(
                ['incident_id' => $incident->id, 'agent_id' => $agentId],
                [
                    'incident_id' => $incident->id,
                    'agent_id'    => $agentId,
                    'assigned_by' => $adminId,
                    'status'      => $status,
                    'start_time'  => $startTime,
                    'end_time'    => $endTime,
                ]
            );
        }
    }
}