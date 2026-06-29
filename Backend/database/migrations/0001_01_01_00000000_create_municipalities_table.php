<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            // 'governorate' et 'country' supprimés — accessibles via city.governorate.country
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->string('address')->nullable();
            $table->integer('surface')->nullable();
            $table->integer('number_of_inhabitants')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable()->unique();
            $table->timestamps();

            $table->index(['name', 'city_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('municipalities');
    }
};