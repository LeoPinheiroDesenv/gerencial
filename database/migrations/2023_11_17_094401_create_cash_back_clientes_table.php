<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_back_clientes', function (Blueprint $table) {
            $table->id();

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')->on('clientes')
            ->onDelete('cascade');

            $table->enum('tipo', ['venda', 'pdv']);
            $table->integer('venda_id');
            $table->decimal('valor_venda', 16, 7);
            $table->decimal('valor_credito', 16, 7);
            $table->decimal('valor_percentual', 5, 2);
            $table->date('data_expiracao');
            $table->boolean('status')->default(1);
            $table->boolean('status_mensagem_5_dias')->default(0);
            $table->boolean('status_mensagem_1_dia')->default(0);

            // alter table cash_back_clientes add column data_expiracao date default null;
            // alter table cash_back_clientes add column status_mensagem_5_dias boolean default 0;
            // alter table cash_back_clientes add column status_mensagem_1_dia boolean default 0;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_back_clientes');
    }
};
