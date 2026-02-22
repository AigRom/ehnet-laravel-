<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            // Laravel default FK nimed:
            // listings_category_id_foreign, listings_location_id_foreign
            $table->dropForeign(['category_id']);
            $table->dropForeign(['location_id']);
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
            $table->unsignedBigInteger('location_id')->nullable()->change();

            $table->string('title', 140)->nullable()->change();
            $table->text('description')->nullable()->change();
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            $table->foreign('location_id')->references('id')->on('locations')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['location_id']);
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
            $table->unsignedBigInteger('location_id')->nullable(false)->change();

            $table->string('title', 140)->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            $table->foreign('location_id')->references('id')->on('locations')->restrictOnDelete();
        });
    }
};
