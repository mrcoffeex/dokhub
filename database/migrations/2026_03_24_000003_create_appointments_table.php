<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // e.g. APT-2026-0001
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('patient_name');
            $table->string('patient_email');
            $table->string('patient_phone');
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->string('cancellation_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'appointment_date']);
            $table->index('patient_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
