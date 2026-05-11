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
        $categoriesBySlug = $categories->keyBy('slug');

        $locations = Location::query()->get();

        if ($users->isEmpty() || $categories->isEmpty() || $locations->isEmpty()) {
            $this->command->warn('ListingSeeder vajab kasutajaid, kategooriaid ja asukohti.');
            return;
        }

        $listings = [
            [
                'title' => 'Üle jäänud kipsplaadid 12,5 mm',
                'description' => 'Üle jäänud korralikud kipsplaadid. Plaadid on kuivad ja hoiustatud siseruumis. Pikkus 2,6 m. Saadaval ca 40tk. Hind plaadi kohta.',
                'price' => 4.50,
                'intent' => 'sell',
                'condition' => 'leftover',
                'category_slug' => 'ehitusplaadid',
                'image' => 'kipsplaadid.jpg',
            ],
            [
                'title' => 'Fibo 3 plokid',
                'description' => 'Umbes 35 tükki.',
                'price' => 20,
                'intent' => 'sell',
                'condition' => 'new',
                'category_slug' => 'tellised-ja-plokid',
                'image' => 'fibo.jpg',
            ],
            [
                'title' => 'Puitprussid 50x100',
                'description' => 'Ehitusest üle jäänud kuivad puitprussid. Sobivad kuuri, terrassi või väiksema karkassi ehituseks.',
                'price' => 20,
                'intent' => 'sell',
                'condition' => 'leftover',
                'category_slug' => 'puitmaterjalid',
                'image' => 'puit.jpg',
            ],
            [
                'title' => 'Makita akudrellid',
                'description' => 'Makita akudrellide komplekt, 2 akut ja laadija. Töökorras.',
                'price' => 70,
                'intent' => 'sell',
                'condition' => 'used',
                'category_slug' => 'tooriistad',
                'image' => 'makita.jpg',
            ],
            [
                'title' => 'Kasutatud PVC aknad renoveerimisest',
                'description' => 'Renoveerimise käigus eemaldatud PVC aknad. Klaaspaketid on terved, raamidel esineb kasutusjälgi. Sobivad kõrvalhoonele, kasvuhoonele või töökojale.',
                'price' => 50,
                'intent' => 'sell',
                'condition' => 'used',
                'category_slug' => 'aknad-ja-uksed',
                'image' => 'aknad2.jpg',
            ],
            [
                'title' => 'Katuse plekkide jäägid',
                'description' => 'Erinevas toonis ja pikkuses katusepleki jääke.',
                'price' => 40,
                'intent' => 'sell',
                'condition' => 'leftover',
                'category_slug' => 'katusematerjalid',
                'image' => 'katuseplekk.jpg',
            ],
            [
                'title' => 'Kivivill 100mm',
                'description' => 'Ehitusest üle jäänud soojustusvilla pakid. Kivivill 100mm 5 pakki.',
                'price' => 50,
                'intent' => 'sell',
                'condition' => 'new',
                'category_slug' => 'soojustusmaterjalid',
                'image' => 'vill.jpg',
            ],
            [
                'title' => 'Siseuks koos lengidega',
                'description' => 'Korteri renoveerimisest üle jäänud siseuks koos lengidega. Uks on kasutatud, kuid korralik. Vajavad kerget puhastust.',
                'price' => 15,
                'intent' => 'sell',
                'condition' => 'used',
                'category_slug' => 'aknad-ja-uksed',
                'image' => 'uks.jpg',
            ],
            [
                'title' => 'Ruberoid katusekattematerjal',
                'description' => 'Rullis 10m, kokku 10 rulli, ca 100m2.',
                'price' => 200,
                'intent' => 'sell',
                'condition' => 'new',
                'category_slug' => 'katusematerjalid',
                'image' => 'ruberoid.jpg',
            ],
            [
                'title' => 'Keraamilised seinaplaadid vannituppa',
                'description' => 'Üle jäänud vannitoa valged seinaplaadid. ca 20m2. Sobivad väiksema vannitoa plaatimiseks.',
                'price' => null,
                'intent' => 'sell',
                'condition' => 'new',
                'category_slug' => 'viimistlusmaterjalid',
                'image' => 'seinaplaadid.jpg',
            ],
            [
                'title' => 'Villa jäägid',
                'description' => 'Suuremas koguses Isover villa jääke, erinevas mõõdus.',
                'price' => 0,
                'intent' => 'sell',
                'condition' => 'leftover',
                'category_slug' => 'soojustusmaterjalid',
                'image' => 'soojustus.jpg',
            ],
            [
                'title' => 'Kasutatud kivivill',
                'description' => 'Kasutatud kivivill 100mm. Hoitud kuivas. Tule vii ära.',
                'price' => null,
                'intent' => 'sell',
                'condition' => 'leftover',
                'category_slug' => 'soojustusmaterjalid',
                'image' => 'villajaagid.jpg',
            ],
            [
                'title' => 'Suured PVC aknad',
                'description' => 'Kasutatud suured PVC aknad (5 tk), mis sobivad näiteks garaaži või töökoja akendeks. Klaaspaketid terved, raamidel kasutusjälgi.',
                'price' => 75,
                'intent' => 'sell',
                'condition' => 'leftover',
                'category_slug' => 'aknad-ja-uksed',
                'image' => 'aknad.jpg',
            ],
            [
                'title' => 'Kasutatud terrassilauad',
                'description' => 'Demonteeritud terrassilt pärit lauad.',
                'price' => 0,
                'intent' => 'sell',
                'condition' => 'used',
                'category_slug' => 'puitmaterjalid',
                'image' => 'terrassilauad.jpg',
            ],
            [
                'title' => 'Tuuletõkke plaadid',
                'description' => 'Defektsed kuid kasutuskõlblikud tuuletõkke plaadid. 3 eur/tk, saadaval üle saja. Küsi lisa.',
                'price' => 3,
                'intent' => 'sell',
                'condition' => 'used',
                'category_slug' => 'ehitusplaadid',
                'image' => 'tuuletoke.jpg',
            ],

            // Need 5 jäävad meelega ilma pildita.
            [
                'title' => 'Vanad maakivid aia või haljastuse jaoks',
                'description' => 'Ära anda maakivid, mis jäid krundi korrastamisest üle. Sobivad aiaääriseks, haljastuseks või dekoratiivseks kasutuseks.',
                'price' => 0,
                'intent' => 'giveaway',
                'condition' => 'used',
                'category_slug' => 'muu',
                'image' => null,
            ],
            [
                'title' => 'Üle jäänud kuivsegud ja pahtel',
                'description' => 'Mõned avamata kuivsegu ja pahtli kotid. Säilitatud kuivas ruumis. Sobivad väiksemateks remondi- ja viimistlustöödeks.',
                'price' => 12,
                'intent' => 'sell',
                'condition' => 'leftover',
                'category_slug' => 'segud-ja-mordid',
                'image' => null,
            ],
            [
                'title' => 'Vihmaveerennide jäägid',
                'description' => 'Katuseprojektist üle jäänud vihmaveerennide ja kinnituste komplekt. Mõned detailid on mõõtu lõigatud.',
                'price' => 28,
                'intent' => 'sell',
                'condition' => 'leftover',
                'category_slug' => 'katusematerjalid',
                'image' => null,
            ],
            [
                'title' => 'Kasutatud radiaatorid',
                'description' => 'Küttesüsteemi uuendamise käigus eemaldatud radiaatorid. Töökorras eemaldamise hetkel, vajavad ülevaatust enne paigaldust.',
                'price' => 45,
                'intent' => 'sell',
                'condition' => 'used',
                'category_slug' => 'sanitaartehnika',
                'image' => null,
            ],
            [
                'title' => 'Annetada ehitusmaterjale kogukonnaprojektile',
                'description' => 'Väiksem kogus erinevaid ehitusmaterjale, mida soovime annetada kogukondliku remondiprojekti jaoks. Sisaldab puitu, plaate ja tarvikuid.',
                'price' => 0,
                'intent' => 'donate',
                'condition' => 'leftover',
                'category_slug' => 'muu',
                'image' => null,
            ],
        ];

        foreach ($listings as $item) {
            $user = $users->random();

            $category = $categoriesBySlug->get($item['category_slug']);

            if (! $category) {
                $this->command->warn("Kategooriat ei leitud: {$item['category_slug']} ({$item['title']})");
                continue;
            }

            $listing = Listing::query()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'location_id' => $locations->random()->id,
                'title' => $item['title'],
                'description' => $item['description'],
                'price' => $item['price'],
                'currency' => 'EUR',
                'listing_type' => 'sale',
                'status' => 'published',
                'published_at' => now()->subDays(rand(1, 35)),
                'expires_at' => now()->addDays(rand(14, 60)),
                'intent' => $item['intent'],
                'condition' => $item['condition'],
                'delivery_options' => collect([
                    ['pickup'],
                    ['pickup', 'agreement'],
                    ['agreement'],
                ])->random(),
                'vat_included' => $user->role === 'business',
            ]);

            if (! empty($item['image'])) {
                $this->attachDemoImage($listing, $item['image']);
            }
        }
    }

    private function attachDemoImage(Listing $listing, string $imageName): void
    {
        $sourcePath = database_path('seeders/demo-images/'.$imageName);

        if (! file_exists($sourcePath)) {
            $this->command->warn("Demo pilti ei leitud: {$imageName}");
            return;
        }

        $monthPath = now()->format('Y/m');

        $largeDirectory = 'listings/large/'.$monthPath;
        $thumbDirectory = 'listings/thumb/'.$monthPath;

        Storage::disk('public')->makeDirectory($largeDirectory);
        Storage::disk('public')->makeDirectory($thumbDirectory);

        $extension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $safeName = Str::slug(pathinfo($imageName, PATHINFO_FILENAME));

        $baseFilename = Str::uuid().'-'.$safeName;

        $filename = $baseFilename.'.'.$extension;
        $thumbFilename = $baseFilename.'-thumb.jpg';

        $path = $largeDirectory.'/'.$filename;
        $thumbPath = $thumbDirectory.'/'.$thumbFilename;

        Storage::disk('public')->put($path, file_get_contents($sourcePath));

        $fullAbsolutePath = Storage::disk('public')->path($path);
        $thumbAbsolutePath = Storage::disk('public')->path($thumbPath);

        $this->createThumbnail($sourcePath, $thumbAbsolutePath, 400, 267);

        [$width, $height] = getimagesize($sourcePath) ?: [null, null];

        ListingImage::query()->create([
            'listing_id' => $listing->id,
            'disk' => 'public',
            'path' => $path,
            'thumb_path' => $thumbPath,
            'mime_type' => mime_content_type($sourcePath) ?: 'image/jpeg',
            'file_size' => file_exists($fullAbsolutePath) ? filesize($fullAbsolutePath) : null,
            'width' => $width,
            'height' => $height,
            'sort_order' => 0,
        ]);
    }

    private function createThumbnail(string $sourcePath, string $targetPath, int $targetWidth, int $targetHeight): void
    {
        $mime = mime_content_type($sourcePath);

        $source = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => null,
        };

        if (! $source) {
            $this->command->warn("Thumbnaili ei saanud luua: {$sourcePath}");
            return;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $sourceRatio = $sourceWidth / $sourceHeight;
        $targetRatio = $targetWidth / $targetHeight;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) ($sourceHeight * $targetRatio);
            $cropX = (int) (($sourceWidth - $cropWidth) / 2);
            $cropY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) ($sourceWidth / $targetRatio);
            $cropX = 0;
            $cropY = (int) (($sourceHeight - $cropHeight) / 2);
        }

        $thumb = imagecreatetruecolor($targetWidth, $targetHeight);

        imagecopyresampled(
            $thumb,
            $source,
            0,
            0,
            $cropX,
            $cropY,
            $targetWidth,
            $targetHeight,
            $cropWidth,
            $cropHeight
        );

        imagejpeg($thumb, $targetPath, 85);

        imagedestroy($source);
        imagedestroy($thumb);
    }
}