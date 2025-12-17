<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLocations extends Command
{
    protected $signature = 'locations:import';
    protected $description = 'Import EHAK locations from CSV';

    public function handle(): int
    {
        $path = storage_path('app/data/ehak.csv');

        if (! file_exists($path)) {
            $this->error('CSV file not found: ' . $path);
            return self::FAILURE;
        }

        $this->info('Importing locations from EHAK CSV...');

        $handle = fopen($path, 'r');
        if (! $handle) {
            $this->error('Could not open file: ' . $path);
            return self::FAILURE;
        }

        // 1) Read first line (raw) and detect delimiter
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            $this->error('CSV is empty.');
            fclose($handle);
            return self::FAILURE;
        }

        // Detect delimiter: prefer tab if present, else comma
        $delimiter = str_contains($firstLine, "\t") ? "\t" : ",";

        // Parse header using detected delimiter
        $header = str_getcsv($firstLine, $delimiter);

        // Clean header keys (trim + remove UTF-8 BOM)
        $header = array_map(function ($h) {
            $h = trim($h);
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h); // remove BOM if present
            return $h;
        }, $header);

        $map = array_flip($header);

        // Safety: show what we detected if required column missing
        $required = [
            'Value',
            'Label-et-EE',
            'Label-en-GB',
            'Label-ru-RU',
            'Parent',
            'Level',
            'Description-et-EE',
            'Description-en-GB',
            'Description-ru-RU',
            'IsValid',
        ];

        foreach ($required as $col) {
            if (! array_key_exists($col, $map)) {
                $this->error("Missing column in CSV header: {$col}");
                $this->line('Detected header columns:');
                $this->line(implode(' | ', $header));
                fclose($handle);
                return self::FAILURE;
            }
        }

        // 2) Now read remaining rows with fgetcsv using detected delimiter
        $insertedOrUpdated = 0;

        DB::transaction(function () use ($handle, $map, $delimiter, &$insertedOrUpdated) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                // skip empty lines
                if (count($row) < 3) {
                    continue;
                }

                // only valid rows
                if (($row[$map['IsValid']] ?? null) !== '1') {
                    continue;
                }

                DB::table('locations')->updateOrInsert(
                    ['ehak_code' => (int) $row[$map['Value']]],
                    [
                        'parent_ehak_code' => (($row[$map['Parent']] ?? '') !== '')
                            ? (int) $row[$map['Parent']]
                            : null,

                        'level' => (int) $row[$map['Level']],

                        'name_et' => $row[$map['Label-et-EE']] ?? '',
                        'name_en' => $row[$map['Label-en-GB']] ?? null,
                        'name_ru' => $row[$map['Label-ru-RU']] ?? null,

                        'full_label_et' => $row[$map['Description-et-EE']] ?? '',
                        'full_label_en' => $row[$map['Description-en-GB']] ?? null,
                        'full_label_ru' => $row[$map['Description-ru-RU']] ?? null,

                        'is_valid' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $insertedOrUpdated++;
            }
        });

        fclose($handle);

        $count = DB::table('locations')->count();
        $this->info("Done. Processed {$insertedOrUpdated} valid rows. Total in DB: {$count}");

        return self::SUCCESS;
    }
}
