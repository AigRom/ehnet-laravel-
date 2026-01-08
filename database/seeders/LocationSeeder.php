<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/locations.csv');

        if (!File::exists($path)) {
            throw new \RuntimeException("CSV not found at: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $rows  = array_map(fn ($line) => str_getcsv($line), $lines);

        $header = array_shift($rows);
        $header = array_map('trim', $header);
        $idx = array_flip($header);

        DB::transaction(function () use ($rows, $idx) {

            foreach ($rows as $r) {
                // vajalikud väljad CSV-st
                $value = trim($r[$idx['Value']] ?? '');
                if ($value === '') {
                    continue;
                }

                $parent = trim($r[$idx['Parent']] ?? '');
                $level  = (int)($r[$idx['Level']] ?? 0);

                $nameEt = $r[$idx['Label-et-EE']] ?? null;
                $nameEn = $r[$idx['Label-en-GB']] ?? null;
                $nameRu = $r[$idx['Label-ru-RU']] ?? null;

                $fullEt = $r[$idx['Description-et-EE']] ?? $nameEt;
                $fullEn = $r[$idx['Description-en-GB']] ?? $nameEn;
                $fullRu = $r[$idx['Description-ru-RU']] ?? $nameRu;

                $isValid = isset($idx['IsValid']) ? (bool)((int)($r[$idx['IsValid']] ?? 1)) : true;

                DB::table('locations')->upsert(
                    [[
                        'ehak_code'         => (int)$value,
                        'parent_ehak_code'  => $parent !== '' ? (int)$parent : null,
                        'level'             => $level,

                        'name_et'           => $nameEt ?? '',
                        'name_en'           => $nameEn,
                        'name_ru'           => $nameRu,

                        'full_label_et'     => $fullEt ?? ($nameEt ?? ''),
                        'full_label_en'     => $fullEn,
                        'full_label_ru'     => $fullRu,

                        'is_valid'          => $isValid,

                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]],
                    ['ehak_code'], // unique key
                    [
                        'parent_ehak_code','level',
                        'name_et','name_en','name_ru',
                        'full_label_et','full_label_en','full_label_ru',
                        'is_valid','updated_at'
                    ]
                );
            }
        });
    }
}
