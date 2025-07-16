<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->ulid('book_id')->primary(); // ULID sebagai primary key
            $table->string('title')->unique();
            $table->string('isbn')->unique();
            $table->string('publisher')->nullable(false);
            $table->string('year_publised')->nullable(false);
            $table->integer('stock')->nullable(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
