<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->timestamp('seller_read_at')->nullable()->after('read_at');
            $table->timestamp('buyer_read_at')->nullable()->after('seller_read_at');
        });

        DB::table('messages')
            ->whereNotNull('read_at')
            ->update([
                'seller_read_at' => DB::raw('read_at'),
                'buyer_read_at' => DB::raw('read_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn([
                'seller_read_at',
                'buyer_read_at',
            ]);
        });
    }
};