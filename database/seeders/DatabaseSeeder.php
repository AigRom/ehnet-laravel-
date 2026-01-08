<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Locations PEAB tulema enne kasutajaid
        $this->call(LocationSeeder::class);

        // 2) Testkasutaja (ainult dev jaoks)
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        //kategooriad
        $this->call(CategorySeeder::class);
    }
}
