<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filials', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->string('descricao', 50);

            $table->string('razao_social', 100);
            $table->string('nome_fantasia', 80);
            $table->string('cnpj', 19);
            $table->string('ie', 20);
            $table->string('logradouro', 80);
            $table->string('complemento', 100)->default('');

            $table->string('numero', 10);
            $table->string('bairro', 50);
            $table->string('fone', 20);
            $table->string('cep', 10);
            $table->string('pais', 20);
            $table->string('email', 60);
            $table->string('municipio', 30);
            $table->integer('codPais');
            $table->integer('codMun');
            $table->char('UF', 2);

            $table->integer('nat_op_padrao')->nullable();
            $table->integer('ambiente');
            $table->string('cUF', 2);
            $table->string('numero_serie_nfe', 3);
            $table->string('numero_serie_nfce', 3);
            $table->string('numero_serie_cte', 3);
            $table->string('numero_serie_mdfe', 3);
            $table->string('numero_serie_nfse', 3);
            $table->integer('ultimo_numero_nfe');
            $table->integer('ultimo_numero_nfce');
            $table->integer('ultimo_numero_cte');
            $table->integer('ultimo_numero_mdfe');
            $table->integer('ultimo_numero_nfse')->default(0);
            $table->string('regime_tributacao', 3);
            $table->string('csc', 60);
            $table->string('csc_id', 10);

            $table->string('inscricao_municipal', 25)->nullable();
            $table->string('aut_xml', 20)->nullable();
            $table->string('logo', 100)->nullable();

            $table->binary('arquivo_certificado')->nullable();
            $table->string('senha_certificado', 30)->nullable();
            $table->boolean('status')->default(1);

            // alter table filials add column status boolean default 0;
            // alter table filials add column senha_certificado varchar(30) default null;
            // alter table filials add column arquivo_certificado blob default null;

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
        Schema::dropIfExists('filials');
    }
}
