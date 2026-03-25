<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing string values to JSON arrays, then change the column type
        DB::statement("
            ALTER TABLE doctors
            ALTER COLUMN specialization TYPE json
            USING to_json(ARRAY[specialization])
        ");
    }

    public function down(): void
    {
        // Convert back: extract first element of JSON array to string
        DB::statement("
            ALTER TABLE doctors
            ALTER COLUMN specialization TYPE varchar(255)
            USING specialization->>0
        ");
    }
};
