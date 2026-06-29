<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Municipality;
use App\Models\City;

class MunicipalitySeeder extends Seeder
{
    public function run(): void
    {
        $cityId = fn(string $name): ?int => City::where('name', $name)->value('id');

        $municipalities = [
            // ══════════════════════════════════════════════════════════
            // GOUVERNORAT DE MONASTIR — 13 délégations (pop. 2024 INS)
            // ══════════════════════════════════════════════════════════
            ['name' => 'Municipalité de Monastir',               'city_id' => $cityId('Monastir'),               'number_of_inhabitants' => 110084, 'surface' => 108,  'address' => 'Avenue Habib Bourguiba, Monastir',      'email' => 'contact@mun-monastir.tn',     'phone' => '+216 73 460 000'],
            ['name' => 'Municipalité de Jemmal',                 'city_id' => $cityId('Jemmal'),                 'number_of_inhabitants' => 71158,  'surface' => 68,   'address' => 'Avenue principale, Jemmal',             'email' => 'contact@mun-jemmal.tn',       'phone' => '+216 73 480 000'],
            ['name' => 'Municipalité de Moknine',                'city_id' => $cityId('Moknine'),                'number_of_inhabitants' => 97655,  'surface' => 103,  'address' => 'Avenue Habib Bourguiba, Moknine',       'email' => 'contact@mun-moknine.tn',      'phone' => '+216 73 490 000'],
            ['name' => 'Municipalité de Ksar Hellal',            'city_id' => $cityId('Ksar Hellal'),            'number_of_inhabitants' => 55505,  'surface' => 32,   'address' => 'Avenue de la République, Ksar Hellal',  'email' => 'contact@mun-ksarhellal.tn',   'phone' => '+216 73 475 000'],
            ['name' => 'Municipalité de Ksibet el-Médiouni',     'city_id' => $cityId('Ksibet el-Médiouni'),     'number_of_inhabitants' => 41075,  'surface' => 38,   'address' => 'Route côtière, Ksibet el-Médiouni',     'email' => 'contact@mun-ksibet.tn',       'phone' => '+216 73 455 000'],
            ['name' => 'Municipalité de Bembla',                 'city_id' => $cityId('Bembla'),                 'number_of_inhabitants' => 36076,  'surface' => 34,   'address' => 'Centre-ville, Bembla',                  'email' => 'contact@mun-bembla.tn',       'phone' => '+216 73 450 000'],
            ['name' => 'Municipalité de Téboulba',               'city_id' => $cityId('Téboulba'),               'number_of_inhabitants' => 41049,  'surface' => 22,   'address' => 'Avenue de la Mer, Téboulba',            'email' => 'contact@mun-teboulba.tn',     'phone' => '+216 73 495 000'],
            ['name' => 'Municipalité de Zéramdine',              'city_id' => $cityId('Zéramdine'),              'number_of_inhabitants' => 30732,  'surface' => 150,  'address' => 'Route nationale, Zéramdine',            'email' => 'contact@mun-zeramdine.tn',    'phone' => '+216 73 485 000'],
            ['name' => 'Municipalité de Sayada-Lamta-Bou Hajar', 'city_id' => $cityId('Sayada-Lamta-Bou Hajar'), 'number_of_inhabitants' => 26939, 'surface' => 27,   'address' => 'Route côtière, Sayada',                 'email' => 'contact@mun-sayada.tn',       'phone' => '+216 73 465 000'],
            ['name' => 'Municipalité de Bekalta',                'city_id' => $cityId('Bekalta'),                'number_of_inhabitants' => 19091,  'surface' => 176,  'address' => 'Centre-ville, Bekalta',                 'email' => 'contact@mun-bekalta.tn',      'phone' => '+216 73 470 000'],
            ['name' => 'Municipalité de Sahline',                'city_id' => $cityId('Sahline'),                'number_of_inhabitants' => 32485,  'surface' => 42,   'address' => 'Route de Monastir, Sahline',            'email' => 'contact@mun-sahline.tn',      'phone' => '+216 73 452 000'],
            ['name' => 'Municipalité de Ouerdanine',             'city_id' => $cityId('Ouerdanine'),             'number_of_inhabitants' => 22690,  'surface' => 112,  'address' => 'Centre-ville, Ouerdanine',              'email' => 'contact@mun-ouerdanine.tn',   'phone' => '+216 73 488 000'],
            ['name' => 'Municipalité de Beni Hassen',            'city_id' => $cityId('Beni Hassen'),            'number_of_inhabitants' => 15230,  'surface' => 64,   'address' => 'Centre-ville, Beni Hassen',             'email' => 'contact@mun-benihassen.tn',   'phone' => '+216 73 453 000'],

            // ══════════════════════════════════════════════════════════
            // GOUVERNORAT DE SOUSSE — 16 délégations (pop. 2024 INS)
            // ══════════════════════════════════════════════════════════
            ['name' => 'Municipalité de Sousse Médina',          'city_id' => $cityId('Sousse Médina'),          'number_of_inhabitants' => 32220,  'surface' => 8,    'address' => 'Médina de Sousse, près du Ribat',       'email' => 'contact@mun-sousse-medina.tn',  'phone' => '+216 73 225 000'],
            ['name' => 'Municipalité de Sousse Riadh',           'city_id' => $cityId('Sousse Riadh'),           'number_of_inhabitants' => 71464,  'surface' => 12,   'address' => 'Quartier Riadh, Sousse',                'email' => 'contact@mun-sousse-riadh.tn',   'phone' => '+216 73 226 000'],
            ['name' => 'Municipalité de Sousse Jawhara',         'city_id' => $cityId('Sousse Jawhara'),         'number_of_inhabitants' => 97535,  'surface' => 15,   'address' => 'Quartier Jawhara, Sousse',              'email' => 'contact@mun-sousse-jawhara.tn', 'phone' => '+216 73 227 000'],
            ['name' => 'Municipalité de Sousse Sidi Abdelhamid', 'city_id' => $cityId('Sousse Sidi Abdelhamid'), 'number_of_inhabitants' => 57691,  'surface' => 10,   'address' => 'Quartier Sidi Abdelhamid, Sousse',      'email' => 'contact@mun-sousse-sidi.tn',    'phone' => '+216 73 228 000'],
            ['name' => 'Municipalité de Hammam Sousse',          'city_id' => $cityId('Hammam Sousse'),          'number_of_inhabitants' => 48899,  'surface' => 13,   'address' => 'Avenue de la Plage, Hammam Sousse',     'email' => 'contact@mun-hammamsousse.tn',   'phone' => '+216 73 370 000'],
            ['name' => 'Municipalité de Akouda',                 'city_id' => $cityId('Akouda'),                 'number_of_inhabitants' => 39174,  'surface' => 16,   'address' => 'Avenue principale, Akouda',             'email' => 'contact@mun-akouda.tn',         'phone' => '+216 73 360 000'],
            ['name' => 'Municipalité de Kalâa Kebira',           'city_id' => $cityId('Kalâa Kebira'),           'number_of_inhabitants' => 67108,  'surface' => 93,   'address' => 'Avenue Habib Bourguiba, Kalâa Kebira',  'email' => 'contact@mun-kalaakebira.tn',    'phone' => '+216 73 350 000'],
            ['name' => "Municipalité de M'saken",                'city_id' => $cityId("M'saken"),                'number_of_inhabitants' => 97312,  'surface' => 242,  'address' => "Avenue Habib Bourguiba, M'saken",       'email' => 'contact@mun-msaken.tn',         'phone' => '+216 73 290 000'],
            ['name' => 'Municipalité de Kalâa Seghira',          'city_id' => $cityId('Kalâa Seghira'),          'number_of_inhabitants' => 45743,  'surface' => 40,   'address' => 'Centre-ville, Kalâa Seghira',           'email' => 'contact@mun-kalaaseghira.tn',   'phone' => '+216 73 355 000'],
            ['name' => 'Municipalité de Enfida',                 'city_id' => $cityId('Enfida'),                 'number_of_inhabitants' => 58613,  'surface' => 1093, 'address' => 'Avenue principale, Enfida',             'email' => 'contact@mun-enfida.tn',         'phone' => '+216 73 340 000'],
            ['name' => 'Municipalité de Bouficha',               'city_id' => $cityId('Bouficha'),               'number_of_inhabitants' => 36094,  'surface' => 473,  'address' => 'Route nationale, Bouficha',             'email' => 'contact@mun-bouficha.tn',       'phone' => '+216 73 345 000'],
            ['name' => 'Municipalité de Zaouiet Ksibet Thrayet', 'city_id' => $cityId('Zaouiet Ksibet Thrayet'), 'number_of_inhabitants' => 49130,  'surface' => 35,   'address' => 'Centre-ville, Zaouiet Ksibet Thrayet',  'email' => 'contact@mun-zaouiet.tn',        'phone' => '+216 73 295 000'],
            ['name' => 'Municipalité de Sidi Bou Ali',           'city_id' => $cityId('Sidi Bou Ali'),           'number_of_inhabitants' => 20647,  'surface' => 340,  'address' => 'Centre-ville, Sidi Bou Ali',            'email' => 'contact@mun-sidibouali.tn',     'phone' => '+216 73 365 000'],
            ['name' => 'Municipalité de Kondar',                 'city_id' => $cityId('Kondar'),                 'number_of_inhabitants' => 15491,  'surface' => 85,   'address' => 'Centre-ville, Kondar',                  'email' => 'contact@mun-kondar.tn',         'phone' => '+216 73 297 000'],
            ['name' => 'Municipalité de Sidi El Hani',           'city_id' => $cityId('Sidi El Hani'),           'number_of_inhabitants' => 14508,  'surface' => 568,  'address' => 'Centre-ville, Sidi El Hani',            'email' => 'contact@mun-sidielhani.tn',     'phone' => '+216 73 330 000'],
            ['name' => 'Municipalité de Hergla',                 'city_id' => $cityId('Hergla'),                 'number_of_inhabitants' => 10652,  'surface' => 42,   'address' => 'Village côtier, Hergla',                'email' => 'contact@mun-hergla.tn',         'phone' => '+216 73 375 000'],
        ];

        foreach ($municipalities as $m) {
            Municipality::firstOrCreate(['name' => $m['name']], $m);
        }
    }
}