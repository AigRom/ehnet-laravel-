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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('ehak_code')->unique(); // Value
            $table->unsignedInteger('parent_ehak_code')->nullable(); // Parent
            $table->unsignedTinyInteger('level'); // Level

            $table->string('name_et');
            $table->string('name_en')->nullable();
            $table->string('name_ru')->nullable();

            $table->string('full_label_et');
            $table->string('full_label_en')->nullable();
            $table->string('full_label_ru')->nullable();

            $table->boolean('is_valid')->default(true);

            $table->timestamps();

            $table->index('level');
            $table->index('parent_ehak_code');
            $table->index('full_label_et');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
