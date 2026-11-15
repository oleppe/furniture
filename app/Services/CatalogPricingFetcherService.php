<?php

namespace App\Services;

use App\Models\AiModel;
use App\Models\BenchmarkArabic;
use App\Models\BenchmarkGeneral;
use App\Models\ModelOffering;
use App\Models\ModelSpec;
use App\Models\Provider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CatalogPricingFetcherService
{
    public const OPENROUTER_MODELS_URL = 'https://openrouter.ai/api/v1/models';

    public const LITELLM_PRICES_URL = 'https://raw.githubusercontent.com/BerriAI/litellm/main/model_prices_and_context_window.json';

    /**
     * Curated metadata for prominent LLM providers.
     *
     * @var array<string, array{name: string, website: string, logo_url: string}>
     */
    protected array $knownProviders = [
        'openai' => [
            'name' => 'OpenAI',
            'website' => 'https://openai.com',
            'logo_url' => 'https://cdn.oaistatic.com/_next/static/media/apple-touch-icon.83af8f7d.png',
        ],
        'anthropic' => [
            'name' => 'Anthropic',
            'website' => 'https://anthropic.com',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/7/78/Anthropic_logo.svg',
        ],
        'google' => [
            'name' => 'Google DeepMind',
            'website' => 'https://deepmind.google',
            'logo_url' => 'https://www.gstatic.com/lamda/images/gemini_sparkle_v002_d4735304ff6292a690345.svg',
        ],
        'google-vertex' => [
            'name' => 'Google Vertex AI',
            'website' => 'https://cloud.google.com/vertex-ai',
            'logo_url' => 'https://www.gstatic.com/lamda/images/gemini_sparkle_v002_d4735304ff6292a690345.svg',
        ],
        'meta' => [
            'name' => 'Meta AI',
            'website' => 'https://ai.meta.com',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/7/7b/Meta_Platforms_Inc._logo.svg',
        ],
        'meta-llama' => [
            'name' => 'Meta AI',
            'website' => 'https://ai.meta.com',
            'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/7/7b/Meta_Platforms_Inc._logo.svg',
        ],
        'deepseek' => [
            'name' => 'DeepSeek',
            'website' => 'https://deepseek.com',
            'logo_url' => 'https://chat.deepseek.com/favicon.svg',
        ],
        'mistralai' => [
            'name' => 'Mistral AI',
            'website' => 'https://mistral.ai',
            'logo_url' => 'https://mistral.ai/images/logo_hubc88c4ece131b91c2c72620786e0825c_2568_256x256_fill_lanczos_center_3.png',
        ],
        'mistral' => [
            'name' => 'Mistral AI',
            'website' => 'https://mistral.ai',
            'logo_url' => 'https://mistral.ai/images/logo_hubc88c4ece131b91c2c72620786e0825c_2568_256x256_fill_lanczos_center_3.png',
        ],
        'cohere' => [
            'name' => 'Cohere',
            'website' => 'https://cohere.com',
            'logo_url' => 'https://cohere.com/favicon.ico',
        ],
        'qwen' => [
            'name' => 'Alibaba Qwen',
            'website' => 'https://qwenlm.github.io',
            'logo_url' => 'https://qwenlm.github.io/assets/images/qwen-logo.png',
        ],
        'fireworks' => [
            'name' => 'Fireworks AI',
            'website' => 'https://fireworks.ai',
            'logo_url' => 'https://fireworks.ai/favicon.ico',
        ],
        'fireworks-ai' => [
            'name' => 'Fireworks AI',
            'website' => 'https://fireworks.ai',
            'logo_url' => 'https://fireworks.ai/favicon.ico',
        ],
        'together' => [
            'name' => 'Together AI',
            'website' => 'https://together.ai',
            'logo_url' => 'https://together.ai/favicon.ico',
        ],
        'together-ai' => [
            'name' => 'Together AI',
            'website' => 'https://together.ai',
            'logo_url' => 'https://together.ai/favicon.ico',
        ],
        'groq' => [
            'name' => 'Groq',
            'website' => 'https://groq.com',
            'logo_url' => 'https://groq.com/favicon.ico',
        ],
        'cerebras' => [
            'name' => 'Cerebras',
            'website' => 'https://cerebras.ai',
            'logo_url' => 'https://cerebras.ai/favicon.ico',
        ],
        'sambanova' => [
            'name' => 'SambaNova',
            'website' => 'https://sambanova.ai',
            'logo_url' => 'https://sambanova.ai/favicon.ico',
        ],
        'deepinfra' => [
            'name' => 'DeepInfra',
            'website' => 'https://deepinfra.com',
            'logo_url' => 'https://deepinfra.com/favicon.ico',
        ],
        'openrouter' => [
            'name' => 'OpenRouter',
            'website' => 'https://openrouter.ai',
            'logo_url' => 'https://openrouter.ai/favicon.ico',
        ],
        'x-ai' => [
            'name' => 'xAI',
            'website' => 'https://x.ai',
            'logo_url' => 'https://x.ai/favicon.ico',
        ],
        'microsoft' => [
            'name' => 'Microsoft Azure',
            'website' => 'https://azure.microsoft.com',
            'logo_url' => 'https://www.microsoft.com/favicon.ico',
        ],
        'azure' => [
            'name' => 'Microsoft Azure',
            'website' => 'https://azure.microsoft.com',
            'logo_url' => 'https://www.microsoft.com/favicon.ico',
        ],
        'amazon' => [
            'name' => 'Amazon AWS',
            'website' => 'https://aws.amazon.com',
            'logo_url' => 'https://a0.awsstatic.com/libra-css/images/site/fav/favicon.ico',
        ],
        'amazon-bedrock' => [
            'name' => 'Amazon Bedrock',
            'website' => 'https://aws.amazon.com/bedrock',
            'logo_url' => 'https://a0.awsstatic.com/libra-css/images/site/fav/favicon.ico',
        ],
        'bedrock' => [
            'name' => 'Amazon Bedrock',
            'website' => 'https://aws.amazon.com/bedrock',
            'logo_url' => 'https://a0.awsstatic.com/libra-css/images/site/fav/favicon.ico',
        ],
        'perplexity' => [
            'name' => 'Perplexity AI',
            'website' => 'https://perplexity.ai',
            'logo_url' => 'https://perplexity.ai/favicon.ico',
        ],
        'databricks' => [
            'name' => 'Databricks',
            'website' => 'https://databricks.com',
            'logo_url' => 'https://databricks.com/favicon.ico',
        ],
        'snowflake' => [
            'name' => 'Snowflake',
            'website' => 'https://snowflake.com',
            'logo_url' => 'https://snowflake.com/favicon.ico',
        ],
        'novita' => [
            'name' => 'Novita AI',
            'website' => 'https://novita.ai',
            'logo_url' => 'https://novita.ai/favicon.ico',
        ],
        'fal-ai' => [
            'name' => 'Fal AI',
            'website' => 'https://fal.ai',
            'logo_url' => 'https://fal.ai/favicon.ico',
        ],
        'replicate' => [
            'name' => 'Replicate',
            'website' => 'https://replicate.com',
            'logo_url' => 'https://replicate.com/favicon.ico',
        ],
    ];

    /**
     * List of provider slugs that produce open-source / open-weights models.
     *
     * @var list<string>
     */
    protected array $openSourceProviders = [
        'meta',
        'meta-llama',
        'mistralai',
        'deepseek',
        'qwen',
        'tiiuae',
        'allenai',
        'eleutherai',
        'stabilityai',
        'nousresearch',
    ];

    /**
     * Provider aliases mapping variations and hosting slugs to canonical provider slugs.
     *
     * @var array<string, string>
     */
    protected array $providerAliases = [
        'vertex_ai' => 'google-vertex',
        'vertex-ai' => 'google-vertex',
        'vertex' => 'google-vertex',
        'vertex_ai-anthropic_models' => 'google-vertex',
        'vertex_ai-language-models' => 'google-vertex',
        'vertex_ai-text-models' => 'google-vertex',
        'vertex_ai-embedding-models' => 'google-vertex',
        'bedrock_converse' => 'amazon-bedrock',
        'bedrock' => 'amazon-bedrock',
        'bedrock_mantle' => 'amazon-bedrock',
        'aws-bedrock' => 'amazon-bedrock',
        'azure_ai' => 'azure',
        'microsoft' => 'azure',
        'meta-llama' => 'meta',
        'together_ai' => 'together',
        'together-ai' => 'together',
        'fireworks_ai' => 'fireworks',
        'fireworks-ai' => 'fireworks',
        'deepinfra' => 'deepinfra',
        'mistral' => 'mistralai',
        'gemini' => 'google',
        'xai' => 'x-ai',
    ];

    /**
     * Fetch models list from OpenRouter API.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchOpenRouterModels(): array
    {
        $response = Http::withUserAgent('Arabic-LLM-Intelligence/1.0')
            ->timeout(60)
            ->retry(3, 1000)
            ->get(self::OPENROUTER_MODELS_URL);

        if (! $response->successful()) {
            throw new \RuntimeException("Failed to fetch OpenRouter models. HTTP Status: {$response->status()}");
        }

        $json = $response->json();

        return $json['data'] ?? [];
    }

    /**
     * Fetch standard direct model pricing and specs from LiteLLM.
     *
     * @return array<string, array<string, mixed>>
     */
    public function fetchLiteLlmModels(): array
    {
        $response = Http::withUserAgent('Arabic-LLM-Intelligence/1.0')
            ->timeout(60)
            ->retry(3, 1000)
            ->get(self::LITELLM_PRICES_URL);

        if (! $response->successful()) {
            throw new \RuntimeException("Failed to fetch LiteLLM model prices. HTTP Status: {$response->status()}");
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    /**
     * Build an indexed lookup dictionary from LiteLLM models.
     *
     * @param  array<string, array<string, mixed>>  $liteLlmData
     * @return array<string, array<string, mixed>>
     */
    public function buildLiteLlmIndex(array $liteLlmData): array
    {
        $index = [];

        foreach ($liteLlmData as $key => $spec) {
            if (! is_array($spec) || $key === 'sample_spec') {
                continue;
            }

            // Raw key lookup
            $index[strtolower($key)] = $spec;

            // Normalized slug lookup
            $normalizedSlug = $this->normalizeModelSlug($key);
            if (! isset($index[$normalizedSlug])) {
                $index[$normalizedSlug] = $spec;
            }

            // Strip provider prefix if present (e.g., openai/gpt-4o -> gpt-4o)
            if (str_contains($key, '/')) {
                $afterSlash = substr($key, strrpos($key, '/') + 1);
                $index[strtolower($afterSlash)] = $spec;
                $index[$this->normalizeModelSlug($afterSlash)] = $spec;
            }
        }

        return $index;
    }

    /**
     * Build an indexed lookup dictionary of model hosting offerings grouped by canonical model slug.
     *
     * @param  array<string, array<string, mixed>>  $liteLlmData
     * @return array<string, list<array{endpoint_key: string, spec: array<string, mixed>}>>
     */
    public function buildLiteLlmOfferingsIndex(array $liteLlmData): array
    {
        $offeringsIndex = [];

        foreach ($liteLlmData as $key => $spec) {
            if (! is_array($spec) || $key === 'sample_spec') {
                continue;
            }

            $entry = [
                'endpoint_key' => $key,
                'spec' => $spec,
            ];

            // 1. Index under canonical normalized model slug
            $canonicalSlug = $this->normalizeModelSlug($key);
            $offeringsIndex[$canonicalSlug][] = $entry;

            // 2. Also index under date-stripped base slug if applicable (e.g. gpt-4o-2024-08-06 -> gpt-4o)
            $cleanName = preg_replace('/-\d{4}-?\d{2}-?\d{2}.*$/', '', $key);
            if ($cleanName !== null && $cleanName !== $key) {
                $baseSlug = $this->normalizeModelSlug($cleanName);
                if ($baseSlug !== $canonicalSlug) {
                    $offeringsIndex[$baseSlug][] = $entry;
                }
            }
        }

        // Deduplicate entries within each group by endpoint_key
        foreach ($offeringsIndex as $slug => $entries) {
            $unique = [];
            foreach ($entries as $e) {
                $unique[$e['endpoint_key']] = $e;
            }
            $offeringsIndex[$slug] = array_values($unique);
        }

        return $offeringsIndex;
    }

    /**
     * Synchronize models and pricing catalog from OpenRouter and LiteLLM.
     *
     * @param  int|null  $limit  Optional limit for testing/debugging
     * @return array{providers: int, models: int, specs: int, offerings: int, litellm_matched: int}
     */
    public function syncCatalog(?int $limit = null): array
    {
        Log::info('Starting AI models catalog sync...');

        // Clean up any corrupt duplicates from previous runs
        $this->pruneInvalidDuplicateModels();

        $openRouterModels = $this->fetchOpenRouterModels();
        $liteLlmData = $this->fetchLiteLlmModels();
        $liteLlmIndex = $this->buildLiteLlmIndex($liteLlmData);
        $liteLlmOfferingsIndex = $this->buildLiteLlmOfferingsIndex($liteLlmData);

        if ($limit !== null && $limit > 0) {
            $openRouterModels = array_slice($openRouterModels, 0, $limit);
        }

        $syncedProvidersCount = 0;
        $syncedModelsCount = 0;
        $syncedSpecsCount = 0;
        $syncedOfferingsCount = 0;
        $liteLlmMatchedCount = 0;

        $providersCache = [];
        $openRouterProvider = $this->resolveOrCreateProvider('openrouter');

        foreach ($openRouterModels as $orModel) {
            $rawId = $orModel['id'] ?? null;
            if (! $rawId) {
                continue;
            }

            // 1. Resolve Provider
            $providerSlug = $this->extractProviderSlug($rawId);
            if (! isset($providersCache[$providerSlug])) {
                $provider = $this->resolveOrCreateProvider($providerSlug);
                $providersCache[$providerSlug] = $provider;
                $syncedProvidersCount++;
            } else {
                $provider = $providersCache[$providerSlug];
            }

            // 2. Resolve Model Slug & Match LiteLLM
            $modelSlug = $this->normalizeModelSlug($rawId);
            $rawModelName = $orModel['name'] ?? $rawId;
            $cleanModelName = $this->cleanModelName($rawModelName, $provider->name);
            $isVariant = str_contains($rawId, ':');

            // Match LiteLLM spec & offerings
            $matchedOfferings = $liteLlmOfferingsIndex[$modelSlug] ?? [];
            $liteLlmSpec = $this->matchLiteLlmSpec($modelSlug, $rawId, $liteLlmIndex);
            if ($liteLlmSpec === null && ! empty($matchedOfferings)) {
                $liteLlmSpec = $matchedOfferings[0]['spec'];
            }

            $litellmId = null;
            if ($liteLlmSpec !== null) {
                $litellmId = $liteLlmSpec['_key'] ?? $modelSlug;
                $liteLlmMatchedCount++;
            }

            // 3. Determine open source status, architecture & capabilities
            $architecture = $this->resolveArchitecture($orModel, $liteLlmSpec);
            $isOpenSource = $this->determineIsOpenSource($providerSlug, $orModel, $liteLlmSpec);
            $releaseDate = isset($orModel['created']) ? Carbon::createFromTimestamp($orModel['created'])->toDateString() : null;
            $capabilities = $this->extractCapabilities($orModel, $liteLlmSpec);

            // 4. Upsert AiModel with all available metadata
            $existingModel = AiModel::where('slug', $modelSlug)->first();

            $modelData = [
                'provider_id' => $provider->id,
                'architecture' => $architecture,
                'architecture_details' => $orModel['architecture'] ?? null,
                'release_date' => $releaseDate,
                'is_open_source' => $isOpenSource,
                'litellm_id' => $litellmId,
                'capabilities' => $capabilities,
                'supported_parameters' => $orModel['supported_parameters'] ?? null,
                'default_parameters' => $orModel['default_parameters'] ?? null,
                'supported_voices' => $orModel['supported_voices'] ?? null,
                'reasoning_spec' => $orModel['reasoning'] ?? null,
                'per_request_limits' => $orModel['per_request_limits'] ?? null,
                'knowledge_cutoff' => $orModel['knowledge_cutoff'] ?? null,
                'expiration_date' => $orModel['expiration_date'] ?? null,
                'openrouter_details_url' => $orModel['links']['details'] ?? null,
                'alias_target' => $orModel['alias_target'] ?? null,
            ];

            // Only set primary identity attributes if new or not a variant
            if ($existingModel === null || ! $isVariant) {
                $modelData['name'] = $cleanModelName;
                $modelData['openrouter_id'] = $rawId;
                $modelData['canonical_slug'] = $orModel['canonical_slug'] ?? null;
                $modelData['hugging_face_id'] = $orModel['hugging_face_id'] ?? null;
                $modelData['description'] = $orModel['description'] ?? null;
            }

            $aiModel = AiModel::updateOrCreate(
                ['slug' => $modelSlug],
                $modelData
            );

            if ($existingModel === null) {
                $syncedModelsCount++;
            }

            // 5. Calculate and Upsert ModelSpec with all pricing & provider details
            $specs = $this->extractPricingAndSpecs($orModel, $liteLlmSpec);

            // Variant entries (:batch, :free, :nitro, ...) price a non-standard tier and
            // must never overwrite the model's canonical normal usage pricing.
            if (! $isVariant || ! ModelSpec::where('model_id', $aiModel->id)->exists()) {
                ModelSpec::updateOrCreate(
                    ['model_id' => $aiModel->id],
                    [
                        'input_cost_per_1m' => $specs['input_cost_per_1m'],
                        'output_cost_per_1m' => $specs['output_cost_per_1m'],
                        'input_cache_read_cost_per_1m' => $specs['input_cache_read_cost_per_1m'],
                        'input_cache_write_cost_per_1m' => $specs['input_cache_write_cost_per_1m'],
                        'input_cache_write_1h_cost_per_1m' => $specs['input_cache_write_1h_cost_per_1m'],
                        'web_search_cost' => $specs['web_search_cost'],
                        'internal_reasoning_cost_per_1m' => $specs['internal_reasoning_cost_per_1m'],
                        'image_input_cost' => $specs['image_input_cost'],
                        'image_output_cost' => $specs['image_output_cost'],
                        'audio_input_cost_per_1m' => $specs['audio_input_cost_per_1m'],
                        'audio_output_cost_per_1m' => $specs['audio_output_cost_per_1m'],
                        'pricing_overrides' => $specs['pricing_overrides'],
                        'context_window' => $specs['context_window'],
                        'max_output_tokens' => $specs['max_output_tokens'],
                        'top_provider_context_length' => $specs['top_provider_context_length'],
                        'top_provider_max_completion_tokens' => $specs['top_provider_max_completion_tokens'],
                        'is_moderated' => $specs['is_moderated'],
                        'raw_pricing' => $specs['raw_pricing'],
                    ]
                );

                $syncedSpecsCount++;
            }

            // 6. Record benchmarks if provided by OpenRouter (Artificial Analysis & Design Arena)
            if (isset($orModel['benchmarks']) && is_array($orModel['benchmarks'])) {
                $benchmarks = $orModel['benchmarks'];
                $aa = $benchmarks['artificial_analysis'] ?? [];
                $designArena = $benchmarks['design_arena'] ?? null;

                $elo = null;
                if (is_array($designArena) && ! empty($designArena)) {
                    $elos = array_filter(array_column($designArena, 'elo'));
                    if (! empty($elos)) {
                        $elo = max($elos);
                    }
                }

                BenchmarkGeneral::updateOrCreate(
                    ['model_id' => $aiModel->id],
                    [
                        'elo_rating' => $elo,
                        'intelligence_index' => $aa['intelligence_index'] ?? null,
                        'coding_index' => $aa['coding_index'] ?? null,
                        'agentic_index' => $aa['agentic_index'] ?? null,
                        'design_arena' => $designArena,
                        'raw_benchmarks' => $benchmarks,
                        'source_updated_at' => now(),
                    ]
                );
            }

            // 7. Ensure default Arabic benchmark baseline exists
            $this->ensureArabicBenchmark($aiModel, $specs['output_cost_per_1m']);

            // 8. Synchronize all hosting offerings (OpenRouter, Direct, Cloud, Fast Inference)
            $syncedOfferingsCount += $this->syncOfferingsForModel(
                $aiModel,
                $orModel,
                $matchedOfferings,
                $openRouterProvider,
                $provider
            );
        }

        // 9. Sync standalone direct models from LiteLLM that were not in OpenRouter (when full sync)
        if ($limit === null) {
            $excludedProviders = [
                'bedrock',
                'bedrock_converse',
                'bedrock_mantle',
                'azure',
                'azure_ai',
                'vertex_ai',
                'vertex_ai-anthropic_models',
                'vertex_ai-language-models',
                'vertex_ai-text-models',
                'vertex_ai-embedding-models',
                'groq',
                'together',
                'together_ai',
                'fireworks',
                'fireworks_ai',
                'fireworks_ai-embedding-models',
                'deepinfra',
                'openrouter',
                'perplexity',
                'novita',
                'cerebras',
                'sambanova',
                'snowflake',
                'databricks',
                'aihubmix',
                'nlp_cloud',
                'text-completion-openai',
            ];

            foreach ($liteLlmData as $key => $spec) {
                if (! is_array($spec) || $key === 'sample_spec') {
                    continue;
                }

                // Only create canonical model for standalone/direct models (not regional cloud sub-endpoints)
                if (str_contains($key, '/')) {
                    continue;
                }

                // Skip Bedrock dot notation endpoints (e.g. apac.anthropic..., anthropic.claude..., amazon.nova..., meta.llama...)
                if (preg_match('/^(?:(?:apac|eu|us-gov|us|jp|au|global)\.)?(?:anthropic|amazon|meta|cohere|mistral|ai21|openai|google)\./i', $key)) {
                    continue;
                }

                $creatorSlug = strtolower($spec['litellm_provider'] ?? '');
                if (in_array($creatorSlug, $excludedProviders, true)) {
                    continue;
                }

                $slug = $this->normalizeModelSlug($key);
                if (AiModel::where('slug', $slug)->exists()) {
                    continue;
                }

                $creatorSlug = $creatorSlug ?: 'independent';
                if (! isset($providersCache[$creatorSlug])) {
                    $provider = $this->resolveOrCreateProvider($creatorSlug);
                    $providersCache[$creatorSlug] = $provider;
                    $syncedProvidersCount++;
                } else {
                    $provider = $providersCache[$creatorSlug];
                }

                $cleanName = Str::headline($slug);
                $matchedOfferings = $liteLlmOfferingsIndex[$slug] ?? [['endpoint_key' => $key, 'spec' => $spec]];

                $capabilities = $this->extractCapabilities([], $spec);
                $aiModel = AiModel::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'provider_id' => $provider->id,
                        'name' => $cleanName,
                        'architecture' => $spec['mode'] ?? 'text->text',
                        'is_open_source' => $this->determineIsOpenSource($creatorSlug, [], $spec),
                        'litellm_id' => $key,
                        'capabilities' => $capabilities,
                    ]
                );
                $syncedModelsCount++;

                $calc1m = fn (?float $c) => ($c !== null && is_numeric($c)) ? round((float) $c * 1_000_000, 6) : null;
                $inputCost1m = $calc1m($spec['input_cost_per_token'] ?? null);
                $outputCost1m = $calc1m($spec['output_cost_per_token'] ?? null);

                ModelSpec::updateOrCreate(
                    ['model_id' => $aiModel->id],
                    [
                        'input_cost_per_1m' => $inputCost1m,
                        'output_cost_per_1m' => $outputCost1m,
                        'input_cache_read_cost_per_1m' => $calc1m($spec['cache_read_input_token_cost'] ?? null),
                        'input_cache_write_cost_per_1m' => $calc1m($spec['cache_creation_input_token_cost'] ?? null),
                        'context_window' => $spec['max_input_tokens'] ?? null,
                        'max_output_tokens' => $spec['max_output_tokens'] ?? $spec['max_tokens'] ?? null,
                        'raw_pricing' => $spec,
                    ]
                );
                $syncedSpecsCount++;
                $liteLlmMatchedCount++;

                $this->ensureArabicBenchmark($aiModel, $outputCost1m);
                $syncedOfferingsCount += $this->syncOfferingsForModel(
                    $aiModel,
                    [],
                    $matchedOfferings,
                    $openRouterProvider,
                    $provider
                );
            }
        }

        $result = [
            'providers' => count($providersCache),
            'models' => $syncedModelsCount,
            'specs' => $syncedSpecsCount,
            'offerings' => $syncedOfferingsCount,
            'litellm_matched' => $liteLlmMatchedCount,
        ];

        Log::info('AI models catalog sync completed successfully.', $result);

        return $result;
    }

    /**
     * Extract rich capability flags from OpenRouter and LiteLLM metadata.
     *
     * @param  array<string, mixed>  $orModel
     * @param  array<string, mixed>|null  $llmSpec
     * @return array<string, bool>
     */
    public function extractCapabilities(array $orModel, ?array $llmSpec): array
    {
        $inputModalities = $orModel['architecture']['input_modalities'] ?? [];
        $outputModalities = $orModel['architecture']['output_modalities'] ?? [];
        $supportedParams = $orModel['supported_parameters'] ?? [];
        $pricing = $orModel['pricing'] ?? [];

        return [
            'supports_vision' => ! empty($llmSpec['supports_vision']) || in_array('image', $inputModalities, true),
            'supports_function_calling' => ! empty($llmSpec['supports_function_calling']) || in_array('tools', $supportedParams, true),
            'supports_parallel_function_calling' => ! empty($llmSpec['supports_parallel_function_calling']),
            'supports_response_schema' => ! empty($llmSpec['supports_response_schema']) || in_array('response_format', $supportedParams, true),
            'supports_prompt_caching' => ! empty($llmSpec['supports_prompt_caching']) || ((float) ($pricing['input_cache_read'] ?? 0) > 0),
            'supports_prompt_cache_breakpoint' => ! empty($llmSpec['supports_prompt_cache_breakpoint']),
            'supports_pdf_input' => ! empty($llmSpec['supports_pdf_input']),
            'supports_computer_use' => ! empty($llmSpec['supports_computer_use']),
            'supports_reasoning' => ! empty($llmSpec['supports_reasoning']) || isset($orModel['reasoning']) || ((float) ($pricing['internal_reasoning'] ?? 0) > 0),
            'supports_native_streaming' => ! empty($llmSpec['supports_native_streaming']),
            'supports_system_messages' => ! empty($llmSpec['supports_system_messages']),
            'supports_tool_choice' => ! empty($llmSpec['supports_tool_choice']) || in_array('tool_choice', $supportedParams, true),
            'supports_web_search' => ! empty($llmSpec['supports_web_search']) || ((float) ($pricing['web_search'] ?? 0) > 0),
            'supports_audio_input' => ! empty($llmSpec['supports_audio_input']) || in_array('audio', $inputModalities, true) || ((float) ($pricing['audio'] ?? 0) > 0),
            'supports_audio_output' => ! empty($llmSpec['supports_audio_output']) || in_array('audio', $outputModalities, true) || ((float) ($pricing['audio_output'] ?? 0) > 0),
        ];
    }

    /**
     * Parse hosting endpoint details, classification, region, and tiered pricing.
     *
     * @param  array<string, mixed>  $spec
     * @return array{
     *     provider_slug: string,
     *     host_type: string,
     *     region: string,
     *     is_official_direct: bool,
     *     input_cost_per_1m: float|null,
     *     output_cost_per_1m: float|null,
     *     batch_input_cost_per_1m: float|null,
     *     batch_output_cost_per_1m: float|null,
     *     flex_input_cost_per_1m: float|null,
     *     flex_output_cost_per_1m: float|null,
     *     priority_input_cost_per_1m: float|null,
     *     priority_output_cost_per_1m: float|null,
     *     context_tier_above_272k_input_cost_per_1m: float|null,
     *     context_tier_above_272k_output_cost_per_1m: float|null,
     *     cache_read_input_token_cost_per_1m: float|null,
     *     cache_creation_input_token_cost_per_1m: float|null,
     *     regional_processing_uplift_eu: float|null,
     *     regional_processing_uplift_us: float|null
     * }
     */
    public function parseHostingEndpoint(string $key, array $spec, ?string $modelCreatorSlug = null): array
    {
        $lowerKey = strtolower($key);
        $litellmProvider = strtolower($spec['litellm_provider'] ?? '');

        // 1. Identify host_type and provider_slug
        $hostType = 'direct';
        $providerSlug = $modelCreatorSlug ?? 'independent';
        $isOfficialDirect = false;

        if (in_array($litellmProvider, ['azure', 'azure_ai'], true) || str_starts_with($lowerKey, 'azure/')) {
            $providerSlug = 'azure';
            $hostType = 'enterprise_cloud';
        } elseif (
            in_array($litellmProvider, ['bedrock', 'bedrock_converse', 'bedrock_mantle'], true)
            || str_starts_with($lowerKey, 'bedrock/')
            || str_starts_with($lowerKey, 'amazon.')
            || preg_match('/^(?:(?:apac|eu|us-gov|us|jp|au|global)\.)?(?:anthropic|amazon|meta|cohere|mistral|ai21|openai|google)\./i', $lowerKey)
        ) {
            $providerSlug = 'amazon-bedrock';
            $hostType = 'enterprise_cloud';
        } elseif (str_starts_with($litellmProvider, 'vertex_ai') || str_starts_with($lowerKey, 'vertex_ai/')) {
            $providerSlug = 'google-vertex';
            $hostType = 'enterprise_cloud';
        } elseif ($litellmProvider === 'groq' || str_starts_with($lowerKey, 'groq/')) {
            $providerSlug = 'groq';
            $hostType = 'fast_inference';
        } elseif (in_array($litellmProvider, ['together_ai', 'together'], true) || str_starts_with($lowerKey, 'together_ai/') || str_starts_with($lowerKey, 'together/')) {
            $providerSlug = 'together';
            $hostType = 'fast_inference';
        } elseif (in_array($litellmProvider, ['fireworks_ai', 'fireworks'], true) || str_starts_with($lowerKey, 'fireworks_ai/') || str_starts_with($lowerKey, 'fireworks/')) {
            $providerSlug = 'fireworks';
            $hostType = 'fast_inference';
        } elseif ($litellmProvider === 'cerebras' || str_starts_with($lowerKey, 'cerebras/')) {
            $providerSlug = 'cerebras';
            $hostType = 'fast_inference';
        } elseif ($litellmProvider === 'sambanova' || str_starts_with($lowerKey, 'sambanova/')) {
            $providerSlug = 'sambanova';
            $hostType = 'fast_inference';
        } elseif ($litellmProvider === 'deepinfra' || str_starts_with($lowerKey, 'deepinfra/')) {
            $providerSlug = 'deepinfra';
            $hostType = 'fast_inference';
        } elseif ($litellmProvider === 'openrouter' || str_starts_with($lowerKey, 'openrouter/')) {
            $providerSlug = 'openrouter';
            $hostType = 'aggregator';
        } elseif ($litellmProvider === 'novita' || str_starts_with($lowerKey, 'novita/')) {
            $providerSlug = 'novita';
            $hostType = 'fast_inference';
        } elseif ($litellmProvider === 'databricks' || str_starts_with($lowerKey, 'databricks/')) {
            $providerSlug = 'databricks';
            $hostType = 'enterprise_cloud';
        } elseif ($litellmProvider === 'snowflake' || str_starts_with($lowerKey, 'snowflake/')) {
            $providerSlug = 'snowflake';
            $hostType = 'enterprise_cloud';
        } elseif ($litellmProvider === 'perplexity' || str_starts_with($lowerKey, 'perplexity/')) {
            $providerSlug = 'perplexity';
            $hostType = 'enterprise_cloud';
        } elseif ($litellmProvider === 'openai') {
            $providerSlug = 'openai';
            $isDirect = ($modelCreatorSlug === null || $modelCreatorSlug === 'openai');
            $hostType = $isDirect ? 'direct' : 'enterprise_cloud';
            $isOfficialDirect = $isDirect;
        } elseif ($litellmProvider === 'anthropic') {
            $providerSlug = 'anthropic';
            $isDirect = ($modelCreatorSlug === null || $modelCreatorSlug === 'anthropic');
            $hostType = $isDirect ? 'direct' : 'enterprise_cloud';
            $isOfficialDirect = $isDirect;
        } elseif (in_array($litellmProvider, ['gemini', 'google'], true)) {
            $providerSlug = 'google';
            $isDirect = ($modelCreatorSlug === null || $modelCreatorSlug === 'google');
            $hostType = $isDirect ? 'direct' : 'enterprise_cloud';
            $isOfficialDirect = $isDirect;
        } elseif (in_array($litellmProvider, ['mistral', 'mistralai'], true)) {
            $providerSlug = 'mistralai';
            $isDirect = ($modelCreatorSlug === null || $modelCreatorSlug === 'mistralai');
            $hostType = $isDirect ? 'direct' : 'fast_inference';
            $isOfficialDirect = $isDirect;
        } elseif ($litellmProvider === 'deepseek') {
            $providerSlug = 'deepseek';
            $isDirect = ($modelCreatorSlug === null || $modelCreatorSlug === 'deepseek');
            $hostType = $isDirect ? 'direct' : 'fast_inference';
            $isOfficialDirect = $isDirect;
        } elseif ($litellmProvider === 'xai') {
            $providerSlug = 'x-ai';
            $isDirect = ($modelCreatorSlug === null || $modelCreatorSlug === 'x-ai');
            $hostType = $isDirect ? 'direct' : 'fast_inference';
            $isOfficialDirect = $isDirect;
        } elseif ($litellmProvider === 'cohere') {
            $providerSlug = 'cohere';
            $isDirect = ($modelCreatorSlug === null || $modelCreatorSlug === 'cohere');
            $hostType = $isDirect ? 'direct' : 'enterprise_cloud';
            $isOfficialDirect = $isDirect;
        } else {
            if ($modelCreatorSlug !== null && ! str_contains($lowerKey, '/')) {
                $isOfficialDirect = true;
                $hostType = 'direct';
            }
        }

        // 2. Region detection
        $region = 'global';
        if (str_contains($lowerKey, '/eu/') || str_contains($lowerKey, '/europe/') || str_contains($lowerKey, '-eu-') || str_ends_with($lowerKey, '-eu') || str_starts_with($lowerKey, 'eu.')) {
            $region = 'eu';
        } elseif (str_contains($lowerKey, '/us/') || str_contains($lowerKey, '-us-') || str_ends_with($lowerKey, '-us') || str_starts_with($lowerKey, 'us.') || str_starts_with($lowerKey, 'us-gov.')) {
            $region = 'us';
        } elseif (str_contains($lowerKey, '/apac/') || str_contains($lowerKey, '/asia/') || str_contains($lowerKey, '-apac-') || str_ends_with($lowerKey, '-apac') || str_starts_with($lowerKey, 'apac.') || str_starts_with($lowerKey, 'jp.') || str_starts_with($lowerKey, 'au.')) {
            $region = 'apac';
        }

        // 3. Pricing calculations per 1M tokens
        $calc1m = fn ($val) => isset($val) && is_numeric($val)
            ? round((float) $val * 1_000_000, 6)
            : null;

        return [
            'provider_slug' => $providerSlug,
            'host_type' => $hostType,
            'region' => $region,
            'is_official_direct' => $isOfficialDirect,
            'input_cost_per_1m' => $calc1m($spec['input_cost_per_token'] ?? null),
            'output_cost_per_1m' => $calc1m($spec['output_cost_per_token'] ?? null),
            'batch_input_cost_per_1m' => $calc1m($spec['input_cost_per_token_batches'] ?? null),
            'batch_output_cost_per_1m' => $calc1m($spec['output_cost_per_token_batches'] ?? null),
            'flex_input_cost_per_1m' => $calc1m($spec['input_cost_per_token_flex'] ?? null),
            'flex_output_cost_per_1m' => $calc1m($spec['output_cost_per_token_flex'] ?? null),
            'priority_input_cost_per_1m' => $calc1m($spec['input_cost_per_token_priority'] ?? null),
            'priority_output_cost_per_1m' => $calc1m($spec['output_cost_per_token_priority'] ?? null),
            'context_tier_above_272k_input_cost_per_1m' => $calc1m($spec['input_cost_per_token_above_272k_tokens'] ?? null),
            'context_tier_above_272k_output_cost_per_1m' => $calc1m($spec['output_cost_per_token_above_272k_tokens'] ?? null),
            'cache_read_input_token_cost_per_1m' => $calc1m($spec['cache_read_input_token_cost'] ?? null),
            'cache_creation_input_token_cost_per_1m' => $calc1m($spec['cache_creation_input_token_cost'] ?? null),
            'regional_processing_uplift_eu' => isset($spec['regional_processing_uplift_multiplier_eu']) && is_numeric($spec['regional_processing_uplift_multiplier_eu'])
                ? (float) $spec['regional_processing_uplift_multiplier_eu']
                : null,
            'regional_processing_uplift_us' => isset($spec['regional_processing_uplift_multiplier_us']) && is_numeric($spec['regional_processing_uplift_multiplier_us'])
                ? (float) $spec['regional_processing_uplift_multiplier_us']
                : null,
        ];
    }

    /**
     * Synchronize all hosting offerings (OpenRouter, Direct, Cloud, Fast Inference) for a given model.
     *
     * @param  array<string, mixed>  $orModel
     * @param  list<array{endpoint_key: string, spec: array<string, mixed>}>  $matchedLiteLlmOfferings
     */
    public function syncOfferingsForModel(
        AiModel $model,
        array $orModel,
        array $matchedLiteLlmOfferings,
        Provider $openRouterProvider,
        Provider $modelCreatorProvider
    ): int {
        $offeringsCount = 0;

        // 1. OpenRouter Offering
        if (! empty($orModel) && ! empty($orModel['id'])) {
            $pricing = $orModel['pricing'] ?? [];
            $orInputCost = isset($pricing['prompt']) && is_numeric($pricing['prompt'])
                ? (float) $pricing['prompt'] * 1_000_000
                : null;
            $orOutputCost = isset($pricing['completion']) && is_numeric($pricing['completion'])
                ? (float) $pricing['completion'] * 1_000_000
                : null;
            $orCacheReadCost = isset($pricing['input_cache_read']) && is_numeric($pricing['input_cache_read'])
                ? (float) $pricing['input_cache_read'] * 1_000_000
                : null;
            $orCacheWriteCost = isset($pricing['input_cache_write']) && is_numeric($pricing['input_cache_write'])
                ? (float) $pricing['input_cache_write'] * 1_000_000
                : null;

            if (str_ends_with($orModel['id'], ':batch')) {
                // Batch variant endpoints price the discounted batch tier as their usage
                // price. Save that price as batch tier pricing on the base offering so
                // listings keep showing the normal usage price.
                $this->mergeBatchVariantPricing(
                    $model,
                    'openrouter/'.$orModel['id'],
                    $orInputCost,
                    $orOutputCost,
                    [
                        'provider_id' => $openRouterProvider->id,
                        'host_type' => 'aggregator',
                        'region' => 'global',
                    ]
                );
            } else {
                ModelOffering::updateOrCreate(
                    [
                        'model_id' => $model->id,
                        'endpoint_key' => 'openrouter/'.$orModel['id'],
                    ],
                    [
                        'provider_id' => $openRouterProvider->id,
                        'host_type' => 'aggregator',
                        'region' => 'global',
                        'input_cost_per_1m' => $orInputCost !== null ? round($orInputCost, 6) : null,
                        'output_cost_per_1m' => $orOutputCost !== null ? round($orOutputCost, 6) : null,
                        'cache_read_input_token_cost_per_1m' => $orCacheReadCost !== null ? round($orCacheReadCost, 6) : null,
                        'cache_creation_input_token_cost_per_1m' => $orCacheWriteCost !== null ? round($orCacheWriteCost, 6) : null,
                        'is_official_direct' => false,
                        'is_cheapest' => false,
                        'raw_offering_spec' => [
                            'pricing' => $pricing,
                            'top_provider' => $orModel['top_provider'] ?? null,
                        ],
                    ]
                );
            }
            $offeringsCount++;
        }

        // 2. LiteLLM Multi-Provider Offerings
        foreach ($matchedLiteLlmOfferings as $offeringData) {
            $key = $offeringData['endpoint_key'];
            $spec = $offeringData['spec'];

            $parsed = $this->parseHostingEndpoint($key, $spec, $modelCreatorProvider->slug);
            $hostingProvider = $this->resolveOrCreateProvider($parsed['provider_slug']);

            if (str_ends_with($key, ':batch')) {
                // Batch variant endpoints price the discounted batch tier as their usage
                // price. Save that price as batch tier pricing on the base offering so
                // listings keep showing the normal usage price.
                $this->mergeBatchVariantPricing(
                    $model,
                    $key,
                    $parsed['batch_input_cost_per_1m'] ?? $parsed['input_cost_per_1m'],
                    $parsed['batch_output_cost_per_1m'] ?? $parsed['output_cost_per_1m'],
                    [
                        'provider_id' => $hostingProvider->id,
                        'host_type' => $parsed['host_type'],
                        'region' => $parsed['region'],
                    ]
                );
                $offeringsCount++;

                continue;
            }

            ModelOffering::updateOrCreate(
                [
                    'model_id' => $model->id,
                    'endpoint_key' => $key,
                ],
                [
                    'provider_id' => $hostingProvider->id,
                    'host_type' => $parsed['host_type'],
                    'region' => $parsed['region'],
                    'input_cost_per_1m' => $parsed['input_cost_per_1m'],
                    'output_cost_per_1m' => $parsed['output_cost_per_1m'],
                    'batch_input_cost_per_1m' => $parsed['batch_input_cost_per_1m'],
                    'batch_output_cost_per_1m' => $parsed['batch_output_cost_per_1m'],
                    'flex_input_cost_per_1m' => $parsed['flex_input_cost_per_1m'],
                    'flex_output_cost_per_1m' => $parsed['flex_output_cost_per_1m'],
                    'priority_input_cost_per_1m' => $parsed['priority_input_cost_per_1m'],
                    'priority_output_cost_per_1m' => $parsed['priority_output_cost_per_1m'],
                    'context_tier_above_272k_input_cost_per_1m' => $parsed['context_tier_above_272k_input_cost_per_1m'],
                    'context_tier_above_272k_output_cost_per_1m' => $parsed['context_tier_above_272k_output_cost_per_1m'],
                    'cache_read_input_token_cost_per_1m' => $parsed['cache_read_input_token_cost_per_1m'],
                    'cache_creation_input_token_cost_per_1m' => $parsed['cache_creation_input_token_cost_per_1m'],
                    'regional_processing_uplift_eu' => $parsed['regional_processing_uplift_eu'],
                    'regional_processing_uplift_us' => $parsed['regional_processing_uplift_us'],
                    'is_official_direct' => $parsed['is_official_direct'],
                    'is_cheapest' => false,
                    'raw_offering_spec' => $spec,
                ]
            );
            $offeringsCount++;
        }

        // 3. Mark the cheapest offering for this model
        // Prioritize official direct if prices are tied.
        // Batch-tier endpoints (:batch) never win: their price is the discounted
        // batch price, not the normal usage price.
        $cheapest = ModelOffering::where('model_id', $model->id)
            ->whereNotNull('input_cost_per_1m')
            ->where('input_cost_per_1m', '>', 0)
            ->where('endpoint_key', 'not like', '%:batch')
            ->orderBy('input_cost_per_1m', 'asc')
            ->orderBy('is_official_direct', 'desc')
            ->first();

        if ($cheapest !== null) {
            ModelOffering::where('model_id', $model->id)->where('id', '!=', $cheapest->id)->update(['is_cheapest' => false]);
            $cheapest->update(['is_cheapest' => true]);
        }

        return $offeringsCount;
    }

    /**
     * Persist batch-variant endpoint pricing (e.g. "anthropic/claude-opus-5.5:batch") as
     * batch tier pricing on the base offering, instead of creating a separate offering
     * whose usage price would misleadingly be the discounted batch price.
     *
     * @param  array<string, mixed>  $baseAttributes
     */
    private function mergeBatchVariantPricing(
        AiModel $model,
        string $variantEndpointKey,
        ?float $batchInputCostPer1m,
        ?float $batchOutputCostPer1m,
        array $baseAttributes
    ): void {
        $baseEndpointKey = preg_replace('/:batch$/', '', $variantEndpointKey) ?? $variantEndpointKey;

        ModelOffering::updateOrCreate(
            [
                'model_id' => $model->id,
                'endpoint_key' => $baseEndpointKey,
            ],
            array_merge($baseAttributes, [
                'batch_input_cost_per_1m' => $batchInputCostPer1m !== null ? round($batchInputCostPer1m, 6) : null,
                'batch_output_cost_per_1m' => $batchOutputCostPer1m !== null ? round($batchOutputCostPer1m, 6) : null,
            ])
        );

        // Drop any standalone batch offering created by earlier syncs.
        ModelOffering::where('model_id', $model->id)
            ->where('endpoint_key', $variantEndpointKey)
            ->delete();
    }

    /**
     * Extract provider slug from OpenRouter ID (e.g., 'openai/gpt-4o' -> 'openai').
     */
    public function extractProviderSlug(string $openRouterId): string
    {
        if (str_contains($openRouterId, '/')) {
            $parts = explode('/', $openRouterId, 2);

            return Str::slug($parts[0]);
        }

        return 'independent';
    }

    /**
     * Normalize model slug across OpenRouter and LiteLLM naming conventions.
     * Examples:
     * - 'openai/gpt-4o' -> 'gpt-4o'
     * - 'anthropic/claude-3.5-sonnet' -> 'claude-3-5-sonnet'
     * - 'deepseek/deepseek-chat' -> 'deepseek-chat'
     * - 'anthropic/claude-opus-5.5' -> 'claude-opus-5-5'
     * - 'vertex_ai/claude-opus-5-5' -> 'claude-opus-5-5'
     * - 'apac.anthropic.claude-opus-5-5' -> 'claude-opus-5-5'
     */
    public function normalizeModelSlug(string $identifier): string
    {
        $name = trim($identifier);

        // Strip leading tilde (e.g. ~anthropic/claude-opus-latest)
        $name = ltrim($name, '~');

        // Strip @default suffix (e.g. vertex_ai/claude-opus-5-5@default)
        if (str_contains($name, '@')) {
            $name = explode('@', $name)[0];
        }

        // Strip trailing colon tags like :batch, :free, :beta, :extended, :nitro, :online, and Bedrock version tags like :0, :1
        if (str_contains($name, ':')) {
            $name = explode(':', $name)[0];
        }

        // Strip known cloud / proxy / aggregator slash prefixes if present
        $proxyPattern = '/^(?:openrouter|vertex_ai|azure_ai|azure|bedrock|groq|together_ai|together|fireworks_ai|fireworks|deepinfra|databricks|perplexity|novita|cerebras|sambanova|snowflake|aihubmix|nlp_cloud)\//i';
        while (preg_match($proxyPattern, $name)) {
            $name = preg_replace($proxyPattern, '', $name);
        }

        // If slashes still exist: e.g. "anthropic/claude-opus-5.5", "meta-llama/llama-3.3-70b-instruct", "us-gov-west-1/anthropic.claude"
        if (str_contains($name, '/')) {
            $parts = explode('/', $name);
            $name = end($parts);
        }

        // Strip AWS Bedrock regional & provider dot prefixes:
        // Matches: "apac.anthropic.", "us-gov.anthropic.", "anthropic.", "amazon.", "meta.", "cohere.", "mistral.", "ai21.", "openai."
        $name = preg_replace('/^(?:(?:apac|eu|us-gov|us|jp|au|global)\.)?(?:anthropic|amazon|meta|cohere|mistral|ai21|openai|google)\./i', '', $name);

        // Replace version dots with dashes (e.g. 5.5 -> 5-5, 3.5 -> 3-5, 4.1 -> 4-1, 1.5 -> 1-5)
        $name = preg_replace('/(\d+)\.(\d+)/', '$1-$2', $name);

        return Str::slug(strtolower($name));
    }

    /**
     * Clean up model name for UI display.
     */
    public function cleanModelName(string $rawName, string $providerName): string
    {
        $name = trim($rawName);

        // If name has prefix like "OpenAI: GPT-4o", strip prefix
        if (str_contains($name, ':')) {
            $parts = explode(':', $name, 2);
            $candidate = trim($parts[1]);
            if (! empty($candidate)) {
                return $candidate;
            }
        }

        // Strip variant tags like "(batch)", "(free)", etc. from canonical model name
        $name = preg_replace('/\s*\((?:batch|free|beta|nitro|online|self-moderated)\)\s*/i', '', $name);

        return trim($name);
    }

    /**
     * Match a model against LiteLLM index.
     *
     * @param  array<string, array<string, mixed>>  $liteLlmIndex
     * @return array<string, mixed>|null
     */
    public function matchLiteLlmSpec(string $normalizedSlug, string $openRouterId, array $liteLlmIndex): ?array
    {
        // 1. Direct normalized slug match
        if (isset($liteLlmIndex[$normalizedSlug])) {
            $match = $liteLlmIndex[$normalizedSlug];
            $match['_key'] = $normalizedSlug;

            return $match;
        }

        // 2. OpenRouter raw ID match
        $lowerRawId = strtolower($openRouterId);
        if (isset($liteLlmIndex[$lowerRawId])) {
            $match = $liteLlmIndex[$lowerRawId];
            $match['_key'] = $lowerRawId;

            return $match;
        }

        // 3. Model part after slash
        if (str_contains($lowerRawId, '/')) {
            $afterSlash = substr($lowerRawId, strpos($lowerRawId, '/') + 1);
            if (isset($liteLlmIndex[$afterSlash])) {
                $match = $liteLlmIndex[$afterSlash];
                $match['_key'] = $afterSlash;

                return $match;
            }
        }

        return null;
    }

    /**
     * Resolve or create a provider by slug.
     */
    public function resolveOrCreateProvider(string $slug): Provider
    {
        $normalizedSlug = strtolower(trim($slug));
        $canonicalSlug = $this->providerAliases[$normalizedSlug] ?? $normalizedSlug;
        $canonicalSlug = str_replace('_', '-', $canonicalSlug);
        $canonicalSlug = $this->providerAliases[$canonicalSlug] ?? $canonicalSlug;

        $known = $this->knownProviders[$canonicalSlug] ?? null;

        $name = $known['name'] ?? Str::title(str_replace('-', ' ', $canonicalSlug));
        $website = $known['website'] ?? null;
        $logoUrl = $known['logo_url'] ?? null;

        return Provider::firstOrCreate(
            ['slug' => $canonicalSlug],
            [
                'name' => $name,
                'website' => $website,
                'logo_url' => $logoUrl,
            ]
        );
    }

    /**
     * Extract pricing and token limits from OpenRouter and cross-reference with LiteLLM.
     *
     * @param  array<string, mixed>  $orModel
     * @param  array<string, mixed>|null  $llmSpec
     * @return array{input_cost_per_1m: float|null, output_cost_per_1m: float|null, context_window: int|null, max_output_tokens: int|null}
     */
    public function extractPricingAndSpecs(array $orModel, ?array $llmSpec): array
    {
        $pricing = $orModel['pricing'] ?? [];
        $topProvider = $orModel['top_provider'] ?? [];

        // 1. Input cost per 1M tokens
        $inputCostPer1m = null;
        if (isset($pricing['prompt']) && is_numeric($pricing['prompt'])) {
            $inputCostPer1m = (float) $pricing['prompt'] * 1_000_000;
        } elseif ($llmSpec !== null && isset($llmSpec['input_cost_per_token']) && is_numeric($llmSpec['input_cost_per_token'])) {
            $inputCostPer1m = (float) $llmSpec['input_cost_per_token'] * 1_000_000;
        }

        // 2. Output cost per 1M tokens
        $outputCostPer1m = null;
        if (isset($pricing['completion']) && is_numeric($pricing['completion'])) {
            $outputCostPer1m = (float) $pricing['completion'] * 1_000_000;
        } elseif ($llmSpec !== null && isset($llmSpec['output_cost_per_token']) && is_numeric($llmSpec['output_cost_per_token'])) {
            $outputCostPer1m = (float) $llmSpec['output_cost_per_token'] * 1_000_000;
        }

        // 3. Cache Pricing per 1M tokens
        $inputCacheReadPer1m = isset($pricing['input_cache_read']) && is_numeric($pricing['input_cache_read'])
            ? (float) $pricing['input_cache_read'] * 1_000_000
            : null;

        $inputCacheWritePer1m = isset($pricing['input_cache_write']) && is_numeric($pricing['input_cache_write'])
            ? (float) $pricing['input_cache_write'] * 1_000_000
            : null;

        $inputCacheWrite1hPer1m = isset($pricing['input_cache_write_1h']) && is_numeric($pricing['input_cache_write_1h'])
            ? (float) $pricing['input_cache_write_1h'] * 1_000_000
            : null;

        // 4. Modal and Action Costs
        $webSearchCost = isset($pricing['web_search']) && is_numeric($pricing['web_search'])
            ? (float) $pricing['web_search']
            : null;

        $internalReasoningPer1m = isset($pricing['internal_reasoning']) && is_numeric($pricing['internal_reasoning'])
            ? (float) $pricing['internal_reasoning'] * 1_000_000
            : null;

        $imageInputCost = isset($pricing['image']) && is_numeric($pricing['image'])
            ? (float) $pricing['image']
            : null;

        $imageOutputCost = isset($pricing['image_output']) && is_numeric($pricing['image_output'])
            ? (float) $pricing['image_output']
            : null;

        $audioInputPer1m = isset($pricing['audio']) && is_numeric($pricing['audio'])
            ? (float) $pricing['audio'] * 1_000_000
            : null;

        $audioOutputPer1m = isset($pricing['audio_output']) && is_numeric($pricing['audio_output'])
            ? (float) $pricing['audio_output'] * 1_000_000
            : null;

        // 5. Context window & limits
        $contextWindow = $orModel['context_length']
            ?? $topProvider['context_length']
            ?? ($llmSpec['max_input_tokens'] ?? null);

        $maxOutputTokens = $topProvider['max_completion_tokens']
            ?? ($llmSpec['max_output_tokens'] ?? $llmSpec['max_tokens'] ?? null);

        $topProviderContext = $topProvider['context_length'] ?? null;
        $topProviderMaxOutput = $topProvider['max_completion_tokens'] ?? null;
        $isModerated = (bool) ($topProvider['is_moderated'] ?? false);

        return [
            'input_cost_per_1m' => $inputCostPer1m !== null ? round($inputCostPer1m, 6) : null,
            'output_cost_per_1m' => $outputCostPer1m !== null ? round($outputCostPer1m, 6) : null,
            'input_cache_read_cost_per_1m' => $inputCacheReadPer1m !== null ? round($inputCacheReadPer1m, 6) : null,
            'input_cache_write_cost_per_1m' => $inputCacheWritePer1m !== null ? round($inputCacheWritePer1m, 6) : null,
            'input_cache_write_1h_cost_per_1m' => $inputCacheWrite1hPer1m !== null ? round($inputCacheWrite1hPer1m, 6) : null,
            'web_search_cost' => $webSearchCost !== null ? round($webSearchCost, 4) : null,
            'internal_reasoning_cost_per_1m' => $internalReasoningPer1m !== null ? round($internalReasoningPer1m, 6) : null,
            'image_input_cost' => $imageInputCost !== null ? round($imageInputCost, 4) : null,
            'image_output_cost' => $imageOutputCost !== null ? round($imageOutputCost, 4) : null,
            'audio_input_cost_per_1m' => $audioInputPer1m !== null ? round($audioInputPer1m, 6) : null,
            'audio_output_cost_per_1m' => $audioOutputPer1m !== null ? round($audioOutputPer1m, 6) : null,
            'pricing_overrides' => $pricing['overrides'] ?? null,
            'context_window' => $contextWindow !== null ? (int) $contextWindow : null,
            'max_output_tokens' => $maxOutputTokens !== null ? (int) $maxOutputTokens : null,
            'top_provider_context_length' => $topProviderContext !== null ? (int) $topProviderContext : null,
            'top_provider_max_completion_tokens' => $topProviderMaxOutput !== null ? (int) $topProviderMaxOutput : null,
            'is_moderated' => $isModerated,
            'raw_pricing' => $pricing,
        ];
    }

    /**
     * Resolve model architecture description.
     *
     * @param  array<string, mixed>  $orModel
     * @param  array<string, mixed>|null  $llmSpec
     */
    protected function resolveArchitecture(array $orModel, ?array $llmSpec): ?string
    {
        if (isset($orModel['architecture']['modality'])) {
            return $orModel['architecture']['modality'];
        }

        if (isset($llmSpec['mode'])) {
            return $llmSpec['mode'];
        }

        return 'text->text';
    }

    /**
     * Determine if model is open weights / open source.
     *
     * @param  array<string, mixed>  $orModel
     * @param  array<string, mixed>|null  $llmSpec
     */
    protected function determineIsOpenSource(string $providerSlug, array $orModel, ?array $llmSpec): bool
    {
        if (in_array(strtolower($providerSlug), $this->openSourceProviders, true)) {
            return true;
        }

        $desc = strtolower($orModel['description'] ?? '');
        if (str_contains($desc, 'open-source') || str_contains($desc, 'open weights') || str_contains($desc, 'apache 2.0') || str_contains($desc, 'mit license')) {
            return true;
        }

        return false;
    }

    /**
     * Ensure Arabic benchmark metric baseline is initialized.
     */
    protected function ensureArabicBenchmark(AiModel $model, ?float $outputCostPer1m): void
    {
        // Standard Arabic baseline: ~1,550 tokens per 1,000 Arabic words (inflation 1.55x)
        $tokensPer1k = 1550.0;
        $inflationRatio = 1.55;
        $costPer1kWords = null;

        if ($outputCostPer1m !== null && $outputCostPer1m > 0) {
            $costPer1kWords = ($tokensPer1k / 1_000_000) * $outputCostPer1m;
        }

        BenchmarkArabic::firstOrCreate(
            ['model_id' => $model->id],
            [
                'arabic_tokens_per_1k_words' => $tokensPer1k,
                'token_inflation_ratio' => $inflationRatio,
                'effective_arabic_cost_per_1k_words' => $costPer1kWords !== null ? round($costPer1kWords, 6) : null,
            ]
        );
    }

    /**
     * Prune invalid duplicate models created from hosting endpoints / regional cloud prefixes.
     */
    public function pruneInvalidDuplicateModels(): int
    {
        $invalidSlugs = [
            'anthropicclaude%',
            'apacanthropic%',
            'usanthropic%',
            'euanthropic%',
            'auanthropic%',
            'jpanthropic%',
            'globalanthropic%',
            'us-govanthropic%',
            'amazonnova%',
            'metallama%',
            'coherecommand%',
        ];

        $invalidProviderSlugs = [
            'bedrock_converse',
            'bedrock',
            'vertex_ai-language-models',
            'vertex_ai-embedding-models',
            'vertex_ai-text-models',
            'together_ai',
            'fireworks_ai',
            'fireworks_ai-embedding-models',
            'text-completion-openai',
            'nlp_cloud',
            'cohere_chat',
        ];

        $query = AiModel::whereNull('openrouter_id')
            ->where(function ($q) use ($invalidSlugs, $invalidProviderSlugs) {
                foreach ($invalidSlugs as $pattern) {
                    $q->orWhere('slug', 'like', $pattern);
                }
                $q->orWhereHas('provider', function ($pq) use ($invalidProviderSlugs) {
                    $pq->whereIn('slug', $invalidProviderSlugs);
                });
            });

        $invalidModels = $query->get();
        $count = $invalidModels->count();

        foreach ($invalidModels as $model) {
            $model->offerings()->delete();
            $model->specs()->delete();
            $model->benchmarkGeneral()->delete();
            $model->benchmarkArabic()->delete();
            $model->delete();
        }

        // Delete orphan invalid providers
        Provider::whereIn('slug', $invalidProviderSlugs)
            ->whereDoesntHave('models')
            ->whereDoesntHave('offerings')
            ->delete();

        if ($count > 0) {
            Log::info("Pruned {$count} invalid duplicate models and their relations.");
        }

        return $count;
    }
}
