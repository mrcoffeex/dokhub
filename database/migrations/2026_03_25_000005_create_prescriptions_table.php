<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // RX-2026-0001
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('diagnosis_id')->nullable()->constrained()->nullOnDelete();
            $table->json('medications'); // [{name, dosage, frequency, duration, instructions}]
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'patient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
