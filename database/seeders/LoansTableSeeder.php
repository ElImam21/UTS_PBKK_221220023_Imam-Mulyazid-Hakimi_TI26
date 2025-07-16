<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Loan;
use App\Models\Book;
use App\Models\User;

class LoansTableSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

        // Ambil semua ID buku dan user
        $books = \App\Models\Book::pluck('book_id')->toArray();
        $users = \App\Models\User::pluck('user_id')->toArray();

        $usedCombos = [];

        $loans = [];

        for ($i = 0; $i < 500; $i++) {
            $bookId = $faker->randomElement($books);
            $userId = $faker->randomElement($users);

            // Gunakan tanggal pengembalian acak dalam 1 tahun ke depan
            $returnDate = $faker->dateTimeBetween('now', '+1 year')->format('Y-m-d');

            // Mencegah peminjaman ganda buku yg sama oleh user yg sama di tanggal sama (opsional)
            $comboKey = $userId . '_' . $bookId . '_' . $returnDate;
            if (isset($usedCombos[$comboKey])) {
                continue;
            }
            $usedCombos[$comboKey] = true;

            $loans[] = [
                'loans_id' => (string) Str::ulid(),
                'user_id' => $userId,
                'book_id' => $bookId,
                'return_date' => $returnDate,
            ];
        }

        // Insert data dalam chunk agar efisien
        foreach (array_chunk($loans, 100) as $chunk) {
            Loan::insert($chunk);
        }
    }
}