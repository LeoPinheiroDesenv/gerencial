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
        Schema::create('difals', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->string('uf', 2);
            $table->string('cfop', 4);
            $table->decimal('pICMSUFDest', 6, 2);
            $table->decimal('pICMSInter', 6, 2);
            $table->decimal('pICMSInterPart', 6, 2);
            $table->decimal('pFCPUFDest', 6, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('difals');
    }
};
