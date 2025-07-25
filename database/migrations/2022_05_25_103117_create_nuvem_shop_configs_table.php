<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNuvemShopConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nuvem_shop_configs', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->string('client_id', 10)->default('');
            $table->string('client_secret', 150)->default('');
            $table->string('email', 80)->default('');
            $table->integer('natureza_padrao')->nullable();
            $table->string('forma_pagamento_padrao', 2)->nullable();

            // alter table nuvem_shop_configs add column natureza_padrao integer default null;
            // alter table nuvem_shop_configs add column forma_pagamento_padrao varchar(2) default null;

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
        Schema::dropIfExists('nuvem_shop_configs');
    }
}
