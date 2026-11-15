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
            $table->json('capabilities')->nullable()->after('architecture_details');
        });

        Schema::create('model_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('models')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('endpoint_key')->index();
            $table->string('host_type')->default('direct')->index(); // direct, enterprise_cloud, fast_inference, aggregator
            $table->string('region')->default('global')->index(); // global, us, eu, apac

            // Standard Pricing per 1M tokens
            $table->decimal('input_cost_per_1m', 14, 6)->nullable();
            $table->decimal('output_cost_per_1m', 14, 6)->nullable();

            // Discounted & Tiered Pricing per 1M tokens
            $table->decimal('batch_input_cost_per_1m', 14, 6)->nullable();
            $table->decimal('batch_output_cost_per_1m', 14, 6)->nullable();
            $table->decimal('flex_input_cost_per_1m', 14, 6)->nullable();
            $table->decimal('flex_output_cost_per_1m', 14, 6)->nullable();
            $table->decimal('priority_input_cost_per_1m', 14, 6)->nullable();
            $table->decimal('priority_output_cost_per_1m', 14, 6)->nullable();
            $table->decimal('context_tier_above_272k_input_cost_per_1m', 14, 6)->nullable();
            $table->decimal('context_tier_above_272k_output_cost_per_1m', 14, 6)->nullable();

            // Prompt Caching per 1M tokens
            $table->decimal('cache_read_input_token_cost_per_1m', 14, 6)->nullable();
            $table->decimal('cache_creation_input_token_cost_per_1m', 14, 6)->nullable();

            // Regional Processing Uplift Multipliers (e.g., 1.10 = +10%)
            $table->decimal('regional_processing_uplift_eu', 5, 3)->nullable();
            $table->decimal('regional_processing_uplift_us', 5, 3)->nullable();

            // Performance / Telemetry
            $table->decimal('tokens_per_sec', 8, 2)->nullable();
            $table->decimal('ttft_ms', 8, 2)->nullable();

            // Decision Flags
            $table->boolean('is_official_direct')->default(false)->index();
            $table->boolean('is_cheapest')->default(false)->index();

            // Raw Provider Offering Metadata
            $table->json('raw_offering_spec')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_offerings');

        Schema::table('models', function (Blueprint $table) {
            $table->dropColumn('capabilities');
        });
    }
};
