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

        // Create a few users.
        User::factory()
            ->count(3)
            ->create();
    }
}
