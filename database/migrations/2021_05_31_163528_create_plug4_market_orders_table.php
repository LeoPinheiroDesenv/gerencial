<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlug4MarketOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plug4market_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id')->nullable();
            $table->string('order_number')->nullable();
            $table->integer('marketplace')->nullable();
            $table->integer('status')->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->string('shipping_name')->nullable();
            $table->string('payment_name')->nullable();
            $table->decimal('interest', 10, 2)->nullable();
            $table->decimal('total_commission', 10, 2)->nullable();
            $table->string('type_billing')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();

            // Dados de entrega (shipping)
            $table->string('shipping_recipient_name')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_street')->nullable();
            $table->string('shipping_street_number')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_street_complement')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_district')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_zip_code')->nullable();
            $table->string('shipping_ibge')->nullable();

            // Dados de cobrança (billing)
            $table->string('billing_name')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_document_id')->nullable();
            $table->string('billing_state_registration_id')->nullable();
            $table->string('billing_street')->nullable();
            $table->string('billing_street_number')->nullable();
            $table->string('billing_street_complement')->nullable();
            $table->string('billing_district')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_zip_code')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('billing_gender')->nullable();
            $table->date('billing_date_of_birth')->nullable();
            $table->boolean('billing_tax_payer')->nullable();
            $table->string('billing_ibge')->nullable();

            // Relacionamentos
            $table->integer('cliente_id')->unsigned()->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null');

            // Controle
            $table->boolean('sincronizado')->default(false);
            $table->timestamp('ultima_sincronizacao')->nullable();
            $table->json('raw_data')->nullable();

            // Campos de nota fiscal (invoice)
            $table->string('invoice_number')->nullable();
            $table->string('invoice_key')->nullable();
            $table->dateTime('invoice_date')->nullable();
            $table->string('invoice_url')->nullable();
            $table->string('invoice_status')->nullable();
            $table->text('invoice_payload')->nullable();

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
        Schema::dropIfExists('plug4market_orders');
    }
}