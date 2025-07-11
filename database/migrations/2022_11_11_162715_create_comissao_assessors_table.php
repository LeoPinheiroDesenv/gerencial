<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComissaoAssessorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comissao_assessors', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('venda_caixa_id')->nullable()->unsigned();
            $table->foreign('venda_caixa_id')->references('id')->on('venda_caixas')
            ->onDelete('cascade');

            $table->decimal('valor', 10, 2);
            $table->boolean('status')->default(false);

            $table->integer('assessor_id')->nullable()->unsigned();
            $table->foreign('assessor_id')->references('id')->on('acessors')
            ->onDelete('cascade');

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
        Schema::dropIfExists('comissao_assessors');
    }
}
