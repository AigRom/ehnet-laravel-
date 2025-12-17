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
        Schema::create('listings', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('category_id')->constrained()->restrictOnDelete();
        $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();

        $table->string('title', 140);
        $table->text('description');

        // tavakuulutuse hind (null = kokkuleppel, 0 = tasuta)
        $table->decimal('price', 10, 2)->nullable();
        $table->string('currency', 3)->default('EUR');

        // sale | auction
        $table->string('listing_type', 20)->default('sale');

        // draft|pending|published|rejected|archived
        $table->string('status', 20)->default('published');
        $table->timestamp('published_at')->nullable();

        // moderatsiooni jaoks (võid ignoreerida kuni vaja)
        $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('reviewed_at')->nullable();
        $table->string('rejected_reason', 255)->nullable();

        $table->timestamps();

        $table->index(['listing_type', 'status']);
        $table->index(['category_id']);
        $table->index(['location_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
