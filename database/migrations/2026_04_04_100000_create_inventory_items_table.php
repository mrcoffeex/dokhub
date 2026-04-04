<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->default('other'); // medicine|equipment|supplies|consumables|other
            $table->string('sku')->nullable();
            $table->string('unit')->default('unit'); // tablets, vials, boxes, pieces ...
            $table->text('description')->nullable();
            $table->integer('current_stock')->default(0);
            $table->integer('min_stock')->default(0);   // low-stock threshold
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
