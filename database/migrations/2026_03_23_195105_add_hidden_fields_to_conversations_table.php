<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->timestamp('seller_hidden_at')->nullable()->after('buyer_id');
            $table->timestamp('buyer_hidden_at')->nullable()->after('seller_hidden_at');
            $table->timestamp('fully_hidden_at')->nullable()->after('buyer_hidden_at');

            $table->index('seller_hidden_at');
            $table->index('buyer_hidden_at');
            $table->index('fully_hidden_at');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['seller_hidden_at']);
            $table->dropIndex(['buyer_hidden_at']);
            $table->dropIndex(['fully_hidden_at']);

            $table->dropColumn([
                'seller_hidden_at',
                'buyer_hidden_at',
                'fully_hidden_at',
            ]);
        });
    }
};