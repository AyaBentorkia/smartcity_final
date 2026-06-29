<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

/**
 * CategorySeeder — v2.0
 * 7 domaines métiers couvrant tous les incidents urbains
 * y compris les incendies et risques thermiques / gaz.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Incendies et risques thermiques / gaz',
                'description' => 'Incendies de bâtiments, véhicules, fuites de gaz, odeurs suspectes, explosions, fumées.',
                'color'       => '#E74C3C',
            ],
            [
                'name'        => 'Voirie et infrastructures routières',
                'description' => 'Nids-de-poule, chaussées dégradées, trottoirs cassés, routes inondées, accidents.',
                'color'       => '#FF5733',
            ],
            [
                'name'        => 'Électricité et éclairage public',
                'description' => 'Pannes de lampadaires, câbles exposés, courts-circuits, armoires électriques ouvertes.',
                'color'       => '#FFC300',
            ],
            [
                'name'        => 'Eau et assainissement',
                'description' => 'Fuites d\'eau, canalisations cassées, inondations, égouts bouchés ou débordés.',
                'color'       => '#2E86C1',
            ],
            [
                'name'        => 'Espaces verts et environnement',
                'description' => 'Arbres dangereux, pelouses non entretenues, aires de jeux endommagées, irrigation défectueuse.',
                'color'       => '#1E8449',
            ],
            [
                'name'        => 'Déchets et propreté urbaine',
                'description' => 'Collecte non effectuée, dépôts sauvages, bennes pleines, débris sur voie publique.',
                'color'       => '#28B463',
            ],
            [
                'name'        => 'Mobilier urbain',
                'description' => 'Bancs cassés, abris bus endommagés, barrières, panneaux de signalisation défectueux.',
                'color'       => '#8E44AD',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }

        $this->command->info('✅ ' . count($categories) . ' catégories créées/vérifiées.');
    }
}