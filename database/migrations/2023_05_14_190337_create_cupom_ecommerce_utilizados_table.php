<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCupomEcommerceUtilizadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cupom_ecommerce_utilizados', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('cupom_id')->unsigned();
            $table->foreign('cupom_id')->references('id')
            ->on('cupom_desconto_ecommerces')->onDelete('cascade');

            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')
            ->on('cliente_ecommerces')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cupom_ecommerce_utilizados');
    }
}
