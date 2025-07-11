<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned()->nullable();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->string('razao_social', 100);
            $table->string('nome_fantasia', 80);
            $table->string('cpf_cnpj', 19)->default("000.000.000-00");
            $table->string('rua', 80);
            $table->string('ie_rg', 20);
            $table->string('numero', 10);
            $table->string('bairro', 50);
            $table->string('telefone', 20);
            $table->string('complemento', 100)->default('');
            $table->string('celular', 20)->default("00 00000 0000");
            $table->string('email', 60)->default("null");
            $table->string('cep', 10)->default("null");
            $table->integer('consumidor_final');
            $table->integer('contribuinte');
            $table->boolean('inativo')->default(0);

            $table->integer('cidade_id')->nullable()->unsigned();
            $table->foreign('cidade_id')->references('id')->on('cidades')->onDelete('cascade');

            $table->decimal('limite_venda', 10,2)->default(0);
            $table->decimal('valor_cashback', 10,2)->default(0);

            $table->string('rua_cobranca', 100);
            $table->string('numero_cobranca', 10);
            $table->string('bairro_cobranca', 30);
            $table->string('cep_cobranca', 9);

            $table->integer('cidade_cobranca_id')->nullable()->unsigned();
            $table->foreign('cidade_cobranca_id')->references('id')
            ->on('cidades')->onDelete('cascade');

            $table->integer('cod_pais')->default(1058);
            $table->string('id_estrangeiro', 30)->default("");

            $table->integer('grupo_id')->default(0);
            $table->integer('acessor_id')->default(0);

            $table->string('contador_nome', 30)->default('');
            $table->string('contador_telefone', 15)->default('');
            $table->string('contador_email', 60)->default('');

            $table->integer('funcionario_id')->default(0);
            $table->string('observacao')->default('');
            $table->string('data_aniversario', 5)->default('');
            $table->string('data_nascimento', 10)->default('');

            $table->string('nuvemshop_id', 20)->default('');
            $table->string('imagem', 30)->default('');

            $table->string('instagram', 255)->default('');
            $table->string('facebook', 255)->default('');
            $table->string('linkedin', 255)->default('');
            $table->string('tiktok', 255)->default('');
            $table->string('whatsapp', 255)->default('');

            $table->string('rua_entrega', 100);
            $table->string('nome_entrega', 80);
            $table->string('cpf_cnpj_entrega', 20);
            $table->string('numero_entrega', 10);
            $table->string('bairro_entrega', 30);
            $table->string('cep_entrega', 9);
            $table->integer('cidade_entrega_id')->nullable();

            $table->string('nome_responsavel', 100)->nullable();
            $table->string('cpf_responsavel', 14)->nullable();
            $table->string('rg_responsavel', 15)->nullable();
            $table->date('data_nascimento_responsavel')->nullable();
            $table->string('rua_responsavel', 60)->nullable();
            $table->string('numero_responsavel', 15)->nullable();
            $table->string('bairro_responsavel', 45)->nullable();
            $table->integer('cidade_responsavel')->nullable();
            $table->string('complemento_responsavel', 60)->nullable();
            $table->string('cep_responsavel', 9)->nullable();
            $table->string('email_responsavel', 60)->nullable();
            $table->string('telefone_responsavel', 15)->nullable();

            // alter table clientes add column nome_responsavel varchar(255) default null;
            // alter table clientes add column cpf_responsavel varchar(14) default null;
            // alter table clientes add column rg_responsavel varchar(15) default null;
            // alter table clientes add column data_nascimento_responsavel date default null;
            // alter table clientes add column rua_responsavel varchar(60) default null;
            // alter table clientes add column numero_responsavel varchar(15) default null;
            // alter table clientes add column bairro_responsavel varchar(45) default null;
            // alter table clientes add column cidade_responsavel integer default null;
            // alter table clientes add column complemento_responsavel varchar(60) default null;
            // alter table clientes add column cep_responsavel varchar(9) default null;
            // alter table clientes add column email_responsavel varchar(60) default null;
            // alter table clientes add column telefone_responsavel varchar(15) default null;

            // alter table clientes add column instagram varchar(255) default '';
            // alter table clientes add column facebook varchar(255) default '';
            // alter table clientes add column linkedin varchar(255) default '';
            // alter table clientes add column tiktok varchar(255) default '';
            // alter table clientes add column whatsapp varchar(255) default '';

            // $table->string('cidade', 10)->default("null");

            // alter table clientes add column funcionario_id integer default 0;
            // alter table clientes add column observacao varchar(255) default '';
            // alter table clientes add column contador_email varchar(60) default '';
            // alter table clientes add column data_aniversario varchar(5) default '';
            // alter table clientes add column complemento varchar(100) default '';

            // alter table clientes add column nuvemshop_id varchar(20) default '';
            // alter table clientes add column acessor_id integer default 0;
            // alter table clientes add column imagem varchar(30) default '';
            // alter table clientes add column data_nascimento varchar(10) default '';
            // alter table clientes modify column email varchar(60);

            // alter table clientes add column inativo boolean default 0;

            // alter table clientes add column nome_entrega varchar(80) default '';
            // alter table clientes add column cpf_cnpj_entrega varchar(20) default '';
            // alter table clientes add column rua_entrega varchar(100) default '';
            // alter table clientes add column numero_entrega varchar(10) default '';
            // alter table clientes add column bairro_entrega varchar(30) default '';
            // alter table clientes add column cep_entrega varchar(9) default '';
            // alter table clientes add column cidade_entrega_id integer default null;
            // alter table clientes add column cidade_cobranca_id integer default null;
            // alter table clientes add column valor_cashback decimal(10,2) default 0;

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
        Schema::dropIfExists('clientes');
    }
}
