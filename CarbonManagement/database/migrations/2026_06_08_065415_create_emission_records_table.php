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
        Schema::create('emission_records', function (Blueprint $table) {
            $table->id();
            $table->string('reporting_period'); // e.g. "January 2024"
            $table->foreignId('emission_standard_id')->constrained('emission_standards');
            $table->decimal('scope1_total', 15, 4)->default(0.0000);
            $table->decimal('scope2_total', 15, 4)->default(0.0000);
            $table->decimal('scope3_total', 15, 4)->default(0.0000);
            $table->decimal('total_emission', 15, 4)->default(0.0000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emission_records');
    }
};
