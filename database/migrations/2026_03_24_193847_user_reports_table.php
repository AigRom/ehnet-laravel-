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
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();

            // Kes teatas
            $table->foreignId('reporter_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Kelle kohta teade tehti
            $table->foreignId('reported_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Seos vestlusega (valikuline)
            $table->foreignId('conversation_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Põhjus (nt spam, abuse jne)
            $table->string('reason');

            // Täpsem kirjeldus
            $table->text('details')->nullable();

            // Admin jaoks (hiljem)
            $table->string('status')->default('new');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
