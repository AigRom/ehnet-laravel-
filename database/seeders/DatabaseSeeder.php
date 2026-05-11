<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->cleanListingUploads();

        $this->call([
            LocationSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            ListingSeeder::class,
        ]);
    }

    private function cleanListingUploads(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        foreach ([
            'listings/large',
            'listings/thumb',
        ] as $directory) {
            Storage::disk('public')->deleteDirectory($directory);
            Storage::disk('public')->makeDirectory($directory);
        }
    }
}