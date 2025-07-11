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
        Schema::create('conta_empresas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('nome', 50);
            $table->string('banco', 50)->nullable();
            $table->string('agencia', 10)->nullable();
            $table->string('conta', 10)->nullable();

            $table->integer('plano_conta_id')->nullable();
            $table->decimal('saldo', 16, 2)->nullable();
            $table->decimal('saldo_inicial', 16, 2)->nullable();
            $table->boolean('status')->default(0);

            // alter table conta_empresas add column saldo_inicial decimal(16,2) default null;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conta_empresas');
    }
};
