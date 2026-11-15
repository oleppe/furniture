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
        Schema::create('benchmarks_arabic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('models')->cascadeOnDelete();
            $table->decimal('arabic_tokens_per_1k_words', 10, 2)->nullable();
            $table->decimal('token_inflation_ratio', 8, 4)->nullable();
            $table->decimal('effective_arabic_cost_per_1k_words', 14, 6)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benchmarks_arabic');
    }
};
