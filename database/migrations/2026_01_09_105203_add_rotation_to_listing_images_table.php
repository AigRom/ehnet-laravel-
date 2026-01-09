<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('listing_images', function (Blueprint $table) {
            $table->unsignedSmallInteger('rotation')->default(0)->after('sort_order'); // 0/90/180/270
        });
    }

    public function down(): void
    {
        Schema::table('listing_images', function (Blueprint $table) {
            $table->dropColumn('rotation');
        });
    }

};
