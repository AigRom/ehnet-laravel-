<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();

            // Kasutaja, kes blokkis
            $table->foreignId('blocker_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Kasutaja, kes blokeeriti
            $table->foreignId('blocked_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            // Sama kasutajat ei saa sama inimese poolt mitu korda blokeerida
            $table->unique(['blocker_id', 'blocked_user_id']);

            // Lisame indeksid päringute kiirendamiseks
            $table->index('blocker_id');
            $table->index('blocked_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
    }
};