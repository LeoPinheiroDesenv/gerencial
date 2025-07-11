<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemessaReferenciaNvesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('remessa_referencia_nves', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('remessa_id')->unsigned();
            $table->foreign('remessa_id')->references('id')->on('remessa_nves')
            ->onDelete('cascade');

            $table->string('chave', 44);

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
        Schema::dropIfExists('remessa_referencia_nves');
    }
}
