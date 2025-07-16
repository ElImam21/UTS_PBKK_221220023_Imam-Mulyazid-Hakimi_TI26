<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersDetailTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users_detail', function (Blueprint $table) {
            $table->id(); // opsional, bisa dihapus jika tidak perlu
            $table->ulid('user_id')->unique();
            $table->text('address')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('user_id') // pastikan kolom di tabel users bernama `user_id`
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_detail');
    }
}
