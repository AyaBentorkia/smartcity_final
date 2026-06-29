<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Governorate;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $govId = fn(string $name): ?int => Governorate::where('name', $name)->value('id');

        $cities = [
            // ══════════════════════════════════════════════════════════
            // GOUVERNORAT DE MONASTIR — 13 délégations
            // ══════════════════════════════════════════════════════════
            ['name' => 'Monastir',                'governorate_id' => $govId('Monastir')],
            ['name' => 'Jemmal',                  'governorate_id' => $govId('Monastir')],
            ['name' => 'Moknine',                 'governorate_id' => $govId('Monastir')],
            ['name' => 'Ksar Hellal',             'governorate_id' => $govId('Monastir')],
            ['name' => 'Ksibet el-Médiouni',      'governorate_id' => $govId('Monastir')],
            ['name' => 'Bembla',                  'governorate_id' => $govId('Monastir')],
            ['name' => 'Téboulba',                'governorate_id' => $govId('Monastir')],
            ['name' => 'Zéramdine',               'governorate_id' => $govId('Monastir')],
            ['name' => 'Sayada-Lamta-Bou Hajar',  'governorate_id' => $govId('Monastir')],
            ['name' => 'Bekalta',                 'governorate_id' => $govId('Monastir')],
            ['name' => 'Sahline',                 'governorate_id' => $govId('Monastir')],
            ['name' => 'Ouerdanine',              'governorate_id' => $govId('Monastir')],
            ['name' => 'Beni Hassen',             'governorate_id' => $govId('Monastir')],

            // ══════════════════════════════════════════════════════════
            // GOUVERNORAT DE SOUSSE — 16 délégations
            // ══════════════════════════════════════════════════════════
            ['name' => 'Sousse Médina',           'governorate_id' => $govId('Sousse')],
            ['name' => 'Sousse Riadh',            'governorate_id' => $govId('Sousse')],
            ['name' => 'Sousse Jawhara',          'governorate_id' => $govId('Sousse')],
            ['name' => 'Sousse Sidi Abdelhamid',  'governorate_id' => $govId('Sousse')],
            ['name' => 'Hammam Sousse',           'governorate_id' => $govId('Sousse')],
            ['name' => 'Akouda',                  'governorate_id' => $govId('Sousse')],
            ['name' => 'Kalâa Kebira',            'governorate_id' => $govId('Sousse')],
            ['name' => "M'saken",                 'governorate_id' => $govId('Sousse')],
            ['name' => 'Kalâa Seghira',           'governorate_id' => $govId('Sousse')],
            ['name' => 'Enfida',                  'governorate_id' => $govId('Sousse')],
            ['name' => 'Bouficha',                'governorate_id' => $govId('Sousse')],
            ['name' => 'Zaouiet Ksibet Thrayet',  'governorate_id' => $govId('Sousse')],
            ['name' => 'Sidi Bou Ali',            'governorate_id' => $govId('Sousse')],
            ['name' => 'Kondar',                  'governorate_id' => $govId('Sousse')],
            ['name' => 'Sidi El Hani',            'governorate_id' => $govId('Sousse')],
            ['name' => 'Hergla',                  'governorate_id' => $govId('Sousse')],
        ];

        foreach ($cities as $city) {
            City::firstOrCreate(['name' => $city['name']], $city);
        }
    }
}