<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaturaCtesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fatura_ctes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('numero_fatura');
            $table->date('vencimento');
            $table->decimal('valor_total', 10, 2);
            $table->decimal('desconto', 10, 2);

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('remetente_id')->unsigned();
            $table->foreign('remetente_id')->references('id')->on('clientes');

            $table->boolean('conta_receber_id')->nullable();

            $table->text('observacao');

            // alter table fatura_ctes add column conta_receber_id integer default null;

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
        Schema::dropIfExists('fatura_ctes');
    }
}
