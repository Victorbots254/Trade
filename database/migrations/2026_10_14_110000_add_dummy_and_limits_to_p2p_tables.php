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
            if (!Schema::hasColumn('users', 'is_dummy')) {
                $table->boolean('is_dummy')->default(false)->after('is_p2p_merchant');
            }
        });

        Schema::table('p2p_ads', function (Blueprint $table) {
            if (!Schema::hasColumn('p2p_ads', 'is_dummy')) {
                $table->boolean('is_dummy')->default(false)->after('status');
            }
            if (!Schema::hasColumn('p2p_ads', 'is_limited')) {
                $table->boolean('is_limited')->default(false)->after('is_dummy');
            }
            if (!Schema::hasColumn('p2p_ads', 'requirement_text')) {
                $table->string('requirement_text')->nullable()->after('is_limited');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('p2p_ads', function (Blueprint $table) {
            if (Schema::hasColumn('p2p_ads', 'requirement_text')) {
                $table->dropColumn('requirement_text');
            }
            if (Schema::hasColumn('p2p_ads', 'is_limited')) {
                $table->dropColumn('is_limited');
            }
            if (Schema::hasColumn('p2p_ads', 'is_dummy')) {
                $table->dropColumn('is_dummy');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_dummy')) {
                $table->dropColumn('is_dummy');
            }
        });
    }
};
