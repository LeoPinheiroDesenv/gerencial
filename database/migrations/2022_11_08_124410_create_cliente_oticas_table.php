<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteOticasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente_oticas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('cliente_id')->unsigned()->nullable();
            $table->foreign('cliente_id')->references('id')
            ->on('clientes')->onDelete('cascade');

            $table->decimal('esf_od_longe', 6,2);
            $table->decimal('cil_od_longe', 6,2);
            $table->decimal('eixo_od_longe', 6,2);
            $table->decimal('dnp_od_longe', 6,2);
            $table->decimal('dp_od_longe', 6,2);

            $table->decimal('esf_oe_longe', 6,2);
            $table->decimal('cli_oe_longe', 6,2);
            $table->decimal('eixo_oe_longe', 6,2);
            $table->decimal('dnp_oe_longe', 6,2);

            $table->decimal('esf_od_perto', 6,2);
            $table->decimal('cil_od_perto', 6,2);
            $table->decimal('eixo_od_perto', 6,2);
            $table->decimal('adicao_od_perto', 6,2);
            $table->decimal('altura_od_perto', 6,2);
            $table->decimal('dnp_od_perto', 6,2);
            $table->decimal('dp_od_perto', 6,2);

            $table->decimal('esf_oe_perto', 6,2);
            $table->decimal('cil_oe_perto', 6,2);
            $table->decimal('eixo_oe_perto', 6,2);
            $table->decimal('adicao_oe_perto', 6,2);
            $table->decimal('altura_oe_perto', 6,2);
            $table->decimal('dnp_oe_perto', 6,2);

            $table->string('armacao', 50);
            $table->integer('qtd_armacao');
            $table->decimal('valor_armacao', 10, 2);
            $table->string('lente', 50);
            $table->integer('qtd_lente');
            $table->decimal('valor_lente', 10, 2);

            $table->string('tratamento', 100);
            $table->string('medico', 100);
            $table->string('tipo_lente', 50);
            $table->integer('previsao_retorno_dias');
            $table->string('data', 10);

            $table->string('referencia', 100);
            $table->string('observacao', 200);


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
        Schema::dropIfExists('cliente_oticas');
    }
}
