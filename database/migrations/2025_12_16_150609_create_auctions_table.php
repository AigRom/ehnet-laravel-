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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('listing_id')
                ->unique()
                ->constrained('listings')
                ->cascadeOnDelete();

            $table->decimal('start_price', 10, 2);
            $table->decimal('min_increment', 10, 2)->default(1.00);

            // null = oksjon algab kohe
            $table->dateTime('starts_at')->nullable(); // null = kohe

            // oksjonil peab lõpp olema
            $table->dateTime('ends_at');              // kohustuslik

            // valikulised tulevikuks
            $table->decimal('reserve_price', 10, 2)->nullable();
            $table->decimal('buy_now_price', 10, 2)->nullable();

            $table->timestamps();

            $table->index('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
