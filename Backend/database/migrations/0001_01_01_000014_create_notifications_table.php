<?php
// database/migrations/create_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // admin destinataire
    $table->foreignId('incident_id')->nullable()->constrained('incidents')->nullOnDelete();
    $table->foreignId('assignment_id')->nullable()->constrained('assignments')->nullOnDelete();
    $table->string('title');
    $table->text('body')->nullable();
    $table->string('type')->default('incident_created'); // extensible plus tard
    $table->timestamp('read_at')->nullable(); // null = non lu
    $table->timestamps();

    $table->index('user_id');
    $table->index('read_at');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};