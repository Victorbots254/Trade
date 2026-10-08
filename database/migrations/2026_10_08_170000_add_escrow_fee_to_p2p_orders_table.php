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
        Schema::table('p2p_orders', function (Blueprint $table) {
            $table->decimal('escrow_fee', 16, 4)->default(0.0000)->after('crypto_amount');
        });

        // Ensure all existing USDT/USD ads adhere strictly to 1:1 settlement parity
        \Illuminate\Support\Facades\DB::table('p2p_ads')
            ->whereIn('fiat', ['USDT', 'USD'])
            ->update(['price' => 1.00]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('p2p_orders', function (Blueprint $table) {
            $table->dropColumn('escrow_fee');
        });
    }
};
