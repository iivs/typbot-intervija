<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate existing records from users table to start from scratch.
        User::truncate();
        // Since users are deleted, also delete tokens. They are no longer useful.
        DB::table('personal_access_tokens')->truncate();

        $faker = \Faker\Factory::create();
        // Set one password for all. Use it to log in later.
        $password = bcrypt('password');

        // Create up to 3 users.
        foreach (range(1, 3) as $i) {
            User::create([
                'name' => $faker->name,
                'email' => $faker->safeEmail,
                'password' => $password
            ]);
        }
    }
}
