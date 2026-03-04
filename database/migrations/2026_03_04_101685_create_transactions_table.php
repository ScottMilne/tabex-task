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
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('seller_id');
            $table->string('customer_id');
            $table->string('idempotency_key')->unique();
            $table->integer('amount');
            $table->char('currency', 3);
            $table->enum('payment_provider', ['stripe', 'paypal', 'ideal']);
            $table->integer('payment_provider_fee');
            $table->decimal('commission_rate', 5, 4);
            $table->integer('commission_amount');
            $table->integer('net_amount');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('sellers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
