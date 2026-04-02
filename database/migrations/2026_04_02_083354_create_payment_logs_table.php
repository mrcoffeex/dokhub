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
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('checkout_session_id')->unique(); // cs_… idempotency key
            $table->string('billing_period', 20);           // monthly | yearly
            $table->unsignedInteger('amount');               // centavos
            $table->string('status', 30)->default('completed');
            $table->string('source', 30)->default('return_url'); // return_url | webhook
            $table->timestamp('pro_expires_at')->nullable(); // resulting expiry after activation
            $table->timestamps();

            $table->index('doctor_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
