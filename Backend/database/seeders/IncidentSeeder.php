<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Incident;
use App\Models\User;
use App\Models\Category;
use App\Models\Zone;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * IncidentSeeder — v2.0
 *
 * Incidents nommés de référence couvrant les 7 catégories,
 * utilisés pour les tests et l'AssignmentSeeder.
 *
 * Couvre Monastir (13 mun.) + Sousse (15 mun.).
 * Patterns saisonniers réels 2023-2024.
 */
class IncidentSeeder extends Seeder
{
    public function run(): void
    {
        // ── Médias ────────────────────────────────────────────────
        $makeMedia = function (string $category): ?Media {
            $extensions = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
            foreach ($extensions as $ext) {
                $path = database_path("seeders/images/{$category}.{$ext}");
                if (file_exists($path)) {
                    $filename = Str::uuid() . '.' . $ext;
                    $dest     = "incidents/{$filename}";
                    Storage::disk('public')->put($dest, file_get_contents($path));
                    return Media::create([
                        'url' => rtrim(config('app.url'), '/') . '/storage/' . $dest,
                    ]);
                }
            }
            return null; // Pas bloquant si pas d'image
        };

        // ── Citoyens ──────────────────────────────────────────────
        $ahmed   = User::where('email', 'ahmed.benali@gmail.com')->first();
        $fatma   = User::where('email', 'fatma.trabelsi@gmail.com')->first();
        $karim   = User::where('email', 'karim.saidi@gmail.com')->first();
        $nour    = User::where('email', 'nour.jelassi@gmail.com')->first();
        $youssef = User::where('email', 'youssef.khemiri@gmail.com')->first();
        $sarra   = User::where('email', 'sarra.hamdi@gmail.com')->first();
        $mohamed = User::where('email', 'mohamed.cherif@gmail.com')->first();
        $amira   = User::where('email', 'amira.bouzid@gmail.com')->first();
        $rami    = User::where('email', 'rami.gharbi@gmail.com')->first();
        $ines    = User::where('email', 'ines.mansouri@gmail.com')->first();
        $bilel   = User::where('email', 'bilel.ayari@gmail.com')->first();
        $walid   = User::where('email', 'walid.dridi@gmail.com')->first();

        // ── Catégories ────────────────────────────────────────────
        $catIncendie  = Category::where('name', 'Incendies et risques thermiques / gaz')->first();
        $catVoirie    = Category::where('name', 'Voirie et infrastructures routières')->first();
        $catEclairage = Category::where('name', 'Électricité et éclairage public')->first();
        $catEau       = Category::where('name', 'Eau et assainissement')->first();
        $catEspaces   = Category::where('name', 'Espaces verts et environnement')->first();
        $catDechets   = Category::where('name', 'Déchets et propreté urbaine')->first();
        $catMobilier  = Category::where('name', 'Mobilier urbain')->first();

        // ── Zones ─────────────────────────────────────────────────
        $z = fn(string $name) => Zone::where('name', $name)->first();

        $zMonastirCentre  = $z('Monastir-Centre');
        $zMonastirNord    = $z('Monastir-Nord');
        $zMonastirEst     = $z('Monastir-Est');
        $zMonastirSud     = $z('Monastir-Sud');
        $zMonastirOuest   = $z('Monastir-Ouest');
        $zMoknineC        = $z('Moknine-Centre');
        $zMoknineEst      = $z('Moknine-Est');
        $zKsarC           = $z('Ksar Hellal-Centre');
        $zKsarNord        = $z('Ksar Hellal-Nord');
        $zJemmalC         = $z('Jemmal-Centre');
        $zJemmalNord      = $z('Jemmal-Nord');
        $zTeboulbaEst     = $z('Téboulba-Est');
        $zTeboulbaC       = $z('Téboulba-Centre');
        $zSayadaC         = $z('Sayada-Lamta-Bou Hajar-Centre');
        $zBemblaC         = $z('Bembla-Centre');
        $zZeramdineC      = $z('Zéramdine-Centre');

        $zSousseMediaC    = $z('Sousse Médina-Centre');
        $zSousseMediaEst  = $z('Sousse Médina-Est');
        $zSousseRiadhC    = $z('Sousse Riadh-Centre');
        $zSousseRiadhEst  = $z('Sousse Riadh-Est');
        $zSousseJawC      = $z('Sousse Jawhara-Centre');
        $zSousseSidiC     = $z('Sousse Sidi Abdelhamid-Centre');
        $zHammamC         = $z('Hammam Sousse-Centre');
        $zHammamEst       = $z('Hammam Sousse-Est');
        $zAkoudaC         = $z('Akouda-Centre');
        $zMsakenC         = $z('M\'saken-Centre');
        $zKalaKebirC      = $z('Kalâa Kebira-Centre');
        $zEnfidaC         = $z('Enfida-Centre');
        $zHerglaC         = $z('Hergla-Centre');

        // ── Mapping catégorie → image ─────────────────────────────
        $categoryImageMap = [
            'Incendies et risques thermiques / gaz' => null,   // pas d'image dédiée
            'Voirie et infrastructures routières'   => 'voirie',
            'Électricité et éclairage public'       => 'eclairage',
            'Eau et assainissement'                 => 'eau',
            'Espaces verts et environnement'        => 'espaces',
            'Déchets et propreté urbaine'           => 'dechets',
            'Mobilier urbain'                       => 'mobilier',
        ];

        // ── Dataset des incidents de référence ────────────────────
        $incidents = [

            // ════════════════════════════════════════════════════════
            // INCENDIES ET RISQUES THERMIQUES / GAZ
            // ════════════════════════════════════════════════════════
            [
                'title'       => 'Fuite de gaz Monastir-Centre',
                'description' => 'Forte odeur de gaz signalée rue Ibn Khaldoun. Risque d\'explosion immédiat. Évacuation en cours.',
                'category_id' => $catIncendie?->id,
                'zone_id'     => $zMonastirCentre?->id,
                // 'city'        => 'Monastir',
                'address_text'=> 'Rue Ibn Khaldoun, Monastir-Centre',
                'latitude'    => 35.7643,
                'longitude'   => 10.8113,
                'status'      => 'validated',
                'citizen_id'  => $ahmed?->id,
                'reported_at' => now()->subDays(2),
            ],
            [
                'title'       => 'Incendie véhicule Ksar Hellal-Nord',
                'description' => 'Voiture en feu parking zone industrielle. Intervention pompiers requise.',
                'category_id' => $catIncendie?->id,
                'zone_id'     => $zKsarNord?->id,
                // 'city'        => 'Ksar Hellal',
                'address_text'=> 'Zone industrielle nord, Ksar Hellal',
                'latitude'    => 35.6630,
                'longitude'   => 10.8880,
                'status'      => 'resolved',
                'citizen_id'  => $mohamed?->id,
                'reported_at' => now()->subDays(15),
                'resolved_at' => now()->subDays(14),
            ],
            [
                'title'       => 'Fumée suspecte Sousse Médina-Est',
                'description' => 'Fumée noire épaisse visible depuis le marché central. Origine inconnue.',
                'category_id' => $catIncendie?->id,
                'zone_id'     => $zSousseMediaEst?->id,
                // 'city'        => 'Sousse Médina',
                'address_text'=> 'Marché central, Sousse Médina',
                'latitude'    => 35.8281,
                'longitude'   => 10.6418,
                'status'      => 'in progress',
                'citizen_id'  => $karim?->id,
                'reported_at' => now()->subDays(1),
            ],
            [
                'title'       => 'Incendie bâtiment Hammam Sousse-Est',
                'description' => 'Incendie au rez-de-chaussée d\'un immeuble résidentiel. Résidents évacués.',
                'category_id' => $catIncendie?->id,
                'zone_id'     => $zHammamEst?->id,
                // 'city'        => 'Hammam Sousse',
                'address_text'=> 'Résidence Nour, Avenue de la Plage, Hammam Sousse',
                'latitude'    => 35.8593,
                'longitude'   => 10.5998,
                'status'      => 'resolved',
                'citizen_id'  => $nour?->id,
                'reported_at' => now()->subDays(20),
                'resolved_at' => now()->subDays(19),
            ],
            [
                'title'       => 'Odeur suspecte gaz Moknine-Centre',
                'description' => 'Odeur de gaz persistante dans le quartier du marché. Plusieurs habitants signalent la fuite.',
                'category_id' => $catIncendie?->id,
                'zone_id'     => $zMoknineC?->id,
                // 'city'        => 'Moknine',
                'address_text'=> 'Marché central, Moknine',
                'latitude'    => 35.6422,
                'longitude'   => 10.9015,
                'status'      => 'reported',
                'citizen_id'  => $youssef?->id,
                'reported_at' => now()->subHours(3),
            ],

            // ════════════════════════════════════════════════════════
            // VOIRIE ET INFRASTRUCTURES ROUTIÈRES
            // ════════════════════════════════════════════════════════
            [
                'title'       => 'Nid-de-poule dangereux Monastir-Centre',
                'description' => 'Large nid-de-poule avenue Habib Bourguiba causant des accidents.',
                'category_id' => $catVoirie?->id,
                'zone_id'     => $zMonastirCentre?->id,
                // 'city'        => 'Monastir',
                'address_text'=> 'Avenue Habib Bourguiba, Monastir-Centre',
                'latitude'    => 35.7643,
                'longitude'   => 10.8113,
                'status'      => 'in progress',
                'citizen_id'  => $ahmed?->id,
                'reported_at' => now()->subDays(4),
            ],
            [
                'title'       => 'Trottoir dégradé Moknine-Centre',
                'description' => 'Trottoir effondré rue des Martyrs, dangereux pour piétons.',
                'category_id' => $catVoirie?->id,
                'zone_id'     => $zMoknineC?->id,
                // 'city'        => 'Moknine',
                'address_text'=> 'Rue des Martyrs, Moknine-Centre',
                'latitude'    => 35.6422,
                'longitude'   => 10.9015,
                'status'      => 'reported',
                'citizen_id'  => $youssef?->id,
                'reported_at' => now()->subDays(3),
            ],
            [
                'title'       => 'Route abîmée Jemmal-Centre',
                'description' => 'Chaussée très dégradée route nationale. Ornières profondes post-pluie.',
                'category_id' => $catVoirie?->id,
                'zone_id'     => $zJemmalC?->id,
                // 'city'        => 'Jemmal',
                'address_text'=> 'Route nationale 1, Jemmal',
                'latitude'    => 35.6221,
                'longitude'   => 10.7610,
                'status'      => 'reported',
                'citizen_id'  => $bilel?->id,
                'reported_at' => now()->subDays(2),
            ],
            [
                'title'       => 'Trottoir effondré Sousse Riadh-Centre',
                'description' => 'Effondrement de trottoir quartier résidentiel. Risque de chute.',
                'category_id' => $catVoirie?->id,
                'zone_id'     => $zSousseRiadhC?->id,
                // 'city'        => 'Sousse Riadh',
                'address_text'=> 'Rue des Fleurs, Quartier Riadh, Sousse',
                'latitude'    => 35.8381,
                'longitude'   => 10.5936,
                'status'      => 'in progress',
                'citizen_id'  => $walid?->id,
                'reported_at' => now()->subDays(9),
            ],
            [
                'title'       => 'Route endommagée Enfida-Centre',
                'description' => 'Route principale d\'Enfida criblée de trous après les pluies.',
                'category_id' => $catVoirie?->id,
                'zone_id'     => $zEnfidaC?->id,
                // 'city'        => 'Enfida',
                'address_text'=> 'Avenue principale, Enfida',
                'latitude'    => 36.1414,
                'longitude'   => 10.4672,
                'status'      => 'reported',
                'citizen_id'  => $ines?->id,
                'reported_at' => now()->subDays(2),
            ],
            [
                'title'       => 'Chaussée fissurée Ksar Hellal-Centre',
                'description' => 'Fissures longitudinales dangereuses avenue de la République.',
                'category_id' => $catVoirie?->id,
                'zone_id'     => $zKsarC?->id,
                // 'city'        => 'Ksar Hellal',
                'address_text'=> 'Avenue de la République, Ksar Hellal',
                'latitude'    => 35.6474,
                'longitude'   => 10.8906,
                'status'      => 'resolved',
                'citizen_id'  => $mohamed?->id,
                'reported_at' => now()->subMonths(2),
                'resolved_at' => now()->subMonths(1)->subDays(20),
            ],

            // ════════════════════════════════════════════════════════
            // ÉLECTRICITÉ ET ÉCLAIRAGE PUBLIC
            // ════════════════════════════════════════════════════════
            [
                'title'       => 'Lampadaire en panne Monastir-Est',
                'description' => 'Lampadaire hors service corniche depuis 3 nuits. Zone sombre.',
                'category_id' => $catEclairage?->id,
                'zone_id'     => $zMonastirEst?->id,
                // 'city'        => 'Monastir',
                'address_text'=> 'Corniche de Monastir, zone Est',
                'latitude'    => 35.7643,
                'longitude'   => 10.8350,
                'status'      => 'resolved',
                'citizen_id'  => $fatma?->id,
                'reported_at' => now()->subDays(2),
                'resolved_at' => now()->subDays(1),
            ],
            [
                'title'       => 'Éclairage défaillant Sayada-Centre',
                'description' => 'Tout le quartier nord de Sayada plongé dans le noir la nuit.',
                'category_id' => $catEclairage?->id,
                'zone_id'     => $zSayadaC?->id,
                // 'city'        => 'Sayada-Lamta-Bou Hajar',
                'address_text'=> 'Quartier Nord, Sayada',
                'latitude'    => 35.7000,
                'longitude'   => 10.7700,
                'status'      => 'reported',
                'citizen_id'  => $ahmed?->id,
                'reported_at' => now()->subDays(7),
            ],
            [
                'title'       => 'Éclairage défaillant Sousse corniche',
                'description' => 'Série de lampadaires HS sur 500m de corniche. Insécurité nocturne.',
                'category_id' => $catEclairage?->id,
                'zone_id'     => $zSousseMediaEst?->id,
                // 'city'        => 'Sousse Médina',
                'address_text'=> 'Corniche de Sousse, face hôtel Marhaba',
                'latitude'    => 35.8275,
                'longitude'   => 10.6450,
                'status'      => 'in progress',
                'citizen_id'  => $karim?->id,
                'reported_at' => now()->subDays(7),
            ],
            [
                'title'       => 'Câble électrique exposé Sousse Riadh-Est',
                'description' => 'Câble électrique à découvert sur trottoir. Danger électrocution.',
                'category_id' => $catEclairage?->id,
                'zone_id'     => $zSousseRiadhEst?->id,
                // 'city'        => 'Sousse Riadh',
                'address_text'=> 'Rue de la Paix, Sousse Riadh-Est',
                'latitude'    => 35.8360,
                'longitude'   => 10.5970,
                'status'      => 'validated',
                'citizen_id'  => $walid?->id,
                'reported_at' => now()->subDays(1),
            ],

            // ════════════════════════════════════════════════════════
            // EAU ET ASSAINISSEMENT
            // ════════════════════════════════════════════════════════
            [
                'title'       => "Fuite d'eau Ksar Hellal-Centre",
                'description' => 'Canalisation principale percée avenue République. Eau coulant 24h.',
                'category_id' => $catEau?->id,
                'zone_id'     => $zKsarC?->id,
                // 'city'        => 'Ksar Hellal',
                'address_text'=> 'Avenue de la République, Ksar Hellal',
                'latitude'    => 35.6474,
                'longitude'   => 10.8906,
                'status'      => 'in progress',
                'citizen_id'  => $ahmed?->id,
                'reported_at' => now()->subHours(12),
            ],
            [
                'title'       => 'Caniveau bouché Sousse Médina-Centre',
                'description' => 'Caniveau obstrué créant une retenue d\'eau sur 200m. Odeurs.',
                'category_id' => $catEau?->id,
                'zone_id'     => $zSousseMediaC?->id,
                // 'city'        => 'Sousse Médina',
                'address_text'=> 'Rue de la Médina, Sousse',
                'latitude'    => 35.8254,
                'longitude'   => 10.6386,
                'status'      => 'resolved',
                'citizen_id'  => $karim?->id,
                'reported_at' => now()->subDays(14),
                'resolved_at' => now()->subDays(10),
            ],
            [
                'title'       => "Fuite d'eau Sousse Jawhara-Centre",
                'description' => 'Rupture conduite principale quartier Jawhara. Coupure eau imminente.',
                'category_id' => $catEau?->id,
                'zone_id'     => $zSousseJawC?->id,
                // 'city'        => 'Sousse Jawhara',
                'address_text'=> 'Boulevard Jawhara, Sousse',
                'latitude'    => 35.8450,
                'longitude'   => 10.5830,
                'status'      => 'validated',
                'citizen_id'  => $walid?->id,
                'reported_at' => now()->subHours(6),
            ],
            [
                'title'       => 'Inondation urbaine Jemmal-Nord',
                'description' => 'Zone nord de Jemmal inondée après fortes pluies. Drainage insuffisant.',
                'category_id' => $catEau?->id,
                'zone_id'     => $zJemmalNord?->id,
                // 'city'        => 'Jemmal',
                'address_text'=> 'Zone industrielle nord, Jemmal',
                'latitude'    => 35.6380,
                'longitude'   => 10.7600,
                'status'      => 'resolved',
                'citizen_id'  => $bilel?->id,
                'reported_at' => now()->subMonths(3),
                'resolved_at' => now()->subMonths(3)->addDays(3),
            ],
            [
                'title'       => 'Égout bouché Monastir-Ouest',
                'description' => 'Égout débordant rue du marché. Eaux usées sur chaussée.',
                'category_id' => $catEau?->id,
                'zone_id'     => $zMonastirOuest?->id,
                // 'city'        => 'Monastir',
                'address_text'=> 'Rue du marché, Monastir-Ouest',
                'latitude'    => 35.7643,
                'longitude'   => 10.7900,
                'status'      => 'in progress',
                'citizen_id'  => $fatma?->id,
                'reported_at' => now()->subDays(3),
            ],

            // ════════════════════════════════════════════════════════
            // ESPACES VERTS ET ENVIRONNEMENT
            // ════════════════════════════════════════════════════════
            [
                'title'       => 'Arbre dangereux Monastir-Nord',
                'description' => 'Arbre penché menaçant la chaussée avenue Tahar Haddad. Risque chute.',
                'category_id' => $catEspaces?->id,
                'zone_id'     => $zMonastirNord?->id,
                // 'city'        => 'Monastir',
                'address_text'=> 'Avenue Tahar Haddad, Monastir-Nord',
                'latitude'    => 35.7780,
                'longitude'   => 10.8000,
                'status'      => 'reported',
                'citizen_id'  => $fatma?->id,
                'reported_at' => now()->subDays(1),
            ],
            [
                'title'       => "Arbre dangereux M'saken-Centre",
                'description' => 'Grand eucalyptus déraciné partiellement suite aux vents. Urgent.',
                'category_id' => $catEspaces?->id,
                'zone_id'     => $zMsakenC?->id,
                // 'city'        => "M'saken",
                'address_text'=> "Avenue principale, M'saken",
                'latitude'    => 35.7314,
                'longitude'   => 10.5731,
                'status'      => 'validated',
                'citizen_id'  => $amira?->id,
                'reported_at' => now()->subDays(1),
            ],
            [
                'title'       => 'Pelouse envahie Hammam Sousse-Centre',
                'description' => 'Espace vert principal non entretenu depuis 2 mois. Herbes hautes 1m.',
                'category_id' => $catEspaces?->id,
                'zone_id'     => $zHammamC?->id,
                // 'city'        => 'Hammam Sousse',
                'address_text'=> 'Parc municipal, Hammam Sousse',
                'latitude'    => 35.8593,
                'longitude'   => 10.5920,
                'status'      => 'reported',
                'citizen_id'  => $nour?->id,
                'reported_at' => now()->subDays(10),
            ],
            [
                'title'       => 'Irrigation défectueuse Akouda-Centre',
                'description' => 'Système d\'arrosage automatique fuyard inondant trottoir.',
                'category_id' => $catEspaces?->id,
                'zone_id'     => $zAkoudaC?->id,
                // 'city'        => 'Akouda',
                'address_text'=> 'Parc Akouda-Centre',
                'latitude'    => 35.8742,
                'longitude'   => 10.5702,
                'status'      => 'resolved',
                'citizen_id'  => $sarra?->id,
                'reported_at' => now()->subDays(30),
                'resolved_at' => now()->subDays(25),
            ],

            // ════════════════════════════════════════════════════════
            // DÉCHETS ET PROPRETÉ URBAINE
            // ════════════════════════════════════════════════════════
            [
                'title'       => 'Dépôt sauvage plage Monastir-Est',
                'description' => 'Décharge sauvage formée derrière le parking plage Est. Odeurs pestilentielles.',
                'category_id' => $catDechets?->id,
                'zone_id'     => $zMonastirEst?->id,
                // 'city'        => 'Monastir',
                'address_text'=> 'Parking plage Est, Monastir',
                'latitude'    => 35.7680,
                'longitude'   => 10.8390,
                'status'      => 'in progress',
                'citizen_id'  => $ahmed?->id,
                'reported_at' => now()->subDays(6),
            ],
            [
                'title'       => 'Poubelles pleines Moknine-Centre',
                'description' => 'Bennes à ordures débordantes marché central non collectées 5 jours.',
                'category_id' => $catDechets?->id,
                'zone_id'     => $zMoknineC?->id,
                // 'city'        => 'Moknine',
                'address_text'=> 'Marché central, Moknine',
                'latitude'    => 35.6422,
                'longitude'   => 10.9015,
                'status'      => 'reported',
                'citizen_id'  => $youssef?->id,
                'reported_at' => now()->subDays(5),
            ],
            [
                'title'       => 'Dépôt sauvage plage Hammam Sousse-Est',
                'description' => 'Déchets plastiques et encombrants déposés illégalement plage nord.',
                'category_id' => $catDechets?->id,
                'zone_id'     => $zHammamEst?->id,
                // 'city'        => 'Hammam Sousse',
                'address_text'=> 'Plage nord, Hammam Sousse',
                'latitude'    => 35.8650,
                'longitude'   => 10.5980,
                'status'      => 'in progress',
                'citizen_id'  => $nour?->id,
                'reported_at' => now()->subDays(3),
            ],
            [
                'title'       => 'Poubelles pleines Akouda-Centre',
                'description' => 'Zone touristique : bennes saturées. Forte odeur en pleine saison.',
                'category_id' => $catDechets?->id,
                'zone_id'     => $zAkoudaC?->id,
                // 'city'        => 'Akouda',
                'address_text'=> 'Zone hôtelière, Akouda',
                'latitude'    => 35.8742,
                'longitude'   => 10.5702,
                'status'      => 'reported',
                'citizen_id'  => $sarra?->id,
                'reported_at' => now()->subDays(4),
            ],
            [
                'title'       => 'Décharge sauvage Téboulba-Centre',
                'description' => 'Accumulation de gravats et déchets ménagers route du port.',
                'category_id' => $catDechets?->id,
                'zone_id'     => $zTeboulbaC?->id,
                // 'city'        => 'Téboulba',
                'address_text'=> 'Route du port, Téboulba',
                'latitude'    => 35.6431,
                'longitude'   => 10.9764,
                'status'      => 'resolved',
                'citizen_id'  => $rami?->id,
                'reported_at' => now()->subDays(45),
                'resolved_at' => now()->subDays(40),
            ],

            // ════════════════════════════════════════════════════════
            // MOBILIER URBAIN
            // ════════════════════════════════════════════════════════
            [
                'title'       => 'Bancs vandalisés Téboulba-Est',
                'description' => 'Bancs publics bord de mer vandalisés. Bois arraché, métal tordu.',
                'category_id' => $catMobilier?->id,
                'zone_id'     => $zTeboulbaEst?->id,
                // 'city'        => 'Téboulba',
                'address_text'=> 'Promenade bord de mer, Téboulba-Est',
                'latitude'    => 35.6431,
                'longitude'   => 10.9950,
                'status'      => 'reported',
                'citizen_id'  => $rami?->id,
                'reported_at' => now()->subDays(2),
            ],
            [
                'title'       => 'Abribus endommagé Sousse Sidi-Centre',
                'description' => 'Abri bus principal avenue Bourguiba vandalisé. Vitre brisée.',
                'category_id' => $catMobilier?->id,
                'zone_id'     => $zSousseSidiC?->id,
                // 'city'        => 'Sousse Sidi Abdelhamid',
                'address_text'=> 'Avenue Bourguiba, Sousse Sidi Abdelhamid',
                'latitude'    => 35.8331,
                'longitude'   => 10.6050,
                'status'      => 'in progress',
                'citizen_id'  => $karim?->id,
                'reported_at' => now()->subDays(5),
            ],
            [
                'title'       => 'Barrière cassée Kalâa Kebira-Centre',
                'description' => 'Barrière de protection en béton détruite carrefour principal.',
                'category_id' => $catMobilier?->id,
                'zone_id'     => $zKalaKebirC?->id,
                // 'city'        => 'Kalâa Kebira',
                'address_text'=> 'Carrefour principal, Kalâa Kebira',
                'latitude'    => 35.8620,
                'longitude'   => 10.5473,
                'status'      => 'resolved',
                'citizen_id'  => $sarra?->id,
                'reported_at' => now()->subMonths(1),
                'resolved_at' => now()->subDays(20),
            ],
            [
                'title'       => 'Panneau signalisation tombé Hergla-Centre',
                'description' => 'Panneau stop couché sur la chaussée entrée du village côtier.',
                'category_id' => $catMobilier?->id,
                'zone_id'     => $zHerglaC?->id,
                // 'city'        => 'Hergla',
                'address_text'=> 'Route d\'entrée, Hergla',
                'latitude'    => 36.0961,
                'longitude'   => 10.5366,
                'status'      => 'reported',
                'citizen_id'  => $ines?->id,
                'reported_at' => now()->subDays(1),
            ],
        ];

        // ── Pré-calcul du nom de catégorie pour chaque catégorie ─
        $categoryNames = Category::pluck('name', 'id');

        $mediaCount = 0;

        foreach ($incidents as $data) {
            if (!$data['category_id'] || !$data['zone_id'] || !$data['citizen_id']) {
                $this->command->warn("⚠ Incident ignoré (ref. manquante) : {$data['title']}");
                continue;
            }

            // Vérifier si l'incident existe déjà avec un média
            $existing = Incident::where('title', $data['title'])->first();
            if ($existing && !is_null($existing->media_id)) {
                // Incident déjà seedé avec un média → on ne touche à rien
                continue;
            }

            // Créer le média AVANT l'incident (media_id est une FK sur incidents)
            $mediaId      = null;
            $categoryName = $categoryNames[$data['category_id']] ?? null;
            $imageSlug    = $categoryName ? ($categoryImageMap[$categoryName] ?? null) : null;

            if ($imageSlug) {
                $media = $makeMedia($imageSlug);
                if ($media) {
                    $mediaId = $media->id;
                    $mediaCount++;
                }
            }

            // updateOrCreate : met à jour media_id même si l'incident existait sans média
            Incident::updateOrCreate(
                ['title' => $data['title']],
                array_merge($data, [
                    'media_id'   => $mediaId,
                    'created_at' => $data['reported_at'],
                    'updated_at' => $data['resolved_at'] ?? $data['reported_at'],
                ])
            );
        }

        $this->command->info('✅ ' . count($incidents) . ' incidents de référence créés/vérifiés.');
        $this->command->info("🖼  {$mediaCount} média(s) attaché(s) aux incidents.");
    }
}