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
       Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('photo_id')->nullable()->constrained('medias')->nullOnDelete();
            $table->date('birthdate')->nullable();
            $table->string('cin')->nullable()->unique();
            $table->string('role')->default('citizen');
                        $table->string('status')->default('active');
        $table->string('fcm_token')->nullable();

// $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();            
$table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('municipality_id')->nullable()->constrained('municipalities')->nullOnDelete();

            $table->rememberToken();
            $table->timestamps();
            $table->index('municipality_id');
            $table->index('category_id');
            $table->index(['email','password']);
            $table->index('role');
        $table->index('status');
        $table->index(['municipality_id', 'role']); // index composé — le plus utile
        $table->index(['municipality_id', 'category_id', 'role']);
            // $table->index('role_id');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_id');
        });
    }
};
