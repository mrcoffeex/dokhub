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
        Schema::table('doctors', function (Blueprint $table) {
            // Stores the PayMongo checkout session ID during an in-flight payment.
            // Used server-side so we never trust the session_id URL parameter for the API lookup.
            $table->string('pending_checkout_session_id')->nullable()->after('last_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('pending_checkout_session_id');
        });
    }
};
