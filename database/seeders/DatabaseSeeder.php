<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->cleanPublicUploads();

        $this->call([
            LocationSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            ListingSeeder::class,
        ]);
    }

    private function cleanPublicUploads(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        foreach (['avatars', 'listings', 'messages'] as $directory) {
            $path = public_path($directory);

            if (File::exists($path)) {
                File::deleteDirectory($path);
            }

            File::makeDirectory($path, 0755, true, true);
        }
    }
}