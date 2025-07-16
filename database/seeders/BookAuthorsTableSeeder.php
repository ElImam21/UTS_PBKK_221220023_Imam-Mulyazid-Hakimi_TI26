<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookAuthor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookAuthorsTableSeeder extends Seeder
{
    public function run()
    {
        // Ambil semua buku dan penulis
        $books = Book::all();
        $authors = Author::all();

        // Pastikan ada data buku dan penulis
        if ($books->isEmpty() || $authors->isEmpty()) {
            $this->command->error('Seeder dibatalkan: Tidak ada data buku atau penulis');
            return;
        }

        $bookAuthors = [];
        $usedPairs = [];

        // Untuk setiap buku, tambahkan 1-3 penulis secara acak
        foreach ($books as $book) {
            $authorCount = rand(1, 3);
            $selectedAuthors = $authors->random($authorCount);
            
            foreach ($selectedAuthors as $author) {
                $pair = $book->book_id . '-' . $author->author_id;
                
                // Pastikan pasangan unik
                if (!isset($usedPairs[$pair])) {
                    $usedPairs[$pair] = true;
                    
                    $bookAuthors[] = [
                        'id' => (string) Str::ulid(),
                        'book_id' => $book->book_id,
                        'author_id' => $author->author_id,
                    ];
                }
            }
        }

        // Insert dalam chunk
        foreach (array_chunk($bookAuthors, 500) as $chunk) {
            BookAuthor::insert($chunk);
        }
        
        $this->command->info('Berhasil menghubungkan ' . count($bookAuthors) . ' buku dengan penulis');
    }
}