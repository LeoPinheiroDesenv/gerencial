<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('plug4market_settings', function (Blueprint $table) {
            $table->string('user_login')->nullable()->after('id');
            $table->string('user_password')->nullable()->after('user_login');
        });
    }

    public function down()
    {
        Schema::table('plug4market_settings', function (Blueprint $table) {
            $table->dropColumn(['user_login', 'user_password']);
        });
    }
}; 