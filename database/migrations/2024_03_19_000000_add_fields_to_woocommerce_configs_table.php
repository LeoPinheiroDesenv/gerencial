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
        Schema::table('woocommerce_configs', function (Blueprint $table) {
            $table->boolean('sync_products')->default(true);
            $table->boolean('sync_orders')->default(true);
            $table->boolean('sync_stock')->default(true);
            $table->string('default_category_id')->nullable();
            $table->string('default_status')->default('publish');
            $table->decimal('price_markup', 10, 2)->default(0);
            $table->json('shipping_methods')->nullable();
            $table->json('payment_methods')->nullable();
            $table->timestamp('last_sync')->nullable();
            $table->boolean('auto_sync')->default(false);
            $table->integer('sync_interval')->default(60); // em minutos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woocommerce_configs', function (Blueprint $table) {
            $table->dropColumn([
                'sync_products',
                'sync_orders',
                'sync_stock',
                'default_category_id',
                'default_status',
                'price_markup',
                'shipping_methods',
                'payment_methods',
                'last_sync',
                'auto_sync',
                'sync_interval'
            ]);
        });
    }
}; 