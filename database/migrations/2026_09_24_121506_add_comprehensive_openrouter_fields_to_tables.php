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
        Schema::table('models', function (Blueprint $table) {
            $table->string('canonical_slug')->nullable()->after('slug');
            $table->string('hugging_face_id')->nullable()->after('canonical_slug');
            $table->json('architecture_details')->nullable()->after('architecture');
            $table->json('supported_parameters')->nullable();
            $table->json('default_parameters')->nullable();
            $table->json('supported_voices')->nullable();
            $table->json('reasoning_spec')->nullable();
            $table->json('per_request_limits')->nullable();
            $table->string('knowledge_cutoff')->nullable();
            $table->string('expiration_date')->nullable();
            $table->string('openrouter_details_url')->nullable();
            $table->json('alias_target')->nullable();
        });

        Schema::table('model_specs', function (Blueprint $table) {
            $table->decimal('input_cache_read_cost_per_1m', 14, 6)->nullable()->after('output_cost_per_1m');
            $table->decimal('input_cache_write_cost_per_1m', 14, 6)->nullable()->after('input_cache_read_cost_per_1m');
            $table->decimal('input_cache_write_1h_cost_per_1m', 14, 6)->nullable()->after('input_cache_write_cost_per_1m');
            $table->decimal('web_search_cost', 10, 4)->nullable()->after('input_cache_write_1h_cost_per_1m');
            $table->decimal('internal_reasoning_cost_per_1m', 14, 6)->nullable()->after('web_search_cost');
            $table->decimal('image_input_cost', 10, 4)->nullable()->after('internal_reasoning_cost_per_1m');
            $table->decimal('image_output_cost', 10, 4)->nullable()->after('image_input_cost');
            $table->decimal('audio_input_cost_per_1m', 14, 6)->nullable()->after('image_output_cost');
            $table->decimal('audio_output_cost_per_1m', 14, 6)->nullable()->after('audio_input_cost_per_1m');
            $table->json('pricing_overrides')->nullable()->after('audio_output_cost_per_1m');
            $table->unsignedBigInteger('top_provider_context_length')->nullable()->after('context_window');
            $table->unsignedInteger('top_provider_max_completion_tokens')->nullable()->after('max_output_tokens');
            $table->boolean('is_moderated')->default(false)->after('top_provider_max_completion_tokens');
            $table->json('raw_pricing')->nullable();
        });

        Schema::table('benchmarks_general', function (Blueprint $table) {
            $table->decimal('intelligence_index', 6, 2)->nullable()->after('elo_rating');
            $table->decimal('coding_index', 6, 2)->nullable()->after('intelligence_index');
            $table->decimal('agentic_index', 6, 2)->nullable()->after('coding_index');
            $table->json('design_arena')->nullable()->after('agentic_index');
            $table->json('raw_benchmarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->dropColumn([
                'canonical_slug',
                'hugging_face_id',
                'architecture_details',
                'supported_parameters',
                'default_parameters',
                'supported_voices',
                'reasoning_spec',
                'per_request_limits',
                'knowledge_cutoff',
                'expiration_date',
                'openrouter_details_url',
                'alias_target',
            ]);
        });

        Schema::table('model_specs', function (Blueprint $table) {
            $table->dropColumn([
                'input_cache_read_cost_per_1m',
                'input_cache_write_cost_per_1m',
                'input_cache_write_1h_cost_per_1m',
                'web_search_cost',
                'internal_reasoning_cost_per_1m',
                'image_input_cost',
                'image_output_cost',
                'audio_input_cost_per_1m',
                'audio_output_cost_per_1m',
                'pricing_overrides',
                'top_provider_context_length',
                'top_provider_max_completion_tokens',
                'is_moderated',
                'raw_pricing',
            ]);
        });

        Schema::table('benchmarks_general', function (Blueprint $table) {
            $table->dropColumn([
                'intelligence_index',
                'coding_index',
                'agentic_index',
                'design_arena',
                'raw_benchmarks',
            ]);
        });
    }
};
