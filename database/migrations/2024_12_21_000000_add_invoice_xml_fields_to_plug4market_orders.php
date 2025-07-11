<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('plug4market_orders', function (Blueprint $table) {
            // Primeiro, adicionar campos básicos de nota fiscal se não existirem
            if (!Schema::hasColumn('plug4market_orders', 'invoice_number')) {
                $table->string('invoice_number')->nullable();
            }
            if (!Schema::hasColumn('plug4market_orders', 'invoice_key')) {
                $table->string('invoice_key')->nullable();
            }
            if (!Schema::hasColumn('plug4market_orders', 'invoice_date')) {
                $table->timestamp('invoice_date')->nullable();
            }
            if (!Schema::hasColumn('plug4market_orders', 'invoice_url')) {
                $table->string('invoice_url')->nullable();
            }
            if (!Schema::hasColumn('plug4market_orders', 'invoice_status')) {
                $table->string('invoice_status')->nullable();
            }
            if (!Schema::hasColumn('plug4market_orders', 'invoice_payload')) {
                $table->text('invoice_payload')->nullable();
            }
            
            // Campos para XML da nota fiscal
            $table->longText('invoice_xml')->nullable();
            $table->string('invoice_xml_filename')->nullable();
            $table->string('invoice_xml_path')->nullable();
            $table->timestamp('invoice_xml_downloaded_at')->nullable();
            $table->string('invoice_xml_status')->default('pending'); // pending, downloaded, error
            $table->text('invoice_xml_error_message')->nullable();
            
            // Campos adicionais para controle da nota fiscal
            $table->string('invoice_series')->nullable();
            $table->string('invoice_model')->nullable(); // 55=NFe, 65=NFCe
            $table->string('invoice_environment')->nullable(); // 1=Produção, 2=Homologação
            $table->string('invoice_protocol')->nullable();
            $table->timestamp('invoice_protocol_date')->nullable();
            $table->decimal('invoice_total_products', 15, 2)->nullable();
            $table->decimal('invoice_total_taxes', 15, 2)->nullable();
            $table->decimal('invoice_total_shipping', 15, 2)->nullable();
            $table->decimal('invoice_total_discount', 15, 2)->nullable();
            $table->decimal('invoice_total_final', 15, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('plug4market_orders', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_xml',
                'invoice_xml_filename',
                'invoice_xml_path',
                'invoice_xml_downloaded_at',
                'invoice_xml_status',
                'invoice_xml_error_message',
                'invoice_series',
                'invoice_model',
                'invoice_environment',
                'invoice_protocol',
                'invoice_protocol_date',
                'invoice_total_products',
                'invoice_total_taxes',
                'invoice_total_shipping',
                'invoice_total_discount',
                'invoice_total_final'
            ]);
        });
    }
}; 