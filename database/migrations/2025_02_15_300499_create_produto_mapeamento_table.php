<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutoMapeamentoTable extends Migration
{
    public function up()
    {
        Schema::create('produto_mapeamento', function (Blueprint $table) {
            $table->id();
            $table->string('id_xml', 50); // Código do produto no XML da nota
            $table->string('codBarras_xml', 50)->nullable(); // Código de barras que veio no XML
            $table->string('id_fornecedor', 50); // Código do fornecedor
            $table->unsignedBigInteger('id_produto'); // ID do produto já cadastrado no sistema
            $table->string('codBarras_produto', 50); // Código de barras do produto cadastrado
            $table->unsignedBigInteger('fornecedor_id'); // ID do fornecedor
            $table->unsignedBigInteger('empresa_id'); // ID da empresa
            $table->unsignedBigInteger('filial_id')->nullable(); // ID da filial (se houver)
            $table->timestamps();

            // Chaves estrangeiras
            $table->foreign('id_produto')->references('id')->on('produtos');
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores');
            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('filial_id')->references('id')->on('filiais');
        });
    }

    public function down()
    {
        Schema::dropIfExists('produto_mapeamento');
    }
}

