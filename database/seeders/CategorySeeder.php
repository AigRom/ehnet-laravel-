<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['et' => 'Kinnitustarvikud',            'en' => 'Fasteners',                    'ru' => 'Крепёж'],
            ['et' => 'Puitmaterjalid',              'en' => 'Timber',                       'ru' => 'Пиломатериалы'],
            ['et' => 'Viimistlusmaterjalid',        'en' => 'Finishing materials',          'ru' => 'Отделочные материалы'],
            ['et' => 'Sanitaartehnika',             'en' => 'Plumbing',                     'ru' => 'Сантехника'],
            ['et' => 'Elektrimaterjalid ja valgustid','en' => 'Electrical & Lighting',     'ru' => 'Электрика и освещение'],
            ['et' => 'Ehitusplaadid',               'en' => 'Building boards',              'ru' => 'Строительные плиты'],
            ['et' => 'Katusematerjalid',            'en' => 'Roofing materials',            'ru' => 'Кровельные материалы'],
            ['et' => 'Fassaadimaterjalid',          'en' => 'Facade materials',             'ru' => 'Фасадные материалы'],
            ['et' => 'Aknad ja uksed',               'en' => 'Windows & Doors',              'ru' => 'Окна и двери'],
            ['et' => 'Soojustusmaterjalid',         'en' => 'Insulation',                   'ru' => 'Теплоизоляция'],
            ['et' => 'Tellised ja plokid',           'en' => 'Bricks & Blocks',              'ru' => 'Кирпичи и блоки'],
            ['et' => 'Ventilatsioonimaterjalid',    'en' => 'Ventilation materials',        'ru' => 'Вентиляционные материалы'],
            ['et' => 'Metallmaterjalid',             'en' => 'Metal materials',              'ru' => 'Металлоконструкции'],
            ['et' => 'Segud ja mördid',              'en' => 'Mortars & mixes',              'ru' => 'Смеси и растворы'],
            ['et' => 'Tööriistad',                   'en' => 'Tools',                        'ru' => 'Инструменты'],
            ['et' => 'Muu',                          'en' => 'Other',                        'ru' => 'Другое'],
        ];

        foreach ($categories as $index => $cat) {
            Category::updateOrCreate(
                ['slug' => Str::slug($cat['et'])],
                [
                    'name_et'   => $cat['et'],
                    'name_en'   => $cat['en'],
                    'name_ru'   => $cat['ru'],
                    'is_active' => true,
                    'sort_order'=> $index,
                ]
            );
        }
    }
}
