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
        if (!Schema::hasTable('plug4market_tokens')) {
            Schema::create('plug4market_tokens', function (Blueprint $table) {
                $table->id();
                $table->text('access_token')->nullable();
                $table->text('refresh_token')->nullable();
                $table->string('token_type')->nullable();
                $table->string('scope')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plug4market_tokens');
    }
};
