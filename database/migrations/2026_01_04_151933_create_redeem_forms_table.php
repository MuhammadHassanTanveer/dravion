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
        Schema::create('redeem_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->decimal('redeem', 15, 2);
            $table->decimal('tip', 15, 2)->default(0);
            $table->decimal('paid', 15, 2)->default(0);
            $table->string('customer_tag')->nullable();
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'in process', 'paid'])->default('pending');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redeem_forms');
    }
};
