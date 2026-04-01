<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specializations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();

        DB::table('specializations')->insert(
            collect([
                'Allergy & Immunology',
                'Anesthesiology',
                'Cardiology',
                'Colorectal Surgery',
                'Critical Care Medicine',
                'Dermatology',
                'Emergency Medicine',
                'Endocrinology',
                'Family Medicine',
                'Gastroenterology',
                'General Surgery',
                'Geriatrics',
                'Hematology',
                'Infectious Disease',
                'Internal Medicine',
                'Nephrology',
                'Neurology',
                'Neurosurgery',
                'Obstetrics & Gynecology',
                'Oncology',
                'Ophthalmology',
                'Orthopedic Surgery',
                'Otolaryngology (ENT)',
                'Pathology',
                'Pediatrics',
                'Physical Medicine & Rehabilitation',
                'Plastic Surgery',
                'Psychiatry',
                'Pulmonology',
                'Radiology',
                'Rheumatology',
                'Thoracic Surgery',
                'Urology',
                'Vascular Surgery',
            ])->values()->map(fn ($name, $i) => [
                'name'       => $name,
                'is_active'  => true,
                'sort_order' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('specializations');
    }
};
