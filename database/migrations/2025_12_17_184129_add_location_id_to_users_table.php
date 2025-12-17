<?php

// database/migrations/xxxx_add_location_id_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('location_id')
                ->nullable()
                ->after('phone')
                ->constrained('locations')
                ->nullOnDelete();

            // vanad väljad eemaldame
            if (Schema::hasColumn('users', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('users', 'region')) {
                $table->dropColumn('region');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');

            // rollbackiks võib tagasi lisada
            $table->string('city')->nullable();
            $table->string('region')->nullable();
        });
    }
};
