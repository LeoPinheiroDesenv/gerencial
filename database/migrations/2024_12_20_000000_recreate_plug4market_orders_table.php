<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop existing tables if they exist
        Schema::dropIfExists('plug4market_order_items');
        Schema::dropIfExists('plug4market_orders');

        Schema::create('plug4market_orders', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->unique(); // ID do pedido na API Plug4Market
            $table->string('order_number')->nullable(); // Número do pedido
            
            // Campos principais do pedido
            $table->integer('marketplace')->default(7); // ID do marketplace
            $table->integer('status')->default(1); // Status do pedido (1=pending, 2=confirmed, etc.)
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->string('shipping_name')->nullable(); // Nome do frete (ex: SEDEX)
            $table->string('payment_name')->nullable(); // Nome do pagamento (ex: Cartão Crédito)
            $table->decimal('interest', 15, 2)->default(0);
            $table->decimal('total_commission', 15, 2)->default(0);
            $table->string('type_billing')->default('PF'); // PF ou PJ
            $table->decimal('total_amount', 15, 2)->default(0);
            
            // Dados de entrega (shipping)
            $table->string('shipping_recipient_name')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_street')->nullable();
            $table->string('shipping_street_number')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_street_complement')->nullable();
            $table->string('shipping_country')->default('BR');
            $table->string('shipping_district')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_zip_code')->nullable();
            $table->string('shipping_ibge')->nullable();
            
            // Dados de cobrança (billing)
            $table->string('billing_name')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_document_id')->nullable(); // CPF/CNPJ
            $table->string('billing_state_registration_id')->nullable(); // Inscrição estadual
            $table->string('billing_street')->nullable();
            $table->string('billing_street_number')->nullable();
            $table->string('billing_street_complement')->nullable();
            $table->string('billing_district')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_country')->default('BR');
            $table->string('billing_zip_code')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('billing_gender')->nullable();
            $table->date('billing_date_of_birth')->nullable();
            $table->boolean('billing_tax_payer')->default(false);
            $table->string('billing_ibge')->nullable();
            
            // Relacionamento com cliente local
            $table->unsignedBigInteger('cliente_id')->nullable();
            //$table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null');
            
            // Campos de controle
            $table->boolean('sincronizado')->default(false);
            $table->timestamp('ultima_sincronizacao')->nullable();
            $table->json('raw_data')->nullable(); // Dados brutos da API
            
            $table->timestamps();
            
            // Índices
            $table->index(['external_id']);
            $table->index(['order_number']);
            $table->index(['status']);
            $table->index(['marketplace']);
            $table->index(['sincronizado']);
            $table->index(['cliente_id']);
            $table->index(['created_at']);
        });

        // Tabela de itens do pedido
        Schema::create('plug4market_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('plug4market_orders')->onDelete('cascade');
            $table->string('sku'); // SKU do produto
            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 2)->default(0); // Preço unitário
            $table->decimal('total_price', 15, 2)->default(0); // Preço total do item
            
            // Relacionamento com produto local
            $table->unsignedBigInteger('product_id')->nullable();
            //$table->foreign('product_id')->references('id')->on('plug4market_products')->onDelete('set null');
            
            $table->timestamps();
            
            // Índices
            $table->index(['order_id']);
            $table->index(['sku']);
            $table->index(['product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('plug4market_order_items');
        Schema::dropIfExists('plug4market_orders');
    }
}; 