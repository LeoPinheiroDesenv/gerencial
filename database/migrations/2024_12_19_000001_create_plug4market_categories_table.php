<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plug4market_categories', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->unique(); // ID da categoria na API Plug4Market
            $table->string('name'); // Nome da categoria
            $table->text('description')->nullable(); // Descrição da categoria
            $table->foreignId('parent_id')->nullable()->constrained('plug4market_categories')->onDelete('cascade'); // Categoria pai (hierarquia)
            $table->string('external_parent_id')->nullable(); // ID da categoria pai na API
            $table->integer('level')->default(0); // Nível na hierarquia (0 = raiz)
            $table->string('path')->nullable(); // Caminho completo da categoria (ex: "Eletrônicos > Smartphones")
            
            // Status da categoria
            $table->boolean('is_active')->default(true);
            $table->boolean('sincronizado')->default(false);
            $table->timestamp('ultima_sincronizacao')->nullable();
            
            // Dados brutos da API
            $table->json('raw_data')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['external_id']);
            $table->index(['parent_id']);
            $table->index(['is_active']);
            $table->index(['sincronizado']);
            $table->index(['level']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('plug4market_categories');
    }
}; 