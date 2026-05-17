<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_threads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name')->nullable();
            $table->string('email')->nullable();

            $table->string('category');
            $table->string('subject')->nullable();

            $table->string('status')->default('new');
            $table->string('priority')->default('normal');

            $table->timestamps();

            $table->index(['status', 'category']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_threads');
    }
};
