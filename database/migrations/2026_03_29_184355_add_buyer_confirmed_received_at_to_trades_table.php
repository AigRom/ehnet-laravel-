<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->timestamp('buyer_confirmed_received_at')
                ->nullable()
                ->after('completed_at')
                ->comment('Millal ostja kinnitas kauba kättesaamise');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn('buyer_confirmed_received_at');
        });
    }
};
