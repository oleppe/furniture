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
        Schema::table('benchmarks_arabic', function (Blueprint $table) {
            $table->decimal('tokens_per_word', 8, 4)->nullable()->after('arabic_tokens_per_1k_words');
            $table->decimal('latency_seconds', 8, 3)->nullable()->after('tokens_per_word');
            $table->decimal('tokens_per_second', 10, 2)->nullable()->after('latency_seconds');
            $table->unsignedTinyInteger('total_score')->nullable()->after('tokens_per_second');
            $table->boolean('is_valid_json')->default(false)->after('total_score');
            $table->decimal('actual_cost', 16, 8)->nullable()->after('is_valid_json');
            $table->json('scores_breakdown')->nullable()->after('actual_cost');
            $table->string('thinking_mode')->nullable()->after('scores_breakdown');
            $table->timestamp('tested_at')->nullable()->after('thinking_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('benchmarks_arabic', function (Blueprint $table) {
            $table->dropColumn([
                'tokens_per_word',
                'latency_seconds',
                'tokens_per_second',
                'total_score',
                'is_valid_json',
                'actual_cost',
                'scores_breakdown',
                'thinking_mode',
                'tested_at',
            ]);
        });
    }
};
