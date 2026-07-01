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
        Schema::create('emission_record_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emission_record_id')->constrained('emission_records')->onDelete('cascade');
            $table->unsignedTinyInteger('scope'); // 1, 2, or 3
            $table->string('category_name'); // Diesel, Petrol, Electricity, etc.
            $table->decimal('activity_value', 15, 4);
            $table->string('unit');
            $table->decimal('emission_factor', 12, 6);
            $table->decimal('emission_result', 15, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emission_record_details');
    }
};
