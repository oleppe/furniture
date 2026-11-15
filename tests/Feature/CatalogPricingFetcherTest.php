<?php

namespace Tests\Feature;

use App\Models\AiModel;
use App\Models\ModelOffering;
use App\Models\Provider;
use App\Services\CatalogPricingFetcherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CatalogPricingFetcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_sync_catalog_and_prices_with_mocked_apis(): void
    {
        Http::fake([
            CatalogPricingFetcherService::OPENROUTER_MODELS_URL => Http::response([
                'data' => [
                    [
                        'id' => 'openai/gpt-4o',
                        'canonical_slug' => 'openai/gpt-4o-2024-05-13',
                        'name' => 'OpenAI: GPT-4o',
                        'created' => 1715600000,
                        'description' => 'Flagship omni model from OpenAI.',
                        'context_length' => 128000,
                        'architecture' => [
                            'modality' => 'text+image->text',
                            'tokenizer' => 'o200k_base',
                            'input_modalities' => ['text', 'image'],
                            'output_modalities' => ['text'],
                        ],
                        'pricing' => [
                            'prompt' => '0.0000025',
                            'completion' => '0.00001',
                            'input_cache_read' => '0.00000125',
                            'web_search' => '0.01',
                        ],
                        'top_provider' => [
                            'context_length' => 128000,
                            'max_completion_tokens' => 16384,
                            'is_moderated' => true,
                        ],
                        'supported_parameters' => ['temperature', 'top_p', 'tools'],
                        'reasoning' => [
                            'mandatory' => false,
                            'default_effort' => 'high',
                        ],
                        'benchmarks' => [
                            'artificial_analysis' => [
                                'intelligence_index' => 58.5,
                                'coding_index' => 62.0,
                            ],
                            'design_arena' => [
                                ['arena' => 'webapps', 'elo' => 1320],
                            ],
                        ],
                    ],
                    [
                        'id' => 'deepseek/deepseek-chat',
                        'canonical_slug' => 'deepseek/deepseek-chat-v3',
                        'name' => 'DeepSeek: DeepSeek V3',
                        'created' => 1735000000,
                        'description' => 'Open-weights Mixture of Experts model from DeepSeek.',
                        'context_length' => 131072,
                        'architecture' => [
                            'modality' => 'text->text',
                            'tokenizer' => 'Other',
                            'input_modalities' => ['text'],
                            'output_modalities' => ['text'],
                        ],
                        'pricing' => [
                            'prompt' => '0.00000028',
                            'completion' => '0.00000042',
                            'input_cache_read' => '0.000000028',
                        ],
                        'top_provider' => [
                            'context_length' => 131072,
                            'max_completion_tokens' => 8192,
                            'is_moderated' => false,
                        ],
                    ],
                ],
            ], 200),
            CatalogPricingFetcherService::LITELLM_PRICES_URL => Http::response([
                'gpt-4o' => [
                    'litellm_provider' => 'openai',
                    'input_cost_per_token' => 0.0000025,
                    'output_cost_per_token' => 0.00001,
                    'max_input_tokens' => 128000,
                    'max_output_tokens' => 16384,
                ],
                'deepseek/deepseek-chat' => [
                    'litellm_provider' => 'deepseek',
                    'input_cost_per_token' => 0.00000028,
                    'output_cost_per_token' => 0.00000042,
                    'max_input_tokens' => 131072,
                    'max_output_tokens' => 8192,
                ],
            ], 200),
        ]);

        $service = app(CatalogPricingFetcherService::class);
        $stats = $service->syncCatalog();

        $this->assertEquals(2, $stats['providers']);
        $this->assertEquals(2, $stats['models']);
        $this->assertEquals(2, $stats['specs']);
        $this->assertEquals(4, $stats['offerings']);
        $this->assertEquals(2, $stats['litellm_matched']);

        // Verify OpenAI provider & model
        $openAiProvider = Provider::where('slug', 'openai')->first();
        $this->assertNotNull($openAiProvider);
        $this->assertEquals('OpenAI', $openAiProvider->name);
        $this->assertEquals('https://openai.com', $openAiProvider->website);

        $gpt4o = AiModel::where('slug', 'gpt-4o')->first();
        $this->assertNotNull($gpt4o);
        $this->assertEquals('GPT-4o', $gpt4o->name);
        $this->assertEquals('openai/gpt-4o-2024-05-13', $gpt4o->canonical_slug);
        $this->assertEquals('openai/gpt-4o', $gpt4o->openrouter_id);
        $this->assertEquals('gpt-4o', $gpt4o->litellm_id);
        $this->assertFalse($gpt4o->is_open_source);
        $this->assertEquals(['temperature', 'top_p', 'tools'], $gpt4o->supported_parameters);
        $this->assertEquals('high', $gpt4o->reasoning_spec['default_effort']);

        // Verify capabilities
        $this->assertIsArray($gpt4o->capabilities);
        $this->assertTrue($gpt4o->capabilities['supports_vision']);
        $this->assertTrue($gpt4o->capabilities['supports_function_calling']);
        $this->assertTrue($gpt4o->capabilities['supports_prompt_caching']);
        $this->assertTrue($gpt4o->capabilities['supports_web_search']);

        // Verify offerings for GPT-4o
        $this->assertCount(2, $gpt4o->offerings);
        $this->assertNotNull($gpt4o->officialOffering);
        $this->assertEquals('direct', $gpt4o->officialOffering->host_type);
        $this->assertTrue($gpt4o->officialOffering->is_official_direct);
        $this->assertNotNull($gpt4o->cheapestOffering);
        $this->assertTrue($gpt4o->cheapestOffering->is_cheapest);

        $gpt4oSpec = $gpt4o->latestSpec;
        $this->assertNotNull($gpt4oSpec);
        $this->assertEquals('2.500000', $gpt4oSpec->input_cost_per_1m);
        $this->assertEquals('10.000000', $gpt4oSpec->output_cost_per_1m);
        $this->assertEquals('1.250000', $gpt4oSpec->input_cache_read_cost_per_1m);
        $this->assertEquals('0.0100', $gpt4oSpec->web_search_cost);
        $this->assertTrue($gpt4oSpec->is_moderated);
        $this->assertEquals(128000, $gpt4oSpec->context_window);
        $this->assertEquals(16384, $gpt4oSpec->max_output_tokens);

        // Verify Artificial Analysis benchmark
        $benchmark = $gpt4o->benchmarkGeneral;
        $this->assertNotNull($benchmark);
        $this->assertEquals('58.50', $benchmark->intelligence_index);
        $this->assertEquals('62.00', $benchmark->coding_index);
        $this->assertEquals(1320.00, (float) $benchmark->elo_rating);

        // Verify DeepSeek is marked open-source
        $deepseek = AiModel::where('slug', 'deepseek-chat')->first();
        $this->assertNotNull($deepseek);
        $this->assertTrue($deepseek->is_open_source);
        $this->assertEquals('deepseek/deepseek-chat-v3', $deepseek->canonical_slug);
        $this->assertFalse($deepseek->latestSpec->is_moderated);
        $this->assertEquals('0.280000', $deepseek->latestSpec->input_cost_per_1m);
        $this->assertEquals('0.420000', $deepseek->latestSpec->output_cost_per_1m);
        $this->assertEquals('0.028000', $deepseek->latestSpec->input_cache_read_cost_per_1m);
        $this->assertCount(2, $deepseek->offerings);
        $this->assertNotNull($deepseek->cheapestOffering);
    }

    public function test_artisan_command_executes_successfully_with_limit(): void
    {
        Http::fake([
            CatalogPricingFetcherService::OPENROUTER_MODELS_URL => Http::response([
                'data' => [
                    [
                        'id' => 'anthropic/claude-3.5-sonnet',
                        'name' => 'Anthropic: Claude 3.5 Sonnet',
                        'created' => 1718800000,
                        'context_length' => 200000,
                        'pricing' => [
                            'prompt' => '0.000003',
                            'completion' => '0.000015',
                        ],
                        'top_provider' => [
                            'context_length' => 200000,
                            'max_completion_tokens' => 8192,
                        ],
                    ],
                ],
            ], 200),
            CatalogPricingFetcherService::LITELLM_PRICES_URL => Http::response([], 200),
        ]);

        $this->artisan('catalog:fetch-prices', ['--limit' => 1])
            ->expectsOutputToContain('Starting catalog and pricing sync from OpenRouter & LiteLLM')
            ->expectsOutputToContain('Catalog and pricing sync completed successfully')
            ->assertSuccessful();

        $this->assertDatabaseHas('models', [
            'slug' => 'claude-3-5-sonnet',
            'openrouter_id' => 'anthropic/claude-3.5-sonnet',
        ]);

        $this->assertDatabaseHas('model_specs', [
            'input_cost_per_1m' => '3.000000',
            'output_cost_per_1m' => '15.000000',
            'context_window' => 200000,
            'max_output_tokens' => 8192,
        ]);
    }

    public function test_normalize_model_slug_handles_various_formats(): void
    {
        $service = app(CatalogPricingFetcherService::class);

        $this->assertEquals('gpt-4o', $service->normalizeModelSlug('openai/gpt-4o'));
        $this->assertEquals('claude-3-5-sonnet', $service->normalizeModelSlug('anthropic/claude-3.5-sonnet'));
        $this->assertEquals('gemini-2-0-flash', $service->normalizeModelSlug('google/gemini-2.0-flash:free'));
        $this->assertEquals('llama-3-3-70b-instruct', $service->normalizeModelSlug('meta-llama/llama-3.3-70b-instruct'));

        // Claude Opus 5.5 variants & hosting endpoints
        $this->assertEquals('claude-opus-5-5', $service->normalizeModelSlug('anthropic/claude-opus-5.5'));
        $this->assertEquals('claude-opus-5-5', $service->normalizeModelSlug('anthropic/claude-opus-5.5:batch'));
        $this->assertEquals('claude-opus-latest', $service->normalizeModelSlug('~anthropic/claude-opus-latest'));
        $this->assertEquals('claude-opus-5-5', $service->normalizeModelSlug('vertex_ai/claude-opus-5-5'));
        $this->assertEquals('claude-opus-5-5', $service->normalizeModelSlug('vertex_ai/claude-opus-5-5@default'));
        $this->assertEquals('claude-opus-5-5', $service->normalizeModelSlug('azure_ai/claude-opus-5-5'));
        $this->assertEquals('claude-opus-5-5', $service->normalizeModelSlug('anthropic.claude-opus-5-5'));
        $this->assertEquals('claude-opus-5-5', $service->normalizeModelSlug('apac.anthropic.claude-opus-5-5'));
        $this->assertEquals('claude-opus-5-5', $service->normalizeModelSlug('us-gov.anthropic.claude-opus-5-5'));
        $this->assertEquals('claude-opus-5-5', $service->normalizeModelSlug('bedrock/us-gov-west-1/anthropic.claude-opus-5-5'));
        $this->assertEquals('claude-opus-5-5', $service->normalizeModelSlug('openrouter/anthropic/claude-opus-5.5'));
        $this->assertEquals('claude-opus-5-5', $service->normalizeModelSlug('openrouter/anthropic/claude-opus-5.5:batch'));

        // Bedrock dot-notation keys with version suffixes
        $this->assertEquals('nova-pro-v1', $service->normalizeModelSlug('amazon.nova-pro-v1:0'));
        $this->assertEquals('llama3-3-70b-instruct-v1', $service->normalizeModelSlug('meta.llama3-3-70b-instruct-v1:0'));
        $this->assertEquals('command-r-v1', $service->normalizeModelSlug('cohere.command-r-v1:0'));
    }

    public function test_can_sync_multi_provider_offerings_and_tiered_pricing(): void
    {
        Http::fake([
            CatalogPricingFetcherService::OPENROUTER_MODELS_URL => Http::response([
                'data' => [
                    [
                        'id' => 'openai/gpt-4o',
                        'name' => 'OpenAI: GPT-4o',
                        'pricing' => [
                            'prompt' => '0.0000025',
                            'completion' => '0.000010',
                            'input_cache_read' => '0.00000125',
                            'input_cache_write' => '0.000005',
                        ],
                    ],
                ],
            ], 200),
            CatalogPricingFetcherService::LITELLM_PRICES_URL => Http::response([
                'gpt-4o' => [
                    'litellm_provider' => 'openai',
                    'input_cost_per_token' => 0.0000025,
                    'output_cost_per_token' => 0.000010,
                    'input_cost_per_token_batches' => 0.00000125,
                    'output_cost_per_token_batches' => 0.000005,
                    'input_cost_per_token_flex' => 0.000001,
                    'output_cost_per_token_flex' => 0.000004,
                    'input_cost_per_token_priority' => 0.000005,
                    'output_cost_per_token_priority' => 0.00002,
                    'cache_read_input_token_cost' => 0.00000125,
                    'cache_creation_input_token_cost' => 0.000005,
                    'supports_vision' => 1,
                    'supports_prompt_caching' => 1,
                    'supports_function_calling' => 1,
                ],
                'azure/gpt-4o' => [
                    'litellm_provider' => 'azure',
                    'input_cost_per_token' => 0.0000025,
                    'output_cost_per_token' => 0.000010,
                ],
                'azure/eu/gpt-4o-2024-08-06' => [
                    'litellm_provider' => 'azure',
                    'input_cost_per_token' => 0.00000275,
                    'output_cost_per_token' => 0.000011,
                    'regional_processing_uplift_multiplier_eu' => 1.100,
                ],
                'bedrock/openai.gpt-4o' => [
                    'litellm_provider' => 'bedrock',
                    'input_cost_per_token' => 0.0000025,
                    'output_cost_per_token' => 0.000010,
                ],
                'groq/gpt-4o' => [
                    'litellm_provider' => 'groq',
                    'input_cost_per_token' => 0.0000019,
                    'output_cost_per_token' => 0.000008,
                ],
            ], 200),
        ]);

        $service = app(CatalogPricingFetcherService::class);
        $stats = $service->syncCatalog();

        $this->assertEquals(1, $stats['models']);
        $this->assertEquals(6, $stats['offerings']); // 1 OpenRouter + 5 LiteLLM endpoints

        $model = AiModel::where('slug', 'gpt-4o')->first();
        $this->assertNotNull($model);
        $this->assertCount(6, $model->offerings);

        // Verify Official Direct offering (OpenAI)
        $directOffering = $model->offerings()->where('endpoint_key', 'gpt-4o')->first();
        $this->assertNotNull($directOffering);
        $this->assertTrue($directOffering->is_official_direct);
        $this->assertEquals('direct', $directOffering->host_type);
        $this->assertEquals('2.500000', $directOffering->input_cost_per_1m);
        $this->assertEquals('1.250000', $directOffering->batch_input_cost_per_1m);
        $this->assertEquals('1.000000', $directOffering->flex_input_cost_per_1m);
        $this->assertEquals('5.000000', $directOffering->priority_input_cost_per_1m);
        $this->assertEquals('1.250000', $directOffering->cache_read_input_token_cost_per_1m);
        $this->assertEquals('5.000000', $directOffering->cache_creation_input_token_cost_per_1m);

        // Verify Azure EU offering with regional uplift
        $azureEu = $model->offerings()->where('endpoint_key', 'azure/eu/gpt-4o-2024-08-06')->first();
        $this->assertNotNull($azureEu);
        $this->assertEquals('enterprise_cloud', $azureEu->host_type);
        $this->assertEquals('eu', $azureEu->region);
        $this->assertEquals('2.750000', $azureEu->input_cost_per_1m);
        $this->assertEquals('1.100', $azureEu->regional_processing_uplift_eu);

        // Verify Groq fast inference offering marked as cheapest
        $groqOffering = $model->offerings()->where('endpoint_key', 'groq/gpt-4o')->first();
        $this->assertNotNull($groqOffering);
        $this->assertEquals('fast_inference', $groqOffering->host_type);
        $this->assertEquals('1.900000', $groqOffering->input_cost_per_1m);
        $this->assertTrue($groqOffering->is_cheapest);
        $this->assertEquals($groqOffering->id, $model->cheapestOffering->id);
    }

    public function test_can_parse_hosting_endpoint_and_region(): void
    {
        $service = app(CatalogPricingFetcherService::class);

        // 1. Azure EU endpoint
        $azure = $service->parseHostingEndpoint('azure/eu/gpt-4o-2024-08-06', [
            'litellm_provider' => 'azure',
            'input_cost_per_token' => 0.00000275,
            'regional_processing_uplift_multiplier_eu' => 1.1,
        ], 'openai');

        $this->assertEquals('azure', $azure['provider_slug']);
        $this->assertEquals('enterprise_cloud', $azure['host_type']);
        $this->assertEquals('eu', $azure['region']);
        $this->assertFalse($azure['is_official_direct']);
        $this->assertEquals(2.75, $azure['input_cost_per_1m']);
        $this->assertEquals(1.1, $azure['regional_processing_uplift_eu']);

        // 2. Groq fast inference
        $groq = $service->parseHostingEndpoint('groq/llama-3.3-70b-versatile', [
            'litellm_provider' => 'groq',
            'input_cost_per_token' => 0.00000059,
        ], 'meta');

        $this->assertEquals('groq', $groq['provider_slug']);
        $this->assertEquals('fast_inference', $groq['host_type']);
        $this->assertEquals('global', $groq['region']);

        // 3. Bedrock cloud
        $bedrock = $service->parseHostingEndpoint('bedrock/anthropic.claude-3-5-sonnet', [
            'litellm_provider' => 'bedrock',
        ], 'anthropic');

        $this->assertEquals('amazon-bedrock', $bedrock['provider_slug']);
        $this->assertEquals('enterprise_cloud', $bedrock['host_type']);

        // 4. Direct creator
        $direct = $service->parseHostingEndpoint('gpt-4o', [
            'litellm_provider' => 'openai',
            'input_cost_per_token' => 0.0000025,
        ], 'openai');

        $this->assertEquals('openai', $direct['provider_slug']);
        $this->assertEquals('direct', $direct['host_type']);
        $this->assertTrue($direct['is_official_direct']);
    }

    public function test_claude_opus_5_5_unifies_all_providers_without_creating_fake_models(): void
    {
        Http::fake([
            CatalogPricingFetcherService::OPENROUTER_MODELS_URL => Http::response([
                'data' => [
                    [
                        'id' => 'anthropic/claude-opus-5.5',
                        'name' => 'Anthropic: Claude Opus 5.5',
                        'pricing' => [
                            'prompt' => '0.000004',
                            'completion' => '0.000020',
                        ],
                    ],
                    [
                        'id' => 'anthropic/claude-opus-5.5:batch',
                        'name' => 'Anthropic: Claude Opus 5.5 (batch)',
                        'pricing' => [
                            'prompt' => '0.000002',
                            'completion' => '0.000010',
                        ],
                    ],
                ],
            ], 200),
            CatalogPricingFetcherService::LITELLM_PRICES_URL => Http::response([
                'claude-opus-5-5' => [
                    'litellm_provider' => 'anthropic',
                    'input_cost_per_token' => 0.000004,
                    'output_cost_per_token' => 0.000020,
                    'max_input_tokens' => 1000000,
                    'max_output_tokens' => 128000,
                ],
                'vertex_ai/claude-opus-5-5' => [
                    'litellm_provider' => 'vertex_ai-anthropic_models',
                    'input_cost_per_token' => 0.000004,
                    'output_cost_per_token' => 0.000020,
                ],
                'azure_ai/claude-opus-5-5' => [
                    'litellm_provider' => 'azure_ai',
                    'input_cost_per_token' => 0.000004,
                    'output_cost_per_token' => 0.000020,
                ],
                'apac.anthropic.claude-opus-5-5' => [
                    'litellm_provider' => 'bedrock_converse',
                    'input_cost_per_token' => 0.0000044,
                    'output_cost_per_token' => 0.000022,
                ],
                'anthropic.claude-opus-5-5' => [
                    'litellm_provider' => 'bedrock_converse',
                    'input_cost_per_token' => 0.000004,
                    'output_cost_per_token' => 0.000020,
                ],
            ], 200),
        ]);

        $service = app(CatalogPricingFetcherService::class);
        $stats = $service->syncCatalog();

        // There should be exactly ONE canonical model created
        $this->assertEquals(1, $stats['models']);
        $this->assertEquals(1, AiModel::count());

        $model = AiModel::where('slug', 'claude-opus-5-5')->first();
        $this->assertNotNull($model);
        $this->assertEquals('Claude Opus 5.5', $model->name);
        $this->assertEquals('anthropic', $model->provider->slug);
        $this->assertEquals('anthropic/claude-opus-5.5', $model->openrouter_id);

        // Ensure NO fake Bedrock models exist
        $this->assertFalse(AiModel::where('slug', 'anthropicclaude-opus-5-5')->exists());
        $this->assertFalse(AiModel::where('slug', 'apacanthropicclaude-opus-5-5')->exists());
        $this->assertFalse(AiModel::where('name', 'like', '%Anthropicclaude%')->exists());

        // Check offerings on the canonical model (6 endpoints total: 1 OpenRouter + 5 LiteLLM).
        // The OpenRouter :batch variant is not its own endpoint: its discounted price is
        // stored as batch tier pricing on the base offering.
        $this->assertCount(6, $model->offerings);
        $this->assertDatabaseMissing('model_offerings', [
            'model_id' => $model->id,
            'endpoint_key' => 'openrouter/anthropic/claude-opus-5.5:batch',
        ]);

        // Verify Vertex AI offering
        $vertexOffering = $model->offerings()->where('endpoint_key', 'vertex_ai/claude-opus-5-5')->first();
        $this->assertNotNull($vertexOffering);
        $this->assertEquals('google-vertex', $vertexOffering->provider->slug);
        $this->assertEquals('Google Vertex AI', $vertexOffering->provider->name);

        // Verify Azure offering
        $azureOffering = $model->offerings()->where('endpoint_key', 'azure_ai/claude-opus-5-5')->first();
        $this->assertNotNull($azureOffering);
        $this->assertEquals('azure', $azureOffering->provider->slug);
        $this->assertEquals('Microsoft Azure', $azureOffering->provider->name);

        // Verify Bedrock offerings
        $apacBedrock = $model->offerings()->where('endpoint_key', 'apac.anthropic.claude-opus-5-5')->first();
        $this->assertNotNull($apacBedrock);
        $this->assertEquals('amazon-bedrock', $apacBedrock->provider->slug);
        $this->assertEquals('Amazon Bedrock', $apacBedrock->provider->name);
        $this->assertEquals('apac', $apacBedrock->region);

        // Verify Direct offering
        $directOffering = $model->offerings()->where('endpoint_key', 'claude-opus-5-5')->first();
        $this->assertNotNull($directOffering);
        $this->assertTrue($directOffering->is_official_direct);
        $this->assertEquals('anthropic', $directOffering->provider->slug);

        // Batch variant pricing is saved as batch tier pricing on the base offering,
        // while the normal usage price stays the primary displayed price
        $openRouterOffering = $model->offerings()->where('endpoint_key', 'openrouter/anthropic/claude-opus-5.5')->first();
        $this->assertNotNull($openRouterOffering);
        $this->assertEquals('4.000000', $openRouterOffering->input_cost_per_1m);
        $this->assertEquals('20.000000', $openRouterOffering->output_cost_per_1m);
        $this->assertEquals('2.000000', $openRouterOffering->batch_input_cost_per_1m);
        $this->assertEquals('10.000000', $openRouterOffering->batch_output_cost_per_1m);

        // The model's headline pricing shows the normal usage price, not the batch price
        $this->assertEquals('4.000000', $model->latestSpec->input_cost_per_1m);
        $this->assertEquals('20.000000', $model->latestSpec->output_cost_per_1m);

        // The cheapest badge is based on normal usage pricing
        $this->assertNotNull($model->cheapestOffering);
        $this->assertEquals('4.000000', $model->cheapestOffering->input_cost_per_1m);
    }

    public function test_prune_invalid_duplicate_models_removes_corrupted_bedrock_entries(): void
    {
        $bedrockProvider = Provider::create([
            'slug' => 'bedrock_converse',
            'name' => 'Bedrock_Converse',
        ]);

        $junkModel = AiModel::create([
            'slug' => 'anthropicclaude-opus-5-5',
            'name' => 'Anthropicclaude Opus 5 5',
            'provider_id' => $bedrockProvider->id,
            'is_open_source' => false,
        ]);

        $service = app(CatalogPricingFetcherService::class);
        $pruned = $service->pruneInvalidDuplicateModels();

        $this->assertGreaterThanOrEqual(1, $pruned);
        $this->assertDatabaseMissing('models', ['id' => $junkModel->id]);
        $this->assertDatabaseMissing('providers', ['id' => $bedrockProvider->id]);
    }

    public function test_resync_cleans_up_stale_batch_variant_offerings_and_recomputes_cheapest(): void
    {
        Http::fake([
            CatalogPricingFetcherService::OPENROUTER_MODELS_URL => Http::response([
                'data' => [
                    [
                        'id' => 'anthropic/claude-opus-5.5',
                        'name' => 'Anthropic: Claude Opus 5.5',
                        'pricing' => [
                            'prompt' => '0.000004',
                            'completion' => '0.000020',
                        ],
                    ],
                    [
                        'id' => 'anthropic/claude-opus-5.5:batch',
                        'name' => 'Anthropic: Claude Opus 5.5 (batch)',
                        'pricing' => [
                            'prompt' => '0.000002',
                            'completion' => '0.000010',
                        ],
                    ],
                ],
            ], 200),
            CatalogPricingFetcherService::LITELLM_PRICES_URL => Http::response([], 200),
        ]);

        $service = app(CatalogPricingFetcherService::class);
        $service->syncCatalog();

        $model = AiModel::where('slug', 'claude-opus-5-5')->firstOrFail();

        // Simulate legacy data: a standalone :batch offering undercutting the base
        // offering and wrongly holding the "cheapest" badge.
        $staleBatchOffering = ModelOffering::create([
            'model_id' => $model->id,
            'provider_id' => Provider::where('slug', 'openrouter')->firstOrFail()->id,
            'endpoint_key' => 'openrouter/anthropic/claude-opus-5.5:batch',
            'input_cost_per_1m' => 2,
            'output_cost_per_1m' => 10,
            'is_cheapest' => true,
        ]);
        $model->offerings()->where('id', '!=', $staleBatchOffering->id)->update(['is_cheapest' => false]);

        $service->syncCatalog();

        // The stale batch offering is folded into the base offering's batch tier pricing
        $this->assertDatabaseMissing('model_offerings', ['id' => $staleBatchOffering->id]);
        $baseOffering = $model->offerings()->where('endpoint_key', 'openrouter/anthropic/claude-opus-5.5')->firstOrFail();
        $this->assertEquals('4.000000', $baseOffering->input_cost_per_1m);
        $this->assertEquals('2.000000', $baseOffering->batch_input_cost_per_1m);
        $this->assertEquals('10.000000', $baseOffering->batch_output_cost_per_1m);

        // The cheapest badge is back on a normal usage price
        $cheapest = $model->fresh()->cheapestOffering;
        $this->assertNotNull($cheapest);
        $this->assertEquals($baseOffering->id, $cheapest->id);
        $this->assertEquals('4.000000', $cheapest->input_cost_per_1m);
    }
}
