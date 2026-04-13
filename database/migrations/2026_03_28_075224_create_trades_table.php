<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();

            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();

            $table->string('status')->default('interest');
            // interest | reserved | awaiting_confirmation | completed | cancelled

            $table->timestamp('contact_revealed_at')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['conversation_id', 'status']);
            $table->index(['listing_id', 'status']);
            $table->index(['seller_id']);
            $table->index(['buyer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};