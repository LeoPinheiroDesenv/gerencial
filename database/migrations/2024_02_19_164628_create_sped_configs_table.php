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
        Schema::create('sped_configs', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->string('codigo_conta_analitica', 30)->nullable();
            $table->string('codigo_receita', 30)->nullable();
            $table->boolean('gerar_bloco_k')->default(0);
            $table->integer('layout_bloco_k')->default(0);

            $table->string('codigo_obrigacao', 3)->default('000');
            $table->string('data_vencimento', 2)->default('10');

            // alter table sped_configs add column codigo_receita varchar(30) default null;
            // alter table sped_configs add column gerar_bloco_k boolean default 0;
            // alter table sped_configs add column layout_bloco_k integer default 0;
            // alter table sped_configs add column codigo_obrigacao varchar(3) default '000';
            // alter table sped_configs add column data_vencimento varchar(2) default '10';

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sped_configs');
    }
};
