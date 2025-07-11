<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemOrcamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_orcamentos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('orcamento_id')->unsigned();
            $table->foreign('orcamento_id')->references('id')->on('orcamentos')->onDelete('cascade');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos');

            $table->decimal('quantidade', 16,7);
            $table->decimal('valor_original', 16,7);
            $table->decimal('valor_original', 10,2);
            $table->decimal('valor', 16,7);

            $table->decimal('altura', 10,2)->nullable();
            $table->decimal('largura', 10,2)->nullable();
            $table->decimal('profundidade', 10,2)->nullable();
            $table->decimal('acrescimo_perca', 10,2)->nullable();
            $table->decimal('esquerda', 10,2)->nullable();
            $table->decimal('direita', 10,2)->nullable();
            $table->decimal('inferior', 10,2)->nullable();
            $table->decimal('superior', 10,2)->nullable();

            // alter table item_orcamentos add column altura decimal(10,2) default 0;
            // alter table item_orcamentos add column largura decimal(10,2) default 0;
            // alter table item_orcamentos add column profundidade decimal(10,2) default 0;
            // alter table item_orcamentos add column acrescimo_perca decimal(10,2) default 0;
            // alter table item_orcamentos add column esquerda decimal(10,2) default 0;
            // alter table item_orcamentos add column direita decimal(10,2) default 0;
            // alter table item_orcamentos add column inferior decimal(10,2) default 0;
            // alter table item_orcamentos add column superior decimal(10,2) default 0;

            // alter table item_orcamentos modify column quantidade decimal(16,7);
            
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
        Schema::dropIfExists('item_orcamentos');
    }
}
