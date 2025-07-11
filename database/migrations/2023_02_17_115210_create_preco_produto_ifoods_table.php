<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrecoProdutoIfoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preco_produto_ifoods', function (Blueprint $table) {
            $table->id();

            $table->string('id_ifood', 50);
            $table->integer('produto_ifood_id')->unsigned();
            $table->foreign('produto_ifood_id')->references('id')
            ->on('produto_ifoods')->onDelete('cascade');

            $table->decimal('valor', 10, 2)->nullable();
            

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
        Schema::dropIfExists('preco_produto_ifoods');
    }
}
