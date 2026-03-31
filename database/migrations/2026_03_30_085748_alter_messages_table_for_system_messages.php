<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // süsteemisõnumite jaoks sender võib puududa
            $table->foreignId('sender_id')->nullable()->change();

            // message tüüp: user / system
            $table->string('type')->default('user')->after('sender_id');

            // lisainfo süsteemisündmuste jaoks
            $table->json('meta')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // eemaldame lisatud väljad
            $table->dropColumn(['type', 'meta']);

            // taastame sender_id nõutavaks (ettevaatust: kui null väärtused olemas, rollback failib)
            $table->foreignId('sender_id')->nullable(false)->change();
        });
    }
};
