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
        Schema::create('cash_back_configs', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->decimal('valor_percentual', 5, 2);
            $table->integer('dias_expiracao');
            $table->decimal('valor_minimo_venda', 10, 2);
            $table->decimal('percentual_maximo_venda', 10, 2);
            $table->text('mensagem_padrao_whatsapp');
            $table->text('mensagem_automatica_5_dias');
            $table->text('mensagem_automatica_1_dia');

            // alter table cash_back_configs modify column mensagem_padrao_whatsapp text;
            // alter table cash_back_configs add column mensagem_automatica_5_dias text;
            // alter table cash_back_configs add column mensagem_automatica_1_dia text;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_back_configs');
    }
};
