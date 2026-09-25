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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 30)->unique(); // e.g. AEON
            $table->string('branch_type', 30)->nullable(); // storefront | warehouse | outlet
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('address_line')->nullable();
            $table->string('mall', 120)->nullable(); // e.g. AEON Mall Sen Sok
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 2)->default('KH'); // ISO-3166 alpha-2
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 20)->default('pending')->index(); // pending active suspended
            $table->boolean('is_default')->default(false);
            $table->decimal('commission_rate', 5, 2)->default(0); // platform commission %
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
