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
        // 1. Add P2P Merchant and Moderator fields to users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_moderator')->default(false)->after('is_admin');
            $table->boolean('is_p2p_merchant')->default(false)->after('is_moderator');
            $table->string('p2p_merchant_name')->nullable()->after('is_p2p_merchant');
            $table->text('p2p_payment_details')->nullable()->after('p2p_merchant_name');
            $table->decimal('p2p_completion_rate', 5, 2)->default(100.00)->after('p2p_payment_details');
            $table->unsignedInteger('p2p_completed_trades')->default(0)->after('p2p_completion_rate');
        });

        // 2. Create P2P Ads Table
        Schema::create('p2p_ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Merchant
            $table->enum('type', ['sell', 'buy'])->default('sell'); // 'sell' = user buys from merchant, 'buy' = user sells to merchant
            $table->string('asset')->default('USDT');
            $table->string('fiat')->default('KES');
            $table->decimal('price', 14, 2); // Price in Fiat per 1 Asset (e.g. 132.50 KES)
            $table->decimal('total_amount', 18, 4);
            $table->decimal('available_amount', 18, 4);
            $table->decimal('min_limit', 14, 2); // Min fiat transaction limit
            $table->decimal('max_limit', 14, 2); // Max fiat transaction limit
            $table->json('payment_methods'); // Array e.g. ["mpesa", "bank_transfer"]
            $table->json('payment_details')->nullable(); // Saved payment details snapshot
            $table->text('terms')->nullable();
            $table->text('auto_reply')->nullable();
            $table->unsignedInteger('time_limit_minutes')->default(15);
            $table->enum('status', ['active', 'paused', 'closed'])->default('active');
            $table->timestamps();
        });

        // 3. Create P2P Orders Table
        Schema::create('p2p_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('ad_id')->constrained('p2p_ads')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('crypto_amount', 18, 4); // Crypto held in escrow
            $table->decimal('fiat_amount', 14, 2); // Total fiat to transfer
            $table->decimal('price', 14, 2); // Locked rate
            $table->string('payment_method'); // Selected payment method (e.g. 'mpesa')
            $table->json('payment_details')->nullable(); // Snapshot of seller's payment instructions
            $table->enum('status', [
                'pending_payment',
                'paid',
                'completed',
                'cancelled',
                'disputed'
            ])->default('pending_payment');
            $table->timestamp('expires_at');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by')->nullable();
            $table->timestamp('disputed_at')->nullable();
            $table->foreignId('disputed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('dispute_reason')->nullable();
            $table->timestamps();
        });

        // 4. Create P2P Messages Table (In-Order Chat)
        Schema::create('p2p_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('p2p_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message')->nullable();
            $table->string('attachment_path')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p2p_messages');
        Schema::dropIfExists('p2p_orders');
        Schema::dropIfExists('p2p_ads');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_moderator',
                'is_p2p_merchant',
                'p2p_merchant_name',
                'p2p_payment_details',
                'p2p_completion_rate',
                'p2p_completed_trades',
            ]);
        });
    }
};
