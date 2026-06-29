<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
     public function run(): void
    {
        $this->call([
            CategorySeeder::class,
             CountrySeeder::class,
    GovernorateSeeder::class,
    CitySeeder::class,
    MunicipalitySeeder::class,
            UserSeeder::class,
            ZoneSeeder::class,
            IncidentSeeder::class,
            // MassIncidentSeeder::class,
            AssignmentSeeder::class,
        ]);
    }
}
