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
        Schema::create('model_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('models')->cascadeOnDelete();
            $table->decimal('input_cost_per_1m', 14, 6)->nullable();
            $table->decimal('output_cost_per_1m', 14, 6)->nullable();
            $table->unsignedBigInteger('context_window')->nullable();
            $table->unsignedInteger('max_output_tokens')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_specs');
    }
};
