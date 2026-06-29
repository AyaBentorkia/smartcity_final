<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixMediaUrls extends Command
{
    protected $signature = 'media:fix-urls';
    protected $description = 'Fix old media URLs to use the new API route format';

    public function handle()
    {
        $this->info('Démarrage de la correction des URLs médias...');

        $externalUrl = env('APP_EXTERNAL_URL', env('APP_URL', 'http://localhost'));
        $baseUrl = rtrim($externalUrl, '/');

        // Trouver et corriger les URLs avec le vieux format
        // De: http://localhost:8000/storage/incidents/uuid.jpg
        // À: http://192.168.1.6/api/media/incidents/uuid.jpg
        
        $updated = DB::table('media')
            ->where('url', 'like', '%/storage/%')
            ->update([
                'url' => DB::raw(
                    "REGEXP_REPLACE(
                        url, 
                        'https?://[^/]+/storage/([^/]+)/([^/]+\\.\\w+)$',
                        '" . $baseUrl . "/api/media/$1/$2'
                    )"
                )
            ]);

        if ($updated > 0) {
            $this->info("✅ {$updated} URL(s) corrigée(s) avec succès!");
        } else {
            $this->info('✅ Aucune URL à corriger ou déjà au bon format.');
        }

        // Afficher quelques exemples
        $examples = DB::table('media')->limit(3)->get();
        if ($examples->isNotEmpty()) {
            $this->line("\n📋 Exemples d'URLs après correction:");
            foreach ($examples as $media) {
                $this->line("  • {$media->url}");
            }
        }
    }
}
