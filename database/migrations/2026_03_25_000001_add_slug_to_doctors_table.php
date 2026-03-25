<?php

use App\Models\Doctor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Populate slugs for any existing records
        foreach (Doctor::all() as $doctor) {
            $base = Str::slug($doctor->name);
            $slug = $base;
            $i = 2;

            while (Doctor::where('slug', $slug)->where('id', '!=', $doctor->id)->exists()) {
                $slug = $base . '-' . $i++;
            }

            $doctor->slug = $slug;
            $doctor->saveQuietly();
        }
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
