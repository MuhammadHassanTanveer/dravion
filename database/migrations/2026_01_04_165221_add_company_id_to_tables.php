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
        // Add company_id to pages table
        if (!Schema::hasColumn('pages', 'company_id')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('page_name')->constrained()->onDelete('cascade');
            });
        }

        // Add company_id to payment_methods table
        if (!Schema::hasColumn('payment_methods', 'company_id')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('payment_method_name')->constrained()->onDelete('cascade');
            });
        }

        // Add user_id and company_id to deposit_forms table
        if (!Schema::hasColumn('deposit_forms', 'user_id')) {
            Schema::table('deposit_forms', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('remarks')->constrained()->onDelete('cascade');
            });
        }
        if (!Schema::hasColumn('deposit_forms', 'company_id')) {
            Schema::table('deposit_forms', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
            });
        }

        // Add company_id to redeem_forms table
        if (!Schema::hasColumn('redeem_forms', 'company_id')) {
            Schema::table('redeem_forms', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('deposit_forms', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('redeem_forms', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
