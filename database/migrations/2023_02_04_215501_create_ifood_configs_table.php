<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIfoodConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ifood_configs', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->string('clientId', 100);
            $table->string('clientSecret', 200);
            $table->enum('grantType', ['authorization_code', 'client_credentials', 'refresh_token']);
            $table->string('userCode', 10);
            $table->string('authorizationCode', 10);
            $table->string('authorizationCodeVerifier', 150);
            $table->string('verificationUrlComplete', 255);

            $table->text('accessToken');
            $table->text('refreshToken');
            $table->string('merchantId', 40);

            $table->string('catalogId', 100);
            
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
        Schema::dropIfExists('ifood_configs');
    }
}
