<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Author;

class AuthorsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();
        $faker->seed(123); // Untuk hasil konsisten
        
        $authors = [];
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        
        $nationalities = ['Indonesian', 'American', 'British', 'Japanese', 'French', 
                         'German', 'Russian', 'Chinese', 'Indian', 'Brazilian'];
        
        for ($i = 0; $i < 600; $i++) {
            $birthYear = $faker->numberBetween(1800, 2000);
            $birthMonth = $faker->numberBetween(1, 12);
            $birthDay = $faker->numberBetween(1, 28);
            
            $authors[] = [
                'author_id' => (string) Str::ulid(),
                'name' => $faker->name,
                'nationality' => $faker->randomElement($nationalities),
                'birthdate' => $birthDay . '-' . $months[$birthMonth - 1] . '-' . $birthYear,
            ];
        }

        // Insert dalam chunk untuk efisiensi
        foreach (array_chunk($authors, 100) as $chunk) {
            Author::insert($chunk);
        }
    }
}