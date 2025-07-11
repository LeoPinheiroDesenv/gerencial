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
        Schema::create('produto_woocommerce', function (Blueprint $table) {
            $table->id();
        $table->integer('produto_id');
        $table->string('woocommerce_id');
        $table->decimal('woocommerce_valor', 10, 2);
        $table->string('woocommerce_link');
        $table->string('woocommerce_status');
        $table->integer('empresa_id');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produto_woocommerce');
    }
};
