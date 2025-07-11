<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_systems', function (Blueprint $table) {
            $table->increments('id');

            $table->string('cor', 10);

            $table->string('mensagem_plano_indeterminado', 250)->nullable();
            $table->integer('inicio_mensagem_plano')->nullable();
            $table->integer('fim_mensagem_plano')->nullable();
            $table->decimal('valor_base_contrato', 10, 2)->nullable();

            $table->string('usuario_correios', 30)->nullable();
            $table->string('codigo_acesso_correios', 100)->nullable();
            $table->string('cartao_postagem_correios', 100)->nullable();
            $table->text('token_correios');
            $table->string('token_expira_correios', 30);
            $table->string('dr_correios', 30);
            $table->string('contrato_correios', 30);
            $table->string('token_integra_notas', 255)->nullable();

            $table->integer('timeout_nfe')->default(8);
            $table->integer('timeout_nfce')->default(8);
            $table->integer('timeout_cte')->default(8);
            $table->integer('timeout_mdfe')->default(8);

            // alter table config_systems add column usuario_correios varchar(30) default null;
            // alter table config_systems add column codigo_acesso_correios varchar(100) default null;
            // alter table config_systems add column cartao_postagem_correios varchar(100) default null;
            // alter table config_systems add column token_correios text;
            // alter table config_systems add column token_expira_correios varchar(30) default null;
            // alter table config_systems add column dr_correios varchar(30) default null;
            // alter table config_systems add column contrato_correios varchar(30) default null;
            // alter table config_systems add column token_integra_notas varchar(255) default null;
            
            // alter table config_systems add column timeout_nfe integer default 8;
            // alter table config_systems add column timeout_nfce integer default 8;
            // alter table config_systems add column timeout_cte integer default 8;
            // alter table config_systems add column timeout_mdfe integer default 8;
            
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
        Schema::dropIfExists('config_systems');
    }
}
