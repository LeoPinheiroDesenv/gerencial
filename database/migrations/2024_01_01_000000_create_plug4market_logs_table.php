<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlug4MarketLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plug4market_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // 'test_connection', 'token_info', 'product_sync', 'error', etc.
            $table->string('status'); // 'success', 'error', 'warning', 'info'
            $table->text('message');
            $table->json('details')->nullable(); // Additional data like API responses, errors, etc.
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->integer('execution_time_ms')->nullable(); // Time taken for the operation
            $table->timestamps();
            
            $table->index(['action', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plug4market_logs');
    }
} 