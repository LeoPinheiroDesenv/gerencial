<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaturaDocCtesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fatura_doc_ctes', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('fatura_id')->unsigned();
            $table->foreign('fatura_id')->references('id')->on('fatura_ctes')
            ->onDelete('cascade');

            $table->integer('cte_id')->unsigned();
            $table->foreign('cte_id')->references('id')->on('ctes')
            ->onDelete('cascade');

            $table->string('unidade', 20)->nullable();
            $table->string('cte_numero', 20)->nullable();
            $table->string('chave_nfe', 44)->nullable();
            $table->decimal('valor_mercadoria', 10, 2);
            $table->decimal('peso', 10, 2);
            $table->decimal('frete', 10, 2);

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
        Schema::dropIfExists('fatura_doc_ctes');
    }
}
