<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('plug4market_orders', function (Blueprint $table) {
            // Campos para arquivo da nota fiscal
            $table->string('invoice_file_name')->nullable()->after('invoice_xml_error_message');
            $table->string('invoice_file_path')->nullable()->after('invoice_file_name');
            $table->string('invoice_file_size')->nullable()->after('invoice_file_path');
            $table->string('invoice_file_type')->nullable()->after('invoice_file_size');
            $table->timestamp('invoice_file_uploaded_at')->nullable()->after('invoice_file_type');
            
            // Manter o campo invoice_url para compatibilidade, mas torná-lo nullable
            if (Schema::hasColumn('plug4market_orders', 'invoice_url')) {
                $table->string('invoice_url')->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('plug4market_orders', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_file_name',
                'invoice_file_path', 
                'invoice_file_size',
                'invoice_file_type',
                'invoice_file_uploaded_at'
            ]);
        });
    }
}; 