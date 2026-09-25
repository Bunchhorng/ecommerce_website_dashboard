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
        if (! Schema::hasColumn('inventories', 'shop_id')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->foreignId('shop_id')->nullable()->after('product_variant_id')->constrained()->nullOnDelete();
            });
        }

        // The previous unique was per-variant globally; per-branch stock means
        // the same variant may exist in multiple shops. Replace with a
        // composite unique (product_variant_id, shop_id).
        //
        // The unique index also backs the product_variant_id foreign key, so
        // MySQL will not drop it while the FK exists. Drop the FK, swap the
        // unique, then re-create the FK. Non-destructive: no rows touched.
        $needsSwap = ! Schema::hasIndex('inventories', 'inventories_variant_shop_unique');

        if ($needsSwap) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropForeign(['product_variant_id']);
            });

            if (Schema::hasIndex('inventories', 'inventories_product_variant_id_unique')) {
                Schema::table('inventories', function (Blueprint $table) {
                    $table->dropUnique('inventories_product_variant_id_unique');
                });
            }

            Schema::table('inventories', function (Blueprint $table) {
                $table->unique(['product_variant_id', 'shop_id'], 'inventories_variant_shop_unique');
                $table->foreign('product_variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
            });
        }

        if (! Schema::hasIndex('inventories', ['shop_id'])) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->index('shop_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('inventories', 'inventories_variant_shop_unique')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropForeign(['product_variant_id']);
                $table->dropUnique('inventories_variant_shop_unique');
                $table->unique('product_variant_id');
                $table->foreign('product_variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
            });
        }

        Schema::table('inventories', function (Blueprint $table) {
            if (Schema::hasIndex('inventories', ['shop_id'])) {
                $table->dropIndex(['shop_id']);
            }
        });

        if (Schema::hasColumn('inventories', 'shop_id')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropConstrainedForeignId('shop_id');
            });
        }
    }
};
