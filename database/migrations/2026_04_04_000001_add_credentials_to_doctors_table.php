<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->json('education')->nullable()->after('languages');
            $table->json('affiliations')->nullable()->after('education');
            $table->json('certifications')->nullable()->after('affiliations');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['education', 'affiliations', 'certifications']);
        });
    }
};
