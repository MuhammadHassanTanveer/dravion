<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing unique constraint on name and guard_name
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name']);
        });

        // Add a new unique constraint that includes company_id
        // This allows the same role name for different companies
        // Note: In MySQL, NULL values are considered distinct in unique constraints,
        // so multiple rows with NULL company_id and same name/guard_name are allowed
        // We'll handle super admin uniqueness separately if needed
        Schema::table('roles', function (Blueprint $table) {
            $table->unique(['name', 'guard_name', 'company_id'], 'roles_name_guard_company_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new unique constraint
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_name_guard_company_unique');
        });

        // Restore the original unique constraint
        Schema::table('roles', function (Blueprint $table) {
            $table->unique(['name', 'guard_name']);
        });
    }
};
