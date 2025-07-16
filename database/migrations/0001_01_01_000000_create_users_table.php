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
            // Kolom ULID sebagai primary key
            $table->ulid('user_id')->primary();
            
            $table->string('name', 50)->nullable(false);
            $table->string('email', 50)->unique()->nullable(false);
            $table->string('password')->nullable(false); // default: VARCHAR(255)
            $table->date('membership_date')->nullable(false);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        
        // Hapus perintah drop untuk tabel yang tidak dibuat
        // Schema::dropIfExists('password_reset_tokens');
        // Schema::dropIfExists('sessions');
    }
};