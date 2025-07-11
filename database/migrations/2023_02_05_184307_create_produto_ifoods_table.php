<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutoIfoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produto_ifoods', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->integer('categoria_id')->unsigned();
            $table->foreign('categoria_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->string('id_ifood', 50);
            $table->string('id_ifood_aux', 50);
            $table->string('nome', 150);
            $table->text('descricao');
            $table->string('imagem', 200);
            $table->string('serving', 20)->nullable();
            $table->string('ean', 20)->nullable();
            $table->decimal('valor', 10, 2)->nullable();

            $table->string('status', 20)->nullable();
            $table->decimal('estoque', 10, 2)->nullable();

            $table->integer('sellingOption_minimum')->nullable();
            $table->integer('sellingOption_incremental')->nullable();
            $table->integer('sellingOption_averageUnit')->nullable();
            $table->string('sellingOption_availableUnits', 100)->nullable();

            // alter table produto_ifoods add column estoque decimal(10,2) default null;
            // alter table produto_ifoods add column id_ifood_aux varchar(50) default null;

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
        Schema::dropIfExists('produto_ifoods');
    }
}
