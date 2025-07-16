<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Book;

class BooksTableSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

        $books = [];
        $usedTitles = [];
        $usedIsbns = [];

        while (count($books) < 1700) {
            $title = $this->generateBookTitle($faker);
            $isbn = $faker->isbn13;

            // Skip if title or isbn already used
            if (isset($usedTitles[$title]) || isset($usedIsbns[$isbn])) {
                continue;
            }

            $usedTitles[$title] = true;
            $usedIsbns[$isbn] = true;

            $books[] = [
                'book_id' => (string) \Str::ulid(),
                'title' => $title,
                'isbn' => $isbn,
                'publisher' => $faker->company,
                'year_publised' => (string) $faker->year,
                'stock' => $faker->numberBetween(1, 100),
            ];
        }

        // Insert in chunks to avoid memory issues
        foreach (array_chunk($books, 100) as $chunk) {
            \App\Models\Book::insert($chunk);
        }
    }

    private function generateBookTitle($faker)
    {
        $formats = [
            'The {Adjective} {Noun}',
            '{Adjective} {Noun}',
            '{Noun} of the {Adjective} {Noun}',
            '{Noun} and the {Noun}',
            'The {Adjective} {Noun} in the {Place}',
        ];

        $format = $faker->randomElement($formats);

        return preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($faker) {
            switch ($matches[1]) {
                case 'Noun':
                    return $faker->randomElement(['Dragon', 'King', 'Secret', 'Journey', 'Star', 'Shadow', 'War', 'Light', 'Dream', 'Time']);
                case 'Adjective':
                    return $faker->randomElement(['Lost', 'Forgotten', 'Hidden', 'Silent', 'Golden', 'Dark', 'Eternal', 'Ancient', 'Mysterious', 'Final']);
                case 'Place':
                    return $faker->randomElement(['Forest', 'Mountain', 'Ocean', 'Desert', 'Sky', 'River', 'Kingdom', 'City', 'Garden', 'Cavern']);
                default:
                    return $matches[0];
            }
        }, $format);
    }
}