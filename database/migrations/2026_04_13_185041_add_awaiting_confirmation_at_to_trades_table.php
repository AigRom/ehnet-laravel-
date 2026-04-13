<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->timestamp('awaiting_confirmation_at')
                ->nullable()
                ->after('reserved_at')
                ->comment('Millal müüja märkis kauba üleantuks või saadetuks');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn('awaiting_confirmation_at');
        });
    }
};