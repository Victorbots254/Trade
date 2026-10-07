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
        Schema::table('deposits', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('crypto')->after('user_id');
            $table->string('phone_number', 30)->nullable()->after('payment_method');
            $table->decimal('kes_amount', 14, 2)->nullable()->after('amount');
            $table->decimal('exchange_rate', 10, 4)->nullable()->after('kes_amount');
            $table->string('mpesa_receipt', 50)->nullable()->after('tx_hash');
            $table->string('transaction_request_id', 100)->nullable()->after('mpesa_receipt');
            $table->string('merchant_request_id', 100)->nullable()->after('transaction_request_id');
            $table->string('checkout_request_id', 100)->nullable()->after('merchant_request_id');
            $table->string('reference', 100)->nullable()->after('checkout_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'phone_number',
                'kes_amount',
                'exchange_rate',
                'mpesa_receipt',
                'transaction_request_id',
                'merchant_request_id',
                'checkout_request_id',
                'reference',
            ]);
        });
    }
};
