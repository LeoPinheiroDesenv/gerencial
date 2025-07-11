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
        Schema::create('woocommerce_configs', function (Blueprint $table) {
            $table->id();
        $table->integer('empresa_id')->unsigned();
        $table->string('store_url');
        $table->string('consumer_key');
        $table->string('consumer_secret');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
               
        $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woocommerce_configs');
    }
};
