<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For PostgreSQL, we need to alter the type
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE users DROP CONSTRAINT users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('patient', 'admin', 'doctor'))");
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE users DROP CONSTRAINT users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('patient', 'admin'))");
        });
    }
};
