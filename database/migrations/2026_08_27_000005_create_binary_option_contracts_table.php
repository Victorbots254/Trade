<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('binary_option_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->enum('direction', ['higher', 'lower']);
            $table->decimal('entry_price', 24, 8);
            $table->decimal('strike_price', 24, 8)->nullable();
            $table->decimal('investment_amount', 24, 8);
            $table->decimal('payout_rate', 5, 2)->default(0.88); // 88% return
            $table->decimal('payout_amount', 24, 8);
            $table->integer('duration_seconds'); // 60, 300, 900, 1800, 3600
            $table->timestamp('expires_at');
            $table->enum('status', ['active', 'win', 'loss'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('binary_option_contracts');
    }
};
