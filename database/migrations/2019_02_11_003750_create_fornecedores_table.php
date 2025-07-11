<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFornecedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fornecedors', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('razao_social', 100);
            $table->string('nome_fantasia', 80);
            $table->string('cpf_cnpj', 19);
            $table->string('ie_rg', 20);
            $table->string('rua', 80);
            $table->string('numero', 10);
            $table->string('bairro', 50);
            $table->string('telefone', 20);
            $table->string('complemento', 100)->default('');
            $table->string('celular', 20)->default("00 00000 0000");
            $table->string('email', 40)->default(null);
            $table->string('cep', 10)->default(null);

            $table->string('pix', 40)->default('');
            $table->enum('tipo_pix', ['cpf', 'cnpj', 'email', 'telefone', 'chave aleatória'])->default('cpf');

            $table->integer('cidade_id')->unsigned();
            $table->foreign('cidade_id')->references('id')->on('cidades')->onDelete('cascade');
            $table->integer('contribuinte');

            $table->integer('cod_pais')->default(1058);
            $table->string('id_estrangeiro', 30)->default("");

            // alter table fornecedors add column contribuinte integer default 1;

            // alter table fornecedors add column pix varchar(40) default '';
            // alter table fornecedors add column tipo_pix enum('cpf', 'cnpj', 'email', 'telefone', 'chave aleatória') default 'cpf';
            // alter table fornecedors add column complemento varchar(100) default '';

            // alter table fornecedors add column cod_pais integer default 1058;
            // alter table fornecedors add column id_estrangeiro varchar(30) default '';

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
        Schema::dropIfExists('fornecedors');
    }
}
