<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTributacaoUfsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tributacao_ufs', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos')
            ->onDelete('cascade');

            $table->string('uf', 2);
            $table->decimal('percentual_icms', 5, 2);
            $table->decimal('percentual_fcp', 5, 2);
            $table->decimal('percentual_icms_interno', 5, 2);
            $table->decimal('percentual_red_bc', 5, 2)->nullable();

            // alter table tributacao_ufs add column percentual_red_bc decimal(5,2) default null;
            // alter table tributacao_ufs add column percentual_fcp decimal(5,2) default null;
            // alter table tributacao_ufs add column percentual_icms_interno decimal(5,2) default null;
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
        Schema::dropIfExists('tributacao_ufs');
    }
}
