<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plug4market_settings', function (Blueprint $table) {
            $table->id();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->string('base_url')->default('https://api.sandbox.plug4market.com.br');
            $table->boolean('sandbox')->default(true);
            $table->string('seller_id')->default('7');
            $table->string('software_house_cnpj')->default('04026307000112');
            $table->string('store_cnpj')->default('04026307000112');
            $table->string('user_id')->default('89579395-cc99-4a2a-8bb9-8e2165d7611d');
            $table->timestamp('last_test_at')->nullable();
            $table->boolean('last_test_success')->default(false);
            $table->text('last_test_message')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plug4market_settings');
    }
}; 