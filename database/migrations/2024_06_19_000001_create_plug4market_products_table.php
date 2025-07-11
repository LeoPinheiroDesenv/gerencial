<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plug4market_products', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable(); // ID do produto na API Plug4Market
            $table->string('codigo')->unique(); // SKU/Código do produto
            $table->string('descricao');
            $table->string('nome')->nullable(); // Nome do produto
            $table->string('ncm')->nullable();
            $table->string('cfop')->nullable();
            $table->string('unidade')->nullable();
            $table->decimal('valor_unitario', 15, 2);
            $table->decimal('aliquota_icms', 5, 2)->nullable();
            $table->decimal('aliquota_pis', 5, 2)->nullable();
            $table->decimal('aliquota_cofins', 5, 2)->nullable();
            
            // Campos adicionais baseados no código original
            $table->string('marca')->nullable();
            $table->integer('categoria_id')->nullable();
            $table->string('categoria_nome')->nullable();
            $table->integer('largura')->default(10);
            $table->integer('altura')->default(10);
            $table->integer('comprimento')->default(10);
            $table->integer('peso')->default(1);
            $table->integer('estoque')->default(0);
            $table->string('origem')->default('nacional');
            $table->string('ean')->nullable();
            $table->string('modelo')->nullable();
            $table->integer('garantia')->default(12);
            $table->json('imagens')->nullable();
            $table->json('metafields')->nullable();
            $table->json('sales_channels')->nullable();
            
            // Status do produto
            $table->boolean('ativo')->default(true);
            $table->boolean('sincronizado')->default(false);
            $table->timestamp('ultima_sincronizacao')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plug4market_products');
    }
}; 