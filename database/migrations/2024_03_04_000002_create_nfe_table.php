<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nfe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->integer('numero');
            $table->string('chave', 44)->nullable();
            $table->enum('status', ['pendente', 'autorizada', 'rejeitada', 'cancelada'])->default('pendente');
            $table->text('xml')->nullable();
            $table->text('erro')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'numero']);
            $table->unique('chave');
        });
    }

    public function down()
    {
        Schema::dropIfExists('nfe');
    }
}; 