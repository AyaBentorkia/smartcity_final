<?php

namespace Database\Seeders;

/**
 * ╔══════════════════════════════════════════════════════════════════════════════╗
 * ║                     MassIncidentSeeder  —  v2.0                            ║
 * ║          Générateur de 5 000+ incidents urbains pour dataset IA             ║
 * ╠══════════════════════════════════════════════════════════════════════════════╣
 * ║  7 CATÉGORIES  |  28 MUNICIPALITÉS  |  ~110 ZONES                          ║
 * ║                                                                             ║
 * ║  CORPUS                                                                     ║
 * ║  ──────                                                                     ║
 * ║  • 4 250 incidents de base                                                  ║
 * ║  • + 638 récidives  (15 % — mêmes zone+catégorie, délai 30-90 j)           ║
 * ║  • + 250 cascades   ( 5 % — même jour, zones adjacentes)                   ║
 * ║  = 5 138 incidents au minimum                                               ║
 * ║                                                                             ║
 * ║  DISTRIBUTIONS                                                              ║
 * ║  ─────────────                                                              ║
 * ║  • Pondération par population (±40 % variance contrôlée)                   ║
 * ║  • Hotspot : 1 zone/municipalité → 35 % des incidents locaux               ║
 * ║  • Couverture 2022-2025 (pics jan, jul, oct)                                ║
 * ║  • Corrélations saisonnières et horaires par catégorie                      ║
 * ║  • Statuts temporels : 2022-2023 → 80 % résolu                             ║
 * ║    <30j → 70 % signalé                                                      ║
 * ║  • Priorités : critique 15 / haute 25 / moyenne 40 / faible 20             ║
 * ║                                                                             ║
 * ║  PRÉREQUIS MIGRATION                                                        ║
 * ║  ──────────────────────────────────────────────────────────────────────     ║
 * ║  ALTER TABLE incidents ADD COLUMN seeder_batch VARCHAR(50) NULL;            ║
 * ║  ALTER TABLE incidents ADD COLUMN priority VARCHAR(20) NULL;                ║
 * ╚══════════════════════════════════════════════════════════════════════════════╝
 */

use Illuminate\Database\Seeder;
use App\Models\Incident;
use App\Models\Category;
use App\Models\Zone;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Faker\Factory;

class MassIncidentSeeder extends Seeder
{
    // ── Constantes ────────────────────────────────────────────────────────────
    private const BATCH            = 'mass_v1';
    private const BASE_COUNT       = 4250;
    private const RECURRENCE_COUNT = 638;   // ≈15 % du total
    private const CASCADE_COUNT    = 250;   // ≈ 5 % du total
    private const CHUNK_SIZE       = 100;

    // ── Slugs catégorie (doit correspondre à CategorySeeder v2) ──────────────
    private const CAT_SLUGS = [
        'Incendies et risques thermiques / gaz' => 'incendie',
        'Voirie et infrastructures routières'    => 'voirie',
        'Électricité et éclairage public'        => 'eclairage',
        'Eau et assainissement'                  => 'eau',
        'Espaces verts et environnement'         => 'espaces',
        'Déchets et propreté urbaine'            => 'dechets',
        'Mobilier urbain'                        => 'mobilier',
    ];

    // ── Point d'entrée ────────────────────────────────────────────────────────
    public function run(): void
    {
        $faker = Factory::create('fr_FR');
        $now   = Carbon::now();

        // 1. Nettoyage idempotent ─────────────────────────────────────────────
        $this->command->info('🧹 Nettoyage batch précédent (' . self::BATCH . ')…');
        Incident::where('seeder_batch', self::BATCH)->forceDelete();

        // 2. Références ───────────────────────────────────────────────────────
        $this->command->info('📦 Chargement des zones, catégories, citoyens…');
        $catDefs   = $this->buildCategoryDefinitions();
        $citizens  = $this->loadCitizens();
        $zoneData  = $this->loadZoneData();
        $muniZones = $this->buildMuniIndex($zoneData);
        $muniPop   = $this->loadMuniPopulations();

        if (empty($zoneData) || empty($muniZones)) {
            $this->command->error('❌ Aucune zone. Exécutez ZoneSeeder d\'abord.');
            return;
        }

        // 3. Hotspots (1 zone critique / municipalité) ─────────────────────────
        $hotspots = [];
        foreach ($muniZones as $muniId => $zoneIds) {
            $hotspots[$muniId] = $zoneIds[array_rand($zoneIds)];
        }

        // 4. Allocation par population ─────────────────────────────────────────
        $muniAllocations = $this->computeMuniAllocations(
            self::BASE_COUNT, $muniZones, $muniPop, $faker
        );

        // 5. Génération incidents de base ──────────────────────────────────────
        $this->command->info('⚙️  Génération des ' . self::BASE_COUNT . ' incidents de base…');
        $this->command->getOutput()->progressStart(
            self::BASE_COUNT + self::RECURRENCE_COUNT + self::CASCADE_COUNT
        );

        $baseIncidents = $this->generateBaseIncidents(
            $faker, $now, $catDefs, $citizens, $zoneData,
            $muniZones, $hotspots, $muniAllocations
        );

        // 6. Récidives ────────────────────────────────────────────────────────
        $recurrences = $this->applyRecurrencePattern(
            $faker, $now, $baseIncidents, $catDefs, $zoneData, $citizens
        );

        // 7. Cascades ─────────────────────────────────────────────────────────
        $cascades = $this->applyCascadePattern(
            $faker, $now, $baseIncidents, $catDefs, $zoneData, $muniZones, $citizens
        );

        $allIncidents = array_merge($baseIncidents, $recurrences, $cascades);
        shuffle($allIncidents); // éviter les biais séquentiels

        // 8. Insertion transactionnelle par chunks ─────────────────────────────
        $this->command->info('💾 Insertion en base (' . count($allIncidents) . ' incidents)…');
        Incident::withoutEvents(function () use ($allIncidents) {
            DB::transaction(function () use ($allIncidents) {
                foreach (array_chunk($allIncidents, self::CHUNK_SIZE) as $chunk) {
                    DB::table('incidents')->insert($chunk);
                    $this->command->getOutput()->progressAdvance(count($chunk));
                }
            });
        });

        $this->command->getOutput()->progressFinish();
        $this->command->info(sprintf(
            '✅ %d incidents insérés — base: %d | récidives: %d | cascades: %d (batch: %s)',
            count($allIncidents), count($baseIncidents),
            count($recurrences), count($cascades), self::BATCH
        ));
    }

    // ═════════════════════════════════════════════════════════════════════════
    // ALLOCATION PAR POPULATION
    // ═════════════════════════════════════════════════════════════════════════

    private function computeMuniAllocations(
        int $total, array $muniZones, array $muniPop, \Faker\Generator $faker
    ): array {
        $muniIds   = array_keys($muniZones);
        $nMunis    = count($muniIds);
        $base      = $total / $nMunis;
        $allocations = [];
        $assigned  = 0;

        foreach ($muniIds as $i => $muniId) {
            $pop = $muniPop[$muniId] ?? 30000;
            // Facteur population : 0.7 → 1.3 borné à ±40%
            $popFactor = min(1.4, max(0.6, $pop / 55000));
            // Variance aléatoire ±20%
            $variance  = $faker->randomFloat(2, 0.80, 1.20);
            $count     = (int) round($base * $popFactor * $variance);
            $count     = max(50, $count); // minimum 50 par municipalité

            if ($i === $nMunis - 1) {
                $count = $total - $assigned; // reste exact
            }
            $allocations[$muniId] = max(1, $count);
            $assigned += $allocations[$muniId];
        }
        return $allocations;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // GÉNÉRATION INCIDENTS DE BASE
    // ═════════════════════════════════════════════════════════════════════════

    private function generateBaseIncidents(
        \Faker\Generator $faker, Carbon $now,
        array $catDefs, array $citizens,
        array $zoneData, array $muniZones,
        array $hotspots, array $muniAllocations
    ): array {
        $incidents = [];

        foreach ($muniAllocations as $muniId => $count) {
            $zoneIds     = $muniZones[$muniId];
            $hotspotId   = $hotspots[$muniId];
            $hotspotCount = (int) round($count * 0.35);
            $normalCount  = $count - $hotspotCount;

            // Zones non-hotspot (round-robin)
            $otherZones  = array_values(array_filter($zoneIds, fn($z) => $z !== $hotspotId));
            if (empty($otherZones)) $otherZones = [$hotspotId];

            for ($i = 0; $i < $count; $i++) {
                $inHotspot = $i < $hotspotCount;
                $zoneId    = $inHotspot ? $hotspotId : $otherZones[$i % count($otherZones)];
                $zone      = $zoneData[$zoneId];

                $catDef = $this->pickCategoryBySeason($faker, $catDefs);
                $date   = $this->generateDate($faker, $now);
                $hour   = $this->pickHour($faker, $catDef['slug'], $date);
                $date->setTime($hour, $faker->numberBetween(0, 59));

                $incidents[] = $this->buildIncidentRow(
                    $faker, $date, $catDef, $zone, $zoneId, $citizens, $now
                );

                $this->command->getOutput()->progressAdvance();
            }
        }
        return $incidents;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // RÉCIDIVES (15 %)
    // ═════════════════════════════════════════════════════════════════════════

    private function applyRecurrencePattern(
        \Faker\Generator $faker, Carbon $now,
        array $baseIncidents, array $catDefs,
        array $zoneData, array $citizens
    ): array {
        $recurrences = [];
        $pool = $baseIncidents;
        shuffle($pool);
        $pool = array_slice($pool, 0, self::RECURRENCE_COUNT);

        foreach ($pool as $original) {
            $originalDate = Carbon::parse($original['reported_at']);
            $interval     = $faker->numberBetween(30, 90);
            $newDate      = $originalDate->copy()->addDays($interval);

            // Ne pas dépasser aujourd'hui
            if ($newDate->gt($now)) {
                $newDate = $now->copy()->subDays($faker->numberBetween(1, 15));
            }

            $catDef   = $this->findCatDefById($catDefs, $original['category_id']);
            if (!$catDef) continue;

            $hour = $this->pickHour($faker, $catDef['slug'], $newDate);
            $newDate->setTime($hour, $faker->numberBetween(0, 59));

            $row = $this->buildIncidentRow(
                $faker, $newDate, $catDef,
                $zoneData[$original['zone_id']] ?? array_values($zoneData)[0],
                $original['zone_id'], $citizens, $now
            );
            $row['title'] .= ' (récidive)';

            $recurrences[] = $row;
            $this->command->getOutput()->progressAdvance();
        }
        return $recurrences;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // CASCADES (5 %)
    // ═════════════════════════════════════════════════════════════════════════

    private function applyCascadePattern(
        \Faker\Generator $faker, Carbon $now,
        array $baseIncidents, array $catDefs,
        array $zoneData, array $muniZones, array $citizens
    ): array {
        $cascades = [];
        $pool = $baseIncidents;
        shuffle($pool);
        $pool = array_slice($pool, 0, self::CASCADE_COUNT);

        foreach ($pool as $original) {
            $originalZone = $zoneData[$original['zone_id']] ?? null;
            if (!$originalZone) continue;

            // Zone adjacente dans la même municipalité
            $muniId        = $originalZone['muni_id'];
            $siblingZoneIds = array_values(
                array_filter($muniZones[$muniId] ?? [], fn($z) => $z !== $original['zone_id'])
            );

            if (empty($siblingZoneIds)) {
                $siblingZoneId = $original['zone_id'];
            } else {
                $siblingZoneId = $siblingZoneIds[array_rand($siblingZoneIds)];
            }

            $catDef    = $this->findCatDefById($catDefs, $original['category_id']);
            if (!$catDef) continue;

            // Même jour, heure décalée de 1-6h
            $cascadeDate = Carbon::parse($original['reported_at'])
                ->addHours($faker->numberBetween(1, 6));
            if ($cascadeDate->gt($now)) $cascadeDate = $now->copy()->subHours(1);

            $row = $this->buildIncidentRow(
                $faker, $cascadeDate, $catDef,
                $zoneData[$siblingZoneId],
                $siblingZoneId, $citizens, $now
            );
            $row['title'] .= ' (cascade)';

            $cascades[] = $row;
            $this->command->getOutput()->progressAdvance();
        }
        return $cascades;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // CONSTRUCTION D'UNE LIGNE INCIDENT
    // ═════════════════════════════════════════════════════════════════════════

    private function buildIncidentRow(
        \Faker\Generator $faker, Carbon $date, array $catDef,
        array $zone, int $zoneId, array $citizens, Carbon $now
    ): array {
        $title   = $faker->randomElement($catDef['titles']);
        $suffix  = ' — ' . $zone['name'];
        [$lat, $lng] = $this->randomCoords($zone['lat'], $zone['lng'], $zone['rayon']);

        $status   = $this->resolveStatus($faker, $date, $now);
        // $priority = $this->resolvePriority($faker, $catDef['slug']);
        $citizenId = $citizens[array_rand($citizens)];

        $resolvedAt = null;
        if ($status === 'resolved') {
            $resolvedAt = $date->copy()->addDays($faker->numberBetween(1, 14))->toDateTimeString();
            if (Carbon::parse($resolvedAt)->gt($now)) {
                $resolvedAt = $now->toDateTimeString();
            }
        }

        return [
            'title'        => $title . $suffix,
            'description'  => $this->buildDescription($faker, $title, $zone['name'], $date),
            'category_id'  => $catDef['id'],
            'zone_id'      => $zoneId,
            'city'         => $zone['city'],
            'address_text' => $zone['name'] . ', ' . $zone['city'],
            'latitude'     => $lat,
            'longitude'    => $lng,
            'status'       => $status,
            // 'priority'     => $priority,
            'citizen_id'   => $citizenId,
            'reported_at'  => $date->toDateTimeString(),
            'resolved_at'  => $resolvedAt,
            'seeder_batch' => self::BATCH,
            'created_at'   => $date->toDateTimeString(),
            'updated_at'   => $resolvedAt ?? $date->toDateTimeString(),
        ];
    }

    // ═════════════════════════════════════════════════════════════════════════
    // LOGIQUE TEMPORELLE
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Génère une date entre 2022-01-01 et aujourd'hui
     * avec pondération années (2024 dominant) et pics mensuels.
     */
    private function generateDate(\Faker\Generator $faker, Carbon $now): Carbon
    {
        $year = $this->weightedRandom($faker, [
            2022 => 15,
            2023 => 25,
            2024 => 35,
            2025 => 25,
        ]);

        // Pics mensuels : janvier (1.6×), juillet (1.7×), octobre (1.4×)
        $monthWeights = [
            1 => 16, 2 => 9,  3 => 10, 4 => 9, 5 => 10,
            6 => 11, 7 => 17, 8 => 11, 9 => 10, 10 => 14, 11 => 10, 12 => 11,
        ];

        // Pour 2025, ne pas dépasser le mois courant
        if ($year === 2025) {
            $maxMonth = (int) $now->format('n');
            foreach (array_keys($monthWeights) as $m) {
                if ($m > $maxMonth) $monthWeights[$m] = 0;
            }
        }

        $month = $this->weightedRandom($faker, $monthWeights);
        $maxDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Pour le mois courant de 2025, ne pas dépasser aujourd'hui
        if ($year === 2025 && $month === (int) $now->format('n')) {
            $maxDay = min($maxDay, (int) $now->format('j'));
        }

        $day = $faker->numberBetween(1, $maxDay);

        return Carbon::create($year, $month, $day, 12, 0, 0);
    }

    /**
     * Heures réalistes par catégorie.
     */
    private function pickHour(\Faker\Generator $faker, string $catSlug, Carbon $date): int
    {
        return match($catSlug) {
            'eclairage' => $this->weightedRandom($faker, [
                // 18h → 06h uniquement
                18=>10, 19=>12, 20=>14, 21=>12, 22=>10, 23=>8,
                0=>8, 1=>6, 2=>4, 3=>3, 4=>3, 5=>4, 6=>6,
            ]),
            'voirie' => $this->weightedRandom($faker, [
                // heures de pointe 07-09h et 17-19h
                6=>6, 7=>14, 8=>14, 9=>10, 10=>8, 11=>7, 12=>7,
                13=>7, 14=>7, 15=>7, 16=>8, 17=>14, 18=>14, 19=>10, 20=>7,
            ]),
            'dechets' => $this->weightedRandom($faker, [
                // matin collecte 06-10h
                5=>5, 6=>18, 7=>20, 8=>18, 9=>15, 10=>12,
                11=>6, 12=>4, 13=>3, 14=>3, 15=>3, 16=>5, 17=>5, 18=>3,
            ]),
            'eau' => $this->weightedRandom($faker, [
                // 24h, pics 06-08h et 20-22h
                0=>3, 1=>3, 2=>3, 3=>3, 4=>4, 5=>5,
                6=>12, 7=>12, 8=>10, 9=>7, 10=>6, 11=>6,
                12=>6, 13=>6, 14=>6, 15=>6, 16=>6, 17=>7,
                18=>7, 19=>8, 20=>12, 21=>12, 22=>10, 23=>5,
            ]),
            'espaces' => $this->weightedRandom($faker, [
                // heures ouvrées 08-18h
                8=>10, 9=>14, 10=>14, 11=>12, 12=>8,
                13=>8, 14=>12, 15=>12, 16=>12, 17=>10, 18=>8,
            ]),
            'incendie' => $this->weightedRandom($faker, [
                // Pics en journée (12-15h été = chaleur) + soir
                8=>5, 9=>6, 10=>8, 11=>9, 12=>12, 13=>12,
                14=>10, 15=>9, 16=>8, 17=>8, 18=>9, 19=>8,
                20=>8, 21=>6, 22=>5, 23=>4, 0=>3, 1=>2, 2=>2, 3=>2,
            ]),
            default => $faker->numberBetween(6, 22),
        };
    }

    /**
     * Catégorie pondérée par saison.
     */
    private function pickCategoryBySeason(\Faker\Generator $faker, array $catDefs): array
    {
        $month = $faker->numberBetween(1, 12);

        // Saison détectée à partir du mois
        $season = match(true) {
            $month >= 12 || $month <= 2 => 'winter',
            $month >= 3 && $month <= 5  => 'spring',
            $month >= 6 && $month <= 8  => 'summer',
            default                     => 'autumn',
        };

        $weights = match($season) {
            'winter' => [
                'incendie'  => 8,
                'voirie'    => 28, // +40%
                'eclairage' => 25, // +35%
                'eau'       => 22, // +25%
                'espaces'   => 6,
                'dechets'   => 6,
                'mobilier'  => 5,
            ],
            'spring' => [
                'incendie'  => 6,
                'voirie'    => 18, // +15%
                'eclairage' => 7,
                'eau'       => 20, // +30%
                'espaces'   => 29, // +45%
                'dechets'   => 13, // +10%
                'mobilier'  => 7,
            ],
            'summer' => [
                'incendie'  => 20, // +30%
                'voirie'    => 10,
                'eclairage' => 13, // +10%
                'eau'       => 30, // +50%
                'espaces'   => 13, // +10%
                'dechets'   => 9,
                'mobilier'  => 5,
            ],
            'autumn' => [
                'incendie'  => 7,
                'voirie'    => 24, // +30%
                'eclairage' => 8,
                'eau'       => 18, // +20%
                'espaces'   => 12, // +10%
                'dechets'   => 24, // +40%
                'mobilier'  => 7,
            ],
        };

        $slug = $this->weightedRandom($faker, $weights);
        return $this->findCatDefBySlug($catDefs, $slug) ?? $catDefs[array_rand($catDefs)];
    }

    /**
     * Statut selon la règle temporelle.
     */
    private function resolveStatus(\Faker\Generator $faker, Carbon $date, Carbon $now): string
    {
        $ageInDays = $date->diffInDays($now, false);
        if ($ageInDays < 0) $ageInDays = 0;

        // 2022-2023 : 80 % résolu
        if ($date->year <= 2023) {
            return $this->weightedRandom($faker, [
                'resolved'    => 80,
                'rejected'    => 10,
                'in progress' => 5,
                'reported'    => 5,
            ]);
        }

        // <30 jours : 70 % signalé
        if ($ageInDays < 30) {
            return $this->weightedRandom($faker, [
                'reported'    => 70,
                'validated'   => 10,
                'in progress' => 12,
                'rejected'    => 8,
            ]);
        }

        // Général
        return $this->weightedRandom($faker, [
            'reported'    => 25,
            'validated'   => 5,
            'in progress' => 25,
            'resolved'    => 35,
            'rejected'    => 10,
        ]);
    }

    /**
     * Priorité avec pondération et boost pour les incendies.
     */
    // private function resolvePriority(\Faker\Generator $faker, string $catSlug): string
    // {
    //     $weights = match($catSlug) {
    //         'incendie' => ['critique' => 40, 'haute' => 35, 'moyenne' => 20, 'faible' => 5],
    //         'eau'      => ['critique' => 20, 'haute' => 30, 'moyenne' => 35, 'faible' => 15],
    //         'voirie'   => ['critique' => 10, 'haute' => 25, 'moyenne' => 45, 'faible' => 20],
    //         default    => ['critique' => 15, 'haute' => 25, 'moyenne' => 40, 'faible' => 20],
    //     };
    //     return $this->weightedRandom($faker, $weights);
    // }

    // ═════════════════════════════════════════════════════════════════════════
    // DÉFINITIONS DES CATÉGORIES
    // ═════════════════════════════════════════════════════════════════════════

    private function buildCategoryDefinitions(): array
    {
        $id = fn(string $name) => Category::where('name', $name)->value('id');

        return [
            [
                'slug'   => 'incendie',
                'id'     => $id('Incendies et risques thermiques / gaz'),
                'titles' => [
                    'Incendie bâtiment', 'Incendie véhicule', 'Incendie poubelle',
                    'Incendie terrains vagues', 'Fuite de gaz', 'Odeur suspecte de gaz',
                    'Explosion transformateur', 'Fumée suspecte', 'Feu de câbles électriques',
                    'Incendie commerce', 'Incendie entrepôt', 'Départ de feu végétation',
                    'Fuite gaz réseau', 'Incendie toiture', 'Flammes depuis fenêtre',
                    'Incendie dépôt sauvage', 'Forte odeur de brûlé', 'Feu de palissade',
                ],
            ],
            [
                'slug'   => 'voirie',
                'id'     => $id('Voirie et infrastructures routières'),
                'titles' => [
                    'Nid-de-poule', 'Chaussée dégradée', 'Fissure longitudinale route',
                    'Affaissement chaussée', 'Trottoir cassé', 'Trottoir effondré',
                    'Signalisation manquante', 'Panneau stop absent', 'Route inondée',
                    'Regard ouvert sur chaussée', 'Travaux non signalés', 'Glissement terrain',
                    'Accident circulation', 'Dos d\'âne détérioré', 'Trou dangereux chaussée',
                    'Bordure trottoir arrachée', 'Marquage au sol effacé', 'Pont fissuré',
                    'Route encombrée débris', 'Grille avaloir enfoncée',
                ],
            ],
            [
                'slug'   => 'eclairage',
                'id'     => $id('Électricité et éclairage public'),
                'titles' => [
                    'Lampadaire en panne', 'Lampadaire cassé', 'Ampoule grillée',
                    'Éclairage faible rue', 'Poteau électrique penché',
                    'Câble électrique exposé', 'Armoire électrique ouverte',
                    'Éclairage éteint toute la nuit', 'Court-circuit réseau éclairage',
                    'Lampadaire tombé sur voie', 'Vol équipement éclairage',
                    'Scintillement lampadaires', 'Boîte électrique vandalisée',
                    'Fil électrique pendant', 'Panne générale quartier',
                ],
            ],
            [
                'slug'   => 'eau',
                'id'     => $id('Eau et assainissement'),
                'titles' => [
                    "Fuite d'eau réseau", 'Canalisation cassée', 'Égout bouché',
                    'Égout débordé', 'Mauvaise odeur égout', 'Regard assainissement ouvert',
                    'Inondation urbaine', 'Accumulation eau pluviale', 'Drainage défectueux',
                    'Eau potable coupée', 'Pression eau insuffisante',
                    'Fuite bouche incendie', 'Conduite exposée', 'Pollution cours d\'eau',
                    'Station pompage en panne', 'Caniveau bouché', 'Inondation sous-sol',
                    'Rupture conduite principale', 'Fontaine publique en fuite',
                    'Eaux usées sur chaussée',
                ],
            ],
            [
                'slug'   => 'espaces',
                'id'     => $id('Espaces verts et environnement'),
                'titles' => [
                    'Arbre tombé sur voie', 'Branche dangereuse', 'Pelouse non entretenue',
                    'Herbes hautes trottoir', 'Dégradation jardin public',
                    'Aire de jeux endommagée', 'Irrigation défectueuse',
                    'Poubelle parc saturée', 'Fontaine parc cassée',
                    'Arrosage excessif trottoir', 'Sol parc dégradé',
                    'Nid frelons asiatiques', 'Arbre penché sur bâtiment',
                    'Déchets jardin public', 'Palmier malade', 'Élagage urgent',
                    'Taille haie urgente', 'Nettoyage parc après tempête',
                ],
            ],
            [
                'slug'   => 'dechets',
                'id'     => $id('Déchets et propreté urbaine'),
                'titles' => [
                    'Déchets non collectés', 'Décharge sauvage', 'Poubelle saturée',
                    'Débris sur voie publique', 'Mauvaise odeur déchets',
                    'Encombrants abandonnés', 'Gravats illégaux', 'Dépôt sauvage plage',
                    'Bennes débordantes', 'Sacs poubelle éventrés',
                    'Déchets verts non ramassés', 'Ordures marché non collectées',
                    'Verre brisé voie publique', 'Dépôt pneus usagés',
                    'Détritus autour conteneur', 'Poubelles renversées',
                ],
            ],
            [
                'slug'   => 'mobilier',
                'id'     => $id('Mobilier urbain'),
                'titles' => [
                    'Banc cassé', 'Abribus endommagé', 'Barrière cassée',
                    'Poteau signalisation tombé', 'Panneau publicitaire illégal',
                    'Corbeille publique cassée', 'Mobilier vandalisme',
                    'Horodateur en panne', 'Toilettes publiques hors service',
                    'Fontaine buvable cassée', 'Rampe handicapés endommagée',
                    'Grille arbre arrachée', 'Borne vélo abîmée',
                    'Table pique-nique dégradée', 'Miroir route brisé',
                ],
            ],
        ];
    }

    // ═════════════════════════════════════════════════════════════════════════
    // CHARGEMENT DES DONNÉES
    // ═════════════════════════════════════════════════════════════════════════

    private function loadZoneData(): array
    {
        $map = [];
        Zone::with('municipalities')->get()->each(function ($zone) use (&$map) {
            $city = $zone->municipalities?->city
                ?? preg_replace('/-\w+$/', '', $zone->name);
            $map[$zone->id] = [
                'name'    => $zone->name,
                'city'    => $city,
                'lat'     => (float) $zone->latitude_center,
                'lng'     => (float) $zone->longitude_center,
                'rayon'   => (float) $zone->rayon_km,
                'muni_id' => $zone->municipality_id,
            ];
        });
        return $map;
    }

    private function buildMuniIndex(array $zoneData): array
    {
        $index = [];
        foreach ($zoneData as $zoneId => $meta) {
            if ($meta['muni_id']) {
                $index[$meta['muni_id']][] = $zoneId;
            }
        }
        return $index;
    }

    private function loadCitizens(): array
    {
        $ids = User::where('role', 'citizen')->pluck('id')->toArray();
        return $ids ?: [User::first()?->id ?? 1];
    }

    private function loadMuniPopulations(): array
    {
        return \App\Models\Municipality::pluck('number_of_inhabitants', 'id')->toArray();
    }

    // ═════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ═════════════════════════════════════════════════════════════════════════

    private function buildDescription(
        \Faker\Generator $faker, string $title, string $zoneName, Carbon $date
    ): string {
        $templates = [
            "Signalement de « {$title} » dans la zone {$zoneName}. Constaté le {$date->format('d/m/Y')} à {$date->format('H\hi')}. Intervention requise.",
            "Un problème de type « {$title} » a été signalé le {$date->format('d/m/Y')} dans le secteur {$zoneName}. La situation affecte la sécurité des habitants.",
            "Incident enregistré : {$title}. Zone : {$zoneName}. Date : {$date->format('d/m/Y')}. Merci d'intervenir dans les meilleurs délais.",
            "Un citoyen signale « {$title} » en zone {$zoneName}. Heure : {$date->format('H:i')} le {$date->format('d/m/Y')}. Statut à vérifier.",
            "Problème urbain détecté : {$title} — secteur {$zoneName}. Signalement automatique du {$date->format('d/m/Y')}.",
        ];
        return $faker->randomElement($templates);
    }

    private function randomCoords(float $lat, float $lng, float $radiusKm): array
    {
        $r     = ($radiusKm * 0.85) / 111.0;
        $angle = deg2rad(rand(0, 360));
        $dist  = sqrt(rand(10, 100) / 100) * $r;
        return [
            round($lat + $dist * cos($angle), 6),
            round($lng + $dist * sin($angle), 6),
        ];
    }

    /**
     * Tirage pondéré générique — $weights = [valeur => poids_entier]
     */
    private function weightedRandom(\Faker\Generator $faker, array $weights): mixed
    {
        $filtered = array_filter($weights, fn($w) => $w > 0);
        if (empty($filtered)) return array_key_first($weights);

        $total = array_sum($filtered);
        $rand  = rand(1, $total);
        $cumul = 0;
        foreach ($filtered as $key => $weight) {
            $cumul += $weight;
            if ($rand <= $cumul) return $key;
        }
        return array_key_last($filtered);
    }

    private function findCatDefBySlug(array $catDefs, string $slug): ?array
    {
        foreach ($catDefs as $cd) {
            if ($cd['slug'] === $slug) return $cd;
        }
        return null;
    }

    private function findCatDefById(array $catDefs, ?int $id): ?array
    {
        if (!$id) return null;
        foreach ($catDefs as $cd) {
            if ($cd['id'] === $id) return $cd;
        }
        return null;
    }
}