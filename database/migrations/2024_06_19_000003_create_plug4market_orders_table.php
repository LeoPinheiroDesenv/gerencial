<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plug4market_orders', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('order_number')->nullable();
            $table->string('customer_email');
            $table->string('customer_name');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('sale_channel_name')->nullable();
            $table->string('sale_channel_id')->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            $table->timestamp('created_at_api')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plug4market_orders');
    }
}; 