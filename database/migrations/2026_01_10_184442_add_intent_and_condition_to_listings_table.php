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
        Schema::table('listings', function (Blueprint $table) {
            $table->string('intent', 30)->nullable()->after('listing_type');
            $table->string('condition', 30)->nullable()->after('intent');

            $table->index('intent');
            $table->index('condition');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex(['intent']);
            $table->dropIndex(['condition']);
            $table->dropColumn(['intent', 'condition']);
        });
    }

};
