<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultLocationId = Location::query()->value('id');

        if (! $defaultLocationId) {
            $this->command->warn('LocationSeeder peab enne UserSeederit käima.');

            return;
        }

        $users = [
            [
                'name' => 'Martin Saar',
                'first_name' => 'Martin',
                'last_name' => 'Saar',
                'email' => 'martin.saar@ehnet.test',
                'phone' => '+372 5123 4567',
                'role' => 'customer',
            ],
            [
                'name' => 'Katrin Mägi',
                'first_name' => 'Katrin',
                'last_name' => 'Mägi',
                'email' => 'katrin.magi@ehnet.test',
                'phone' => '+372 5345 6789',
                'role' => 'customer',
            ],
            [
                'name' => 'Rasmus Tamm',
                'first_name' => 'Rasmus',
                'last_name' => 'Tamm',
                'email' => 'rasmus.tamm@ehnet.test',
                'phone' => '+372 5567 8901',
                'role' => 'customer',
            ],
            [
                'name' => 'NordEhitus OÜ',
                'company_name' => 'NordEhitus OÜ',
                'company_reg_no' => '16845231',
                'contact_first_name' => 'Andres',
                'contact_last_name' => 'Kask',
                'email' => 'info@nordehitus.test',
                'phone' => '+372 600 1234',
                'role' => 'business',
            ],
            [
                'name' => 'Roheline Renoveerimine OÜ',
                'company_name' => 'Roheline Renoveerimine OÜ',
                'company_reg_no' => '14982376',
                'contact_first_name' => 'Liina',
                'contact_last_name' => 'Põld',
                'email' => 'info@rohereno.test',
                'phone' => '+372 601 5678',
                'role' => 'business',
            ],
            [
                'name' => 'KoduMaterjalid OÜ',
                'company_name' => 'KoduMaterjalid OÜ',
                'company_reg_no' => '16294758',
                'contact_first_name' => 'Tarmo',
                'contact_last_name' => 'Lepp',
                'email' => 'info@kodumaterjalid.test',
                'phone' => '+372 602 9012',
                'role' => 'business',
            ],
        ];

        foreach ($users as $user) {
            $user['phone'] = isset($user['phone'])
                ? preg_replace('/\D+/', '', $user['phone'])
                : null;

            User::updateOrCreate(
                ['email' => $user['email']],
                array_merge([
                    'password' => Hash::make('Parool123'),
                    'location_id' => $defaultLocationId,
                    'email_verified_at' => now(),
                    'terms_accepted_at' => now(),
                    'is_active' => true,
                    'auth_provider' => 'email',
                ], $user)
            );
        }
    }
}
