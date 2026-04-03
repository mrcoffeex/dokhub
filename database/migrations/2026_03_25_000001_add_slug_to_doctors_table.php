<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Populate slugs using raw DB queries — never use Eloquent models in
        // migrations because the model's current traits (e.g. SoftDeletes) may
        // reference columns that don't exist yet at this point in the chain.
        $doctors = DB::table('doctors')->select('id', 'name')->get();

        foreach ($doctors as $doctor) {
            $base = Str::slug($doctor->name);
            $slug = $base;
            $i    = 2;

            while (DB::table('doctors')->where('slug', $slug)->where('id', '!=', $doctor->id)->exists()) {
                $slug = $base . '-' . $i++;
            }

            DB::table('doctors')->where('id', $doctor->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
