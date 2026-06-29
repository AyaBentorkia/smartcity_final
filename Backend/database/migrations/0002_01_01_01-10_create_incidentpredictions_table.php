<?php
// database/migrations/xxxx_create_incident_predictions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_predictions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('triggered_by')->constrained('users');

            $table->string('category');
            $table->date('period');
            $table->unsignedTinyInteger('semaine');       // 1 → 52
            $table->float('probabilite');                 // 0 → 100
            $table->string('risque');                     // Très Faible / Risque Faible / Risque Modéré / Risque Élevé
            $table->json('meteo');                        // temp_moyenne, precipitation, source
            $table->boolean('est_ferie')->default(0);
            $table->text('explication');

            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();

            $table->index(['zone_id', 'created_at']);
            $table->index(['category', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_predictions');
    }
};