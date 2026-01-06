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
        Schema::table('redeem_forms', function (Blueprint $table) {
            // Drop the old enum column
            $table->dropColumn('payment_method');
        });

        Schema::table('redeem_forms', function (Blueprint $table) {
            // Add foreign key column
            $table->foreignId('payment_method_id')->nullable()->after('status')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('redeem_forms', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');
        });

        Schema::table('redeem_forms', function (Blueprint $table) {
            $table->enum('payment_method', ['Chime', 'Cashapp'])->nullable()->after('status');
        });
    }
};
