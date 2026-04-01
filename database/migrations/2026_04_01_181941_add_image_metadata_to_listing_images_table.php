<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('listing_images', function (Blueprint $table) {
            $table->string('disk')->default('public')->after('listing_id');
            $table->string('thumb_path')->nullable()->after('path');
            $table->string('mime_type', 100)->nullable()->after('thumb_path');
            $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
            $table->unsignedInteger('width')->nullable()->after('file_size');
            $table->unsignedInteger('height')->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('listing_images', function (Blueprint $table) {
            $table->dropColumn([
                'disk',
                'thumb_path',
                'mime_type',
                'file_size',
                'width',
                'height',
            ]);
        });
    }
};