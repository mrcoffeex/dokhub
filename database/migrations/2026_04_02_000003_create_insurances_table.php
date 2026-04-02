<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();

        DB::table('insurances')->insert(
            collect([
                'PhilHealth',
                'Maxicare',
                'MediCard',
                'Intellicare',
                'Pacific Cross',
                'FWD',
                'Careplus',
                'Sterling',
                'Cocolife',
                'PruLife',
            ])->values()->map(fn ($name, $i) => [
                'name'       => $name,
                'is_active'  => true,
                'sort_order' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};
