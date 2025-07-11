<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('recibo_conta_rec_conta_recebers', function (Blueprint $table) {
            $table->id();
            
            // FK para recibo
            $table->unsignedBigInteger('recibo_receber_id');
            $table->foreign('recibo_receber_id')
                  ->references('id')
                  ->on('recibo_conta_rec')
                  ->onDelete('cascade');
            
            // FK para conta
            $table->unsignedBigInteger('conta_receber_id');
            $table->foreign('conta_receber_id')
                  ->references('id')
                  ->on('conta_recebers')
                  ->onDelete('cascade');
            
            $table->timestamps();
        });        
    }

    public function down()
    {
        Schema::dropIfExists('recibo_conta_rec_conta_recebers');
    }
};

