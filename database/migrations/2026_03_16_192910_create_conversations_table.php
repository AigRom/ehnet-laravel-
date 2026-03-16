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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // Millise kuulutuse kohta vestlus käib
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();

            // Kuulutuse omanik
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();

            // Kasutaja, kes kirjutab müüjale
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();

            $table->timestamps();

            // Väldib topeltvestlusi sama kuulutuse ja sama ostja/müüja vahel
            $table->unique(['listing_id', 'seller_id', 'buyer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};