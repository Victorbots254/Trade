<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->after('currency');
            
            // Drop old unique key
            $table->dropUnique(['user_id', 'currency']);
            
            // Add new unique key including is_demo
            $table->unique(['user_id', 'currency', 'is_demo']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'currency', 'is_demo']);
            $table->dropColumn('is_demo');
            $table->unique(['user_id', 'currency']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_demo');
        });
    }
};
