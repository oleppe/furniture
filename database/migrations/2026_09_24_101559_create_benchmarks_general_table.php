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
        Schema::create('benchmarks_general', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('models')->cascadeOnDelete();
            $table->decimal('elo_rating', 8, 2)->nullable();
            $table->decimal('mmlu_score', 5, 2)->nullable();
            $table->decimal('code_eval_score', 5, 2)->nullable();
            $table->decimal('math_score', 5, 2)->nullable();
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benchmarks_general');
    }
};
