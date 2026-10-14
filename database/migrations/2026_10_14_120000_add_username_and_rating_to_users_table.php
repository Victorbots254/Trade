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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'p2p_rating')) {
                $table->decimal('p2p_rating', 3, 2)->default(5.00)->after('p2p_completion_rate');
            }
        });

        Schema::table('p2p_ads', function (Blueprint $table) {
            if (!Schema::hasColumn('p2p_ads', 'is_top_trader')) {
                $table->boolean('is_top_trader')->default(false)->after('is_dummy');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('p2p_ads', function (Blueprint $table) {
            if (Schema::hasColumn('p2p_ads', 'is_top_trader')) {
                $table->dropColumn('is_top_trader');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'p2p_rating')) {
                $table->dropColumn('p2p_rating');
            }
            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
        });
    }
};
