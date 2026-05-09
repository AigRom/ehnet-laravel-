<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('first_name')->nullable()->after('email');
            $table->string('last_name')->nullable()->after('first_name');

            $table->date('date_of_birth')->nullable()->after('last_name');

            $table->string('phone', 50)->nullable()->after('date_of_birth');
            $table->string('region')->nullable()->after('phone');
            $table->string('city')->nullable()->after('region');

            $table->string('company_name')->nullable()->after('name');
            $table->string('company_reg_no')->nullable()->after('company_name');

            $table->string('contact_first_name')->nullable()->after('company_reg_no');
            $table->string('contact_last_name')->nullable()->after('contact_first_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'date_of_birth',
                'phone',
                'region',
                'city',
                'company_name',
                'company_reg_no',
                'contact_first_name',
                'contact_last_name',
            ]);
        });
    }
};
