<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Municipality;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder — v2.0
 *
 * Génère de façon programmatique :
 * - 1  super-admin
 * - 28 admins municipaux (13 Monastir + 15 Sousse + 1 Hergla)
 * - 2  agents par catégorie par municipalité = 28 × 7 × 2 = 392 agents
 * - 20 citoyens témoins
 *
 * Convention email agents :
 *   agent.{cat_slug}.{N}.{muni_slug}@baladia.tn
 */
class UserSeeder extends Seeder
{
    // ── Slugs utilisés dans les emails ────────────────────────────
    private const CAT_SLUGS = [
        'Incendies et risques thermiques / gaz' => 'incendie',
        'Voirie et infrastructures routières'    => 'voirie',
        'Électricité et éclairage public'        => 'eclairage',
        'Eau et assainissement'                  => 'eau',
        'Espaces verts et environnement'         => 'espaces',
        'Déchets et propreté urbaine'            => 'dechets',
        'Mobilier urbain'                        => 'mobilier',
    ];

    private const MUNI_SLUGS = [
        'Monastir'                    => 'monastir',
        'Jemmal'                      => 'jemmal',
        'Moknine'                     => 'moknine',
        'Ksar Hellal'                 => 'ksarhellal',
        'Ksibet el-Médiouni'          => 'ksibet',
        'Bembla'                      => 'bembla',
        'Téboulba'                    => 'teboulba',
        'Zéramdine'                   => 'zeramdine',
        'Sayada-Lamta-Bou Hajar'      => 'sayada',
        'Bekalta'                     => 'bekalta',
        'Sahline'                     => 'sahline',
        'Ouerdanine'                  => 'ouerdanine',
        'Beni Hassen'                 => 'benihassen',
        'Sousse Médina'               => 'sousse.medina',
        'Sousse Riadh'                => 'sousse.riadh',
        'Sousse Jawhara'              => 'sousse.jawhara',
        'Sousse Sidi Abdelhamid'      => 'sousse.sidi',
        'Hammam Sousse'               => 'hammam',
        'Akouda'                      => 'akouda',
        'Kalâa Kebira'                => 'kalaakebira',
        "M'saken"                     => 'msaken',
        'Kalâa Seghira'               => 'kalaaseghira',
        'Enfida'                      => 'enfida',
        'Bouficha'                    => 'bouficha',
        'Zaouiet Ksibet Thrayet'      => 'zaouiet',
        'Sidi Bou Ali'                => 'sidibouali',
        'Kondar'                      => 'kondar',
        'Sidi El Hani'                => 'sidielhani',
        'Hergla'                      => 'hergla',
    ];

    public function run(): void
    {
        // ── Super Admin ───────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'superadmin@baladia.tn'],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('password'),
                'phone'             => '+216 20 000 000',
                'address'           => 'Avenue Bourguiba, Tunis',
                'city'              => 'Tunis',
                'role'              => 'super admin',
                'status'            => 'active',
                'birthdate'         => '1980-01-01',
                'cin'               => '00000001',
                'email_verified_at' => now(),
            ]
        );

        // ── Admins municipaux ─────────────────────────────────────
        $adminDefs = [
            // Monastir
            ['city'=>'Monastir',                'email'=>'bentorkiaya7@gmail.com',            'cin'=>'11000001','birth'=>'1982-03-15','phone'=>'+216 20 101 001'],
            ['city'=>'Jemmal',                  'email'=>'admin.jemmal@baladia.tn',              'cin'=>'11000002','birth'=>'1984-07-22','phone'=>'+216 20 101 002'],
            ['city'=>'Moknine',                 'email'=>'admin.moknine@baladia.tn',             'cin'=>'11000003','birth'=>'1983-11-05','phone'=>'+216 20 101 003'],
            ['city'=>'Ksar Hellal',             'email'=>'admin.ksarhellal@baladia.tn',          'cin'=>'11000004','birth'=>'1986-06-20','phone'=>'+216 20 101 004'],
            ['city'=>'Ksibet el-Médiouni',      'email'=>'admin.ksibet@baladia.tn',              'cin'=>'11000005','birth'=>'1985-02-14','phone'=>'+216 20 101 005'],
            ['city'=>'Bembla',                  'email'=>'admin.bembla@baladia.tn',              'cin'=>'11000006','birth'=>'1987-09-18','phone'=>'+216 20 101 006'],
            ['city'=>'Téboulba',                'email'=>'admin.teboulba@baladia.tn',            'cin'=>'11000007','birth'=>'1988-05-30','phone'=>'+216 20 101 007'],
            ['city'=>'Zéramdine',               'email'=>'admin.zeramdine@baladia.tn',           'cin'=>'11000008','birth'=>'1981-12-10','phone'=>'+216 20 101 008'],
            ['city'=>'Sayada-Lamta-Bou Hajar',  'email'=>'admin.sayada@baladia.tn',              'cin'=>'11000009','birth'=>'1989-04-25','phone'=>'+216 20 101 009'],
            ['city'=>'Bekalta',                 'email'=>'admin.bekalta@baladia.tn',             'cin'=>'11000010','birth'=>'1990-08-07','phone'=>'+216 20 101 010'],
            ['city'=>'Sahline',                 'email'=>'admin.sahline@baladia.tn',             'cin'=>'11000011','birth'=>'1983-01-19','phone'=>'+216 20 101 011'],
            ['city'=>'Ouerdanine',              'email'=>'admin.ouerdanine@baladia.tn',          'cin'=>'11000012','birth'=>'1986-03-22','phone'=>'+216 20 101 012'],
            ['city'=>'Beni Hassen',             'email'=>'admin.benihassen@baladia.tn',          'cin'=>'11000013','birth'=>'1984-10-14','phone'=>'+216 20 101 013'],
            // Sousse
            ['city'=>'Sousse Médina',           'email'=>'admin.sousse.medina@baladia.tn',       'cin'=>'12000001','birth'=>'1982-05-11','phone'=>'+216 20 102 001'],
            ['city'=>'Sousse Riadh',            'email'=>'admin.sousse.riadh@baladia.tn',        'cin'=>'12000002','birth'=>'1985-09-03','phone'=>'+216 20 102 002'],
            ['city'=>'Sousse Jawhara',          'email'=>'admin.sousse.jawhara@baladia.tn',      'cin'=>'12000003','birth'=>'1983-12-27','phone'=>'+216 20 102 003'],
            ['city'=>'Sousse Sidi Abdelhamid',  'email'=>'admin.sousse.sidi@baladia.tn',         'cin'=>'12000004','birth'=>'1987-07-15','phone'=>'+216 20 102 004'],
            ['city'=>'Hammam Sousse',           'email'=>'admin.hammamsousse@baladia.tn',        'cin'=>'12000005','birth'=>'1984-02-28','phone'=>'+216 20 102 005'],
            ['city'=>'Akouda',                  'email'=>'admin.akouda@baladia.tn',              'cin'=>'12000006','birth'=>'1988-11-09','phone'=>'+216 20 102 006'],
            ['city'=>'Kalâa Kebira',            'email'=>'admin.kalaakebira@baladia.tn',         'cin'=>'12000007','birth'=>'1981-06-17','phone'=>'+216 20 102 007'],
            ["city"=>"M'saken",                 'email'=>'admin.msaken@baladia.tn',              'cin'=>'12000008','birth'=>'1986-04-06','phone'=>'+216 20 102 008'],
            ['city'=>'Kalâa Seghira',           'email'=>'admin.kalaaseghira@baladia.tn',        'cin'=>'12000009','birth'=>'1989-08-23','phone'=>'+216 20 102 009'],
            ['city'=>'Enfida',                  'email'=>'admin.enfida@baladia.tn',              'cin'=>'12000010','birth'=>'1982-01-31','phone'=>'+216 20 102 010'],
            ['city'=>'Bouficha',                'email'=>'admin.bouficha@baladia.tn',            'cin'=>'12000011','birth'=>'1985-05-20','phone'=>'+216 20 102 011'],
            ['city'=>'Zaouiet Ksibet Thrayet',  'email'=>'admin.zaouiet@baladia.tn',             'cin'=>'12000012','birth'=>'1987-03-12','phone'=>'+216 20 102 012'],
            ['city'=>'Sidi Bou Ali',            'email'=>'admin.sidibouali@baladia.tn',          'cin'=>'12000013','birth'=>'1983-10-08','phone'=>'+216 20 102 013'],
            ['city'=>'Kondar',                  'email'=>'admin.kondar@baladia.tn',              'cin'=>'12000014','birth'=>'1990-07-04','phone'=>'+216 20 102 014'],
            ['city'=>'Sidi El Hani',            'email'=>'admin.sidielhani@baladia.tn',          'cin'=>'12000015','birth'=>'1984-12-15','phone'=>'+216 20 102 015'],
            ['city'=>'Hergla',                  'email'=>'admin.hergla@baladia.tn',              'cin'=>'12000016','birth'=>'1986-09-29','phone'=>'+216 20 102 016'],
        ];

        foreach ($adminDefs as $a) {
            $mun = Municipality::whereHas('city', fn($q) => $q->where('name', $a['city']))->first();
            User::firstOrCreate(
                ['email' => $a['email']],
                [
                    'name'              => 'Admin ' . $a['city'],
                    'password'          => Hash::make('password'),
                    'phone'             => $a['phone'],
                    'address'           => 'Mairie de ' . $a['city'],
                    'city'              => $a['city'],
                    'role'              => 'municipal admin',
                    'municipality_id'   => $mun?->id,
                    'status'            => 'active',
                    'birthdate'         => $a['birth'],
                    'cin'               => $a['cin'],
                    'email_verified_at' => now(),
                ]
            );
        }

        // ── Agents (2 par catégorie par municipalité) ─────────────
        $this->generateAgents();

        // ── Citoyens ──────────────────────────────────────────────
        $citizens = [
            ['name'=>'Ahmed Ben Ali',    'email'=>'ahmed.benali@gmail.com',    'phone'=>'+216 55 100 001','address'=>'Rue Ibn Khaldoun, Monastir',    'city'=>'Monastir',              'birth'=>'1990-07-04','cin'=>'30000001'],
            ['name'=>'Fatma Trabelsi',   'email'=>'fatma.trabelsi@gmail.com',  'phone'=>'+216 55 100 002','address'=>'Corniche, Monastir',            'city'=>'Monastir',              'birth'=>'1995-12-22','cin'=>'30000002'],
            ['name'=>'Karim Saidi',      'email'=>'karim.saidi@gmail.com',     'phone'=>'+216 55 100 003','address'=>'Médina de Sousse',              'city'=>'Sousse Médina',         'birth'=>'1988-04-16','cin'=>'30000003'],
            ['name'=>'Nour Jelassi',     'email'=>'nour.jelassi@gmail.com',    'phone'=>'+216 55 100 004','address'=>'Corniche, Hammam Sousse',       'city'=>'Hammam Sousse',         'birth'=>'1997-03-11','cin'=>'30000004'],
            ['name'=>'Youssef Khemiri',  'email'=>'youssef.khemiri@gmail.com', 'phone'=>'+216 55 100 005','address'=>'Avenue Bourguiba, Moknine',     'city'=>'Moknine',               'birth'=>'1985-08-30','cin'=>'30000005'],
            ['name'=>'Sarra Hamdi',      'email'=>'sarra.hamdi@gmail.com',     'phone'=>'+216 55 100 006','address'=>'Zone touristique, Akouda',      'city'=>'Akouda',                'birth'=>'1993-06-05','cin'=>'30000006'],
            ['name'=>'Mohamed Cherif',   'email'=>'mohamed.cherif@gmail.com',  'phone'=>'+216 55 100 007','address'=>'Ksar Hellal Centre',            'city'=>'Ksar Hellal',           'birth'=>'1987-11-14','cin'=>'30000007'],
            ['name'=>'Amira Bouzid',     'email'=>'amira.bouzid@gmail.com',    'phone'=>'+216 55 100 008','address'=>"Rue de la Liberté, M'saken",   "city"=>"M'saken",               'birth'=>'1992-02-28','cin'=>'30000008'],
            ['name'=>'Rami Gharbi',      'email'=>'rami.gharbi@gmail.com',     'phone'=>'+216 55 100 009','address'=>'Téboulba Port',                 'city'=>'Téboulba',              'birth'=>'1994-09-19','cin'=>'30000009'],
            ['name'=>'Ines Mansouri',    'email'=>'ines.mansouri@gmail.com',   'phone'=>'+216 55 100 010','address'=>'Avenue principale, Enfida',     'city'=>'Enfida',                'birth'=>'1996-05-07','cin'=>'30000010'],
            ['name'=>'Bilel Ayari',      'email'=>'bilel.ayari@gmail.com',     'phone'=>'+216 55 100 011','address'=>'Rue Habib Thameur, Jemmal',     'city'=>'Jemmal',                'birth'=>'1991-03-23','cin'=>'30000011'],
            ['name'=>'Olfa Ben Salah',   'email'=>'olfa.bensalah@gmail.com',   'phone'=>'+216 55 100 012','address'=>'Résidence les Jasmins, Sousse', 'city'=>'Sousse Riadh',          'birth'=>'1998-08-15','cin'=>'30000012'],
            ['name'=>'Walid Dridi',      'email'=>'walid.dridi@gmail.com',     'phone'=>'+216 55 100 013','address'=>'Quartier Jawhara, Sousse',      'city'=>'Sousse Jawhara',        'birth'=>'1986-11-29','cin'=>'30000013'],
            ['name'=>'Marwa Chaabane',   'email'=>'marwa.chaabane@gmail.com',  'phone'=>'+216 55 100 014','address'=>'Zone côtière, Hergla',          'city'=>'Hergla',                'birth'=>'1994-01-08','cin'=>'30000014'],
            ['name'=>'Hedi Jomaa',       'email'=>'hedi.jomaa@gmail.com',      'phone'=>'+216 55 100 015','address'=>'Cité Ettadhamen, Msaken',       "city"=>"M'saken",               'birth'=>'1989-06-12','cin'=>'30000015'],
            ['name'=>'Amel Khiari',      'email'=>'amel.khiari@gmail.com',     'phone'=>'+216 55 100 016','address'=>'Rue des Orangers, Kalâa Kebira','city'=>'Kalâa Kebira',          'birth'=>'1996-10-31','cin'=>'30000016'],
            ['name'=>'Tarek Ben Youssef','email'=>'tarek.benyoussef@gmail.com','phone'=>'+216 55 100 017','address'=>'Boulevard de la mer, Monastir', 'city'=>'Monastir',              'birth'=>'1983-05-17','cin'=>'30000017'],
            ['name'=>'Sonia Zouari',     'email'=>'sonia.zouari@gmail.com',    'phone'=>'+216 55 100 018','address'=>'Lotissement El Wifek, Bouficha', 'city'=>'Bouficha',             'birth'=>'1992-09-04','cin'=>'30000018'],
            ['name'=>'Fares Mbarki',     'email'=>'fares.mbarki@gmail.com',    'phone'=>'+216 55 100 019','address'=>'Avenue Hedi Nouira, Ksar Hellal','city'=>'Ksar Hellal',          'birth'=>'1987-07-20','cin'=>'30000019'],
            ['name'=>'Rim Ben Amor',     'email'=>'rim.benamor@gmail.com',     'phone'=>'+216 55 100 020','address'=>'Corniche, Hammam Sousse',       'city'=>'Hammam Sousse',         'birth'=>'1995-02-14','cin'=>'30000020'],
        ];

        foreach ($citizens as $c) {
            User::firstOrCreate(
                ['email' => $c['email']],
                [
                    'name'              => $c['name'],
                    'password'          => Hash::make('password'),
                    'phone'             => $c['phone'],
                    'address'           => $c['address'],
                    'city'              => $c['city'],
                    'role'              => 'citizen',
                    'status'            => 'active',
                    'birthdate'         => $c['birth'],
                    'cin'               => $c['cin'],
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command->info('✅ Users créés : 1 super-admin, ' . count($adminDefs) . ' admins, 392 agents, ' . count($citizens) . ' citoyens.');
    }

    // ── Génération programmatique des agents ──────────────────────
    private function generateAgents(): void
    {
        $cinBase   = 21000000;
        $phoneBase = 2030000000;
        $counter   = 0;

        $categories = Category::all()->keyBy('name');
        $municipalities = Municipality::all();

        $birthDates = [
            '1990-03-12', '1991-07-18', '1992-11-05', '1993-02-27',
            '1994-08-14', '1995-04-09', '1989-12-01', '1988-06-23',
            '1993-09-16', '1991-01-30', '1992-05-07', '1990-10-22',
            '1994-03-19', '1989-08-11',
        ];

        foreach ($municipalities as $mun) {
            $cityName  = $mun->city?->name ?? $mun->name;
            $muniSlug  = self::MUNI_SLUGS[$cityName] ?? \Str::slug($cityName);

            foreach (self::CAT_SLUGS as $catName => $catSlug) {
                $cat = $categories->get($catName);
                if (!$cat) continue;

                // 2 agents par catégorie par municipalité
                for ($n = 1; $n <= 2; $n++) {
                    $counter++;
                    $cin   = (string)($cinBase + $counter);
                    $phone = '+216 ' . substr((string)($phoneBase + $counter), 0, 2)
                            . ' ' . substr((string)($phoneBase + $counter), 2, 3)
                            . ' ' . substr((string)($phoneBase + $counter), 5, 3);

                    $email = "agent.{$catSlug}.{$n}.{$muniSlug}@baladia.tn";
                    $catLabel = $this->catLabel($catSlug);

                    User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name'              => "Agent {$catLabel} {$n} — {$cityName}",
                            'password'          => Hash::make('password'),
                            'phone'             => "+216 2{$counter}",
                            'address'           => "Mairie de {$cityName}",
                            'city'              => $cityName,
                            'role'              => 'agent',
                            'municipality_id'   => $mun->id,
                            'category_id'       => $cat->id,
                            'status'            => 'active',
                            'birthdate'         => $birthDates[($counter - 1) % count($birthDates)],
                            'cin'               => $cin,
                            'email_verified_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    private function catLabel(string $slug): string
    {
        return match($slug) {
            'incendie'  => 'Incendie',
            'voirie'    => 'Voirie',
            'eclairage' => 'Éclairage',
            'eau'       => 'Eau',
            'espaces'   => 'Espaces Verts',
            'dechets'   => 'Déchets',
            'mobilier'  => 'Mobilier',
            default     => ucfirst($slug),
        };
    }
}