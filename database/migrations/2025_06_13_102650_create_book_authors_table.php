<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('book_authors', function (Blueprint $table) {
            $table->ulid('id')->primary(); // Kolom id bertipe ULID dan sebagai primary key
            $table->ulid('book_id'); // foreign key dari books.book_id
            $table->ulid('author_id'); // foreign key dari authors.author_id

            // Foreign Key Constraints
            $table->foreign('book_id')->references('book_id')->on('books')->onDelete('cascade');
            $table->foreign('author_id')->references('author_id')->on('authors')->onDelete('cascade');

            $table->unique(['book_id', 'author_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_authors');
    }
};
