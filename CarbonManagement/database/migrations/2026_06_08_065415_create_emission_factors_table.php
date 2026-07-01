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
        Schema::create('emission_factors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emission_standard_id')->constrained('emission_standards')->onDelete('cascade');
            $table->unsignedTinyInteger('scope'); // 1, 2, or 3
            $table->string('category_name'); // Diesel, Petrol, Electricity, etc.
            $table->string('unit'); // Liters, kWh, km, etc.
            $table->decimal('factor', 12, 6);
            $table->string('reference_source')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emission_factors');
    }
};
