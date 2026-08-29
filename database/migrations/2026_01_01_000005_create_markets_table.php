<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20)->unique(); // e.g. BTC/USDT
            $table->string('base_currency', 10);
            $table->string('quote_currency', 10);
            $table->decimal('min_order_size', 24, 8)->default(0.0001);
            $table->integer('price_precision')->default(2);
            $table->integer('quantity_precision')->default(4);
            $table->string('status', 20)->default('active');
            $table->decimal('last_price', 24, 8)->default(0);
            $table->decimal('change_24h', 8, 2)->default(0);
            $table->decimal('high_24h', 24, 8)->default(0);
            $table->decimal('low_24h', 24, 8)->default(0);
            $table->decimal('volume_24h', 24, 8)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
