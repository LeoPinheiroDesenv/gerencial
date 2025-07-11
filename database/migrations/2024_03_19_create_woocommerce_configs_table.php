<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('woocommerce_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('store_url');
            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->boolean('is_active')->default(false);
            $table->boolean('sync_products')->default(false);
            $table->boolean('sync_orders')->default(false);
            $table->boolean('sync_stock')->default(false);
            $table->integer('default_category_id')->nullable();
            $table->string('default_status')->default('publish');
            $table->decimal('price_markup', 5, 2)->default(0);
            $table->json('shipping_methods')->nullable();
            $table->json('payment_methods')->nullable();
            $table->timestamp('last_sync')->nullable();
            $table->boolean('auto_sync')->default(false);
            $table->integer('sync_interval')->default(60);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('woocommerce_configs');
    }
}; 