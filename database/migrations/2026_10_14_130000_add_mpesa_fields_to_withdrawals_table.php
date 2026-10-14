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
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->string('bep20_address')->nullable()->change();
            $table->string('payment_method')->default('bep20')->after('amount');
            $table->string('mpesa_phone')->nullable()->after('bep20_address');
            $table->string('mpesa_name')->nullable()->after('mpesa_phone');
            $table->string('mpesa_type')->default('phone')->nullable()->after('mpesa_name');
            $table->string('mpesa_account_number')->nullable()->after('mpesa_type');
            $table->decimal('amount_kes', 20, 2)->nullable()->after('mpesa_account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'mpesa_phone',
                'mpesa_name',
                'mpesa_type',
                'mpesa_account_number',
                'amount_kes',
            ]);
            $table->string('bep20_address')->nullable(false)->change();
        });
    }
};
