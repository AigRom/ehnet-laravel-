<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()
            ->whereIn('email', [
                'martin.saar@ehnet.test',
                'katrin.magi@ehnet.test',
                'rasmus.tamm@ehnet.test',
                'info@nordehitus.test',
                'info@rohereno.test',
                'info@kodumaterjalid.test',
            ])
            ->get();

        $categories = Category::query()->get();
        $locations = Location::query()->get();

        if ($users->isEmpty() || $categories->isEmpty() || $locations->isEmpty()) {
            $this->command->warn('ListingSeeder vajab kasutajaid, kategooriaid ja asukohti.');
            return;
        }

        $templates = [
            [
                'title' => 'Üle jäänud puitmaterjal',
                'description' => 'Ehitusest üle jäänud kuiv ja kasutuskõlblik puitmaterjal. Sobib väiksemateks ehitus- või remonditöödeks.',
                'price' => 45,
                'intent' => 'sell',
                'condition' => 'leftover',
            ],
            [
                'title' => 'Kasutatud siseuks koos lengiga',
                'description' => 'Korralik kasutatud siseuks koos lengiga. Esineb väiksemaid kasutusjälgi, kuid sobib taaskasutuseks.',
                'price' => 35,
                'intent' => 'sell',
                'condition' => 'used',
            ],
            [
                'title' => 'Tasuta ära anda ehitusplokid',
                'description' => 'Väiksem kogus ehitusplokke, mis jäid objektist üle. Tule ise järele.',
                'price' => 0,
                'intent' => 'giveaway',
                'condition' => 'leftover',
            ],
            [
                'title' => 'Katusepleki jäägid',
                'description' => 'Erinevas mõõdus katusepleki jäägid. Sobivad kuuri, varjualuse või väiksema projekti jaoks.',
                'price' => 60,
                'intent' => 'sell',
                'condition' => 'leftover',
            ],
            [
                'title' => 'Kasutatud aknad',
                'description' => 'Renoveerimise käigus eemaldatud aknad. Klaasid terved, raamidel kasutusjäljed.',
                'price' => 80,
                'intent' => 'sell',
                'condition' => 'used',
            ],
            [
                'title' => 'Viimistlusmaterjalide jäägid',
                'description' => 'Erinevad viimistlusmaterjalid: liistud, plaadid ja väiksemad tarvikud. Sobib väiksemaks remondiks.',
                'price' => null,
                'intent' => 'sell',
                'condition' => 'leftover',
            ],
        ];

        $counter = 1;

        foreach ($users as $user) {
            for ($i = 0; $i < 3; $i++) {
                $template = $templates[($counter - 1) % count($templates)];

                $listing = Listing::query()->create([
                    'user_id' => $user->id,
                    'category_id' => $categories->random()->id,
                    'location_id' => $locations->random()->id,
                    'title' => $template['title'] . ' #' . $counter,
                    'description' => $template['description'],
                    'price' => $template['price'],
                    'currency' => 'EUR',
                    'listing_type' => 'sale',
                    'status' => 'published',
                    'published_at' => now()->subDays(rand(1, 20)),
                    'expires_at' => now()->addDays(rand(14, 60)),
                    'intent' => $template['intent'],
                    'condition' => $template['condition'],
                    'delivery_options' => ['pickup', 'agreement'],
                    'vat_included' => $user->role === 'business',
                ]);

                $this->createDemoImage($listing, $counter);

                $counter++;
            }
        }
    }

    private function createDemoImage(Listing $listing, int $number): void
    {
        $directory = 'listings/demo/' . $listing->id;

        Storage::disk('public')->makeDirectory($directory);

        $filename = 'demo-' . Str::slug($listing->title) . '.jpg';
        $thumbFilename = 'thumb-' . Str::slug($listing->title) . '.jpg';

        $path = $directory . '/' . $filename;
        $thumbPath = $directory . '/' . $thumbFilename;

        $fullAbsolutePath = Storage::disk('public')->path($path);
        $thumbAbsolutePath = Storage::disk('public')->path($thumbPath);

        $this->generateImage($fullAbsolutePath, 1200, 800, $listing->title, $number);
        $this->generateImage($thumbAbsolutePath, 400, 267, $listing->title, $number);

        ListingImage::query()->create([
            'listing_id' => $listing->id,
            'disk' => 'public',
            'path' => $path,
            'thumb_path' => $thumbPath,
            'mime_type' => 'image/jpeg',
            'file_size' => file_exists($fullAbsolutePath) ? filesize($fullAbsolutePath) : null,
            'width' => 1200,
            'height' => 800,
            'sort_order' => 0,
        ]);
    }

    private function generateImage(string $absolutePath, int $width, int $height, string $title, int $number): void
    {
        $image = imagecreatetruecolor($width, $height);

        $background = imagecolorallocate($image, 236, 244, 239);
        $accent = imagecolorallocate($image, 31, 120, 82);
        $text = imagecolorallocate($image, 35, 35, 35);
        $muted = imagecolorallocate($image, 110, 110, 110);

        imagefilledrectangle($image, 0, 0, $width, $height, $background);
        imagefilledrectangle($image, 0, 0, $width, 90, $accent);

        imagestring($image, 5, 30, 30, 'EHNET demo kuulutus #' . $number, imagecolorallocate($image, 255, 255, 255));
        imagestring($image, 5, 40, 150, mb_substr($title, 0, 45), $text);
        imagestring($image, 4, 40, 210, 'Demo-pilt seederiga loodud', $muted);
        imagestring($image, 4, 40, 250, 'Asenda hiljem päris tootepildiga', $muted);

        imagejpeg($image, $absolutePath, 85);
        imagedestroy($image);
    }
}