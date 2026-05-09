<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->enum('role', ['admin', 'moderator', 'customer', 'business'])
                ->default('customer')
                ->after('password');

            $table->boolean('is_active')
                ->default(true)
                ->after('role');

            $table->timestamp('terms_accepted_at')
                ->nullable()
                ->after('email_verified_at');

            $table->string('auth_provider')
                ->nullable()
                ->after('is_active');

            $table->string('auth_provider_id')
                ->nullable()
                ->after('auth_provider');

            $table->timestamp('last_login_at')
                ->nullable()
                ->after('remember_token');
        });
    }

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
