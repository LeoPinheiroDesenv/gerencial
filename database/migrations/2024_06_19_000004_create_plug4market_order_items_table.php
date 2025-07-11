<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plug4market_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('plug4market_orders')->onDelete('cascade');
            $table->string('product_id');
            $table->string('product_name');
            $table->string('sku');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plug4market_order_items');
    }
}; 