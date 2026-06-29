<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['super admin', 'municipal admin', 'agent', 'citizen'];

        foreach ($roles as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
}