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
        Schema::create('speds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('data_refrencia', 10)->nullable();
            $table->decimal('saldo_credor', 14, 2)->nullable();
            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            // alter table speds add column data_refrencia varchar(10) default null;
            // alter table speds add column saldo_credor decimal(14,2) default null;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speds');
    }
};
