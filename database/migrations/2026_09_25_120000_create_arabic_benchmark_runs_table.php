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
        Schema::create('arabic_benchmark_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->nullable()->constrained('models')->nullOnDelete();
            $table->string('openrouter_model_id')->index();
            $table->string('thinking_mode')->default('none');
            $table->string('thinking_level')->nullable();
            $table->unsignedInteger('reasoning_tokens')->nullable();
            $table->decimal('tokens_per_word', 8, 4)->default(0.0000);
            $table->unsignedInteger('arabic_words_count')->default(0);
            $table->decimal('latency_seconds', 8, 3)->default(0.000);
            $table->decimal('tokens_per_second', 10, 2)->default(0.00);
            $table->boolean('is_valid_json')->default(false);
            $table->unsignedTinyInteger('total_score')->default(0);
            $table->decimal('score_percentage', 5, 2)->default(0.00);
            $table->boolean('math_score')->default(false);
            $table->boolean('syntax_score')->default(false);
            $table->boolean('dialect_score')->default(false);
            $table->boolean('extraction_score')->default(false);
            $table->boolean('constraints_score')->default(false);
            $table->json('scores_breakdown')->nullable();
            $table->decimal('actual_cost', 16, 8)->default(0.00000000);
            $table->decimal('input_cost_per_1m', 14, 6)->default(0.000000);
            $table->decimal('output_cost_per_1m', 14, 6)->default(0.000000);
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->longText('raw_response')->nullable();
            $table->longText('reasoning_content')->nullable();
            $table->json('parsed_json')->nullable();
            $table->string('status')->default('completed');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arabic_benchmark_runs');
    }
};
