<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryConfigGaleriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_config_galerias', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('config_id')->unsigned();
            $table->foreign('config_id')->references('id')->on('delivery_configs')
            ->onDelete('cascade');

            $table->string('imagem', 30);
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
        Schema::dropIfExists('delivery_config_galerias');
    }
}
