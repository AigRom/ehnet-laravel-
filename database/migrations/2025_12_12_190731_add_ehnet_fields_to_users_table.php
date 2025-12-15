<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // roll: admin, moderator, customer, business
            $table->enum('role', ['admin', 'moderator', 'customer', 'business'])
                ->default('customer')
                ->after('password');

            // kas konto on aktiivne
            $table->boolean('is_active')
                ->default(true)
                ->after('role');

            // millal nõustus kasutustingimustega
            $table->timestamp('terms_accepted_at')
                ->nullable()
                ->after('email_verified_at');

            // autentimise allikas (email, google, smartid jne)
            $table->string('auth_provider')
                ->nullable()
                ->after('is_active');

            // autentimise allika kasutaja ID (nt Google ID)
            $table->string('auth_provider_id')
                ->nullable()
                ->after('auth_provider');

            // viimane sisselogimine
            $table->timestamp('last_login_at')
                ->nullable()
                ->after('remember_token');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'is_active',
                'terms_accepted_at',
                'auth_provider',
                'auth_provider_id',
                'last_login_at',
            ]);
        });
    }

};
