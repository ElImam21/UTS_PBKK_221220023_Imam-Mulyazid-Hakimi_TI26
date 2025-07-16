<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Kalau mau reset table sebelum seed, uncomment:
//      User::truncate();

        for ($i = 0; $i < 200; $i++) {
            User::create([
                // sama seperti AuthController: ULID otomatis
                'user_id'         => (string) Str::ulid(),

                // kolom wajib sesuai migration
                'name'            => $faker->name(),
                'email'           => $faker->unique()->safeEmail(),
                'password'        => Hash::make('password'), // semua password = "password"

                // same as register: membership_date di-set saat register
                'membership_date' => Carbon::now()->toDateString(),
            ]);
        }
    }
}
