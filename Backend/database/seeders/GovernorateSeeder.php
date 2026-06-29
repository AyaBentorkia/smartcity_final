<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Governorate;
use App\Models\Country;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        $countryId = fn(string $code): ?int => Country::where('code', $code)->value('id');

        $governorates = [
            ['name' => 'Monastir', 'country_id' => $countryId('TN')],
            ['name' => 'Sousse',   'country_id' => $countryId('TN')],
        ];

        foreach ($governorates as $governorate) {
            Governorate::firstOrCreate(['name' => $governorate['name']], $governorate);
        }
    }
}