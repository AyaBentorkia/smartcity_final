<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('title')->nullable();   
            $table->text('description')->nullable();   
            $table->float('latitude')->nullable();   
            $table->float('longitude')->nullable();   
            $table->string('address_text')->nullable();   
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('medias')->nullOnDelete();
            $table->string('status');
            // $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('citizen_id')->constrained('users')->onDelete('cascade');
            // $table->float('priority_score')->nullable();
            // $table->string('urgency_level')->nullable();
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->string('seeder_batch', 50)->nullable()->default(null);

            $table->timestamps();
            $table->index('citizen_id');
            $table->index('id');
    $table->index('zone_id');
    $table->index('category_id');
    $table->index('status');
    $table->index(['latitude', 'longitude']);

                // $table->index('city_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};