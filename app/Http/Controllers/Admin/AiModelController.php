<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiModelController extends Controller
{
    /**
     * Display the AI model catalog with high-density pricing and specification filters.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim();
        $providerId = $request->input('provider_id');
        $openSource = $request->input('open_source');
        $isActive = $request->input('is_active');
        $isFeatured = $request->input('is_featured');
        $sortBy = $request->input('sort_by', 'latest');

        $query = AiModel::with([
            'provider',
            'latestSpec',
            'cheapestOffering.provider',
            'officialOffering.provider',
        ])->withCount('offerings');

        if ($search->isNotEmpty()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('openrouter_id', 'like', "%{$search}%")
                    ->orWhereHas('provider', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        if (! empty($providerId)) {
            $query->where('provider_id', $providerId);
        }

        if ($openSource !== null && $openSource !== '') {
            $query->where('is_open_source', (bool) $openSource);
        }

        if ($isActive !== null && $isActive !== '') {
            $query->where('is_active', (bool) $isActive);
        }

        if ($isFeatured !== null && $isFeatured !== '') {
            $query->where('is_featured', (bool) $isFeatured);
        }

        match ($sortBy) {
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'oldest' => $query->orderBy('id', 'asc'),
            'featured' => $query->orderByDesc('is_featured')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        $models = $query->paginate(20)->withQueryString();

        $providers = Provider::orderBy('name')->get(['id', 'name']);

        $stats = [
            'total' => AiModel::count(),
            'active' => AiModel::where('is_active', true)->count(),
            'featured' => AiModel::where('is_featured', true)->count(),
            'open_source' => AiModel::where('is_open_source', true)->count(),
        ];

        return view('admin.models.index', compact(
            'models',
            'providers',
            'stats',
            'search',
            'providerId',
            'openSource',
            'isActive',
            'isFeatured',
            'sortBy'
        ));
    }

    /**
     * Display a detailed profile page for a specific AI model.
     */
    public function show(AiModel $model): View
    {
        $model->load([
            'provider',
            'latestSpec',
            'offerings.provider',
            'benchmarkGeneral',
            'benchmarkArabic',
            'latestSpeed',
        ]);

        return view('admin.models.show', compact('model'));
    }

    /**
     * Fetch all hosting endpoints, specifications, benchmarks, and database fields for the slide-over inspection drawer.
     */
    public function offerings(AiModel $model): JsonResponse
    {
        $model->load([
            'provider',
            'latestSpec',
            'offerings.provider',
            'benchmarkGeneral',
            'benchmarkArabic',
            'latestSpeed',
        ]);

        $offerings = $model->offerings->map(function ($offering) {
            return [
                'id' => $offering->id,
                'provider_name' => $offering->provider?->name ?? 'Unknown',
                'provider_slug' => $offering->provider?->slug,
                'provider_logo_url' => $offering->provider?->logo_url,
                'endpoint_key' => $offering->endpoint_key,
                'host_type' => $offering->host_type,
                'region' => $offering->region,
                'input_cost_per_1m' => (float) $offering->input_cost_per_1m,
                'output_cost_per_1m' => (float) $offering->output_cost_per_1m,
                'batch_input_cost_per_1m' => (float) ($offering->batch_input_cost_per_1m ?? 0),
                'batch_output_cost_per_1m' => (float) ($offering->batch_output_cost_per_1m ?? 0),
                'cache_read_cost_per_1m' => (float) $offering->cache_read_input_token_cost_per_1m,
                'tokens_per_sec' => (float) $offering->tokens_per_sec,
                'ttft_ms' => (float) $offering->ttft_ms,
                'is_official_direct' => (bool) $offering->is_official_direct,
                'is_cheapest' => (bool) $offering->is_cheapest,
            ];
        })->sortBy('input_cost_per_1m')->values();

        return response()->json([
            'model' => [
                'id' => $model->id,
                'name' => $model->name,
                'slug' => $model->slug,
                'canonical_slug' => $model->canonical_slug,
                'hugging_face_id' => $model->hugging_face_id,
                'openrouter_id' => $model->openrouter_id,
                'litellm_id' => $model->litellm_id,
                'provider' => $model->provider?->name,
                'provider_details' => $model->provider ? [
                    'id' => $model->provider->id,
                    'name' => $model->provider->name,
                    'slug' => $model->provider->slug,
                    'website' => $model->provider->website,
                    'logo_url' => $model->provider->logo_url,
                ] : null,
                'description' => $model->description,
                'architecture' => $model->architecture,
                'architecture_details' => $model->architecture_details,
                'capabilities' => $model->capabilities,
                'supported_parameters' => $model->supported_parameters,
                'default_parameters' => $model->default_parameters,
                'supported_voices' => $model->supported_voices,
                'reasoning_spec' => $model->reasoning_spec,
                'per_request_limits' => $model->per_request_limits,
                'knowledge_cutoff' => $model->knowledge_cutoff,
                'expiration_date' => $model->expiration_date,
                'alias_target' => $model->alias_target,
                'release_date' => $model->release_date?->format('Y-m-d'),
                'release_date_formatted' => $model->release_date?->toFormattedDateString(),
                'is_open_source' => (bool) $model->is_open_source,
                'is_active' => (bool) $model->is_active,
                'is_featured' => (bool) $model->is_featured,
                'context_window' => $model->latestSpec?->context_window,
                'max_output_tokens' => $model->latestSpec?->max_output_tokens,
                'baseline_input_cost' => (float) ($model->latestSpec?->input_cost_per_1m ?? 0),
                'baseline_output_cost' => (float) ($model->latestSpec?->output_cost_per_1m ?? 0),
                'openrouter_url' => $model->openrouter_details_url,
                'show_url' => route('admin.models.show', $model),
                'created_at' => $model->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $model->updated_at?->format('Y-m-d H:i:s'),
            ],
            'spec' => $model->latestSpec ? [
                'id' => $model->latestSpec->id,
                'context_window' => $model->latestSpec->context_window,
                'max_output_tokens' => $model->latestSpec->max_output_tokens,
                'top_provider_context_length' => $model->latestSpec->top_provider_context_length,
                'top_provider_max_completion_tokens' => $model->latestSpec->top_provider_max_completion_tokens,
                'is_moderated' => (bool) $model->latestSpec->is_moderated,
                'input_cost_per_1m' => $model->latestSpec->input_cost_per_1m !== null ? (float) $model->latestSpec->input_cost_per_1m : null,
                'output_cost_per_1m' => $model->latestSpec->output_cost_per_1m !== null ? (float) $model->latestSpec->output_cost_per_1m : null,
                'input_cache_read_cost_per_1m' => $model->latestSpec->input_cache_read_cost_per_1m !== null ? (float) $model->latestSpec->input_cache_read_cost_per_1m : null,
                'input_cache_write_cost_per_1m' => $model->latestSpec->input_cache_write_cost_per_1m !== null ? (float) $model->latestSpec->input_cache_write_cost_per_1m : null,
                'input_cache_write_1h_cost_per_1m' => $model->latestSpec->input_cache_write_1h_cost_per_1m !== null ? (float) $model->latestSpec->input_cache_write_1h_cost_per_1m : null,
                'web_search_cost' => $model->latestSpec->web_search_cost !== null ? (float) $model->latestSpec->web_search_cost : null,
                'internal_reasoning_cost_per_1m' => $model->latestSpec->internal_reasoning_cost_per_1m !== null ? (float) $model->latestSpec->internal_reasoning_cost_per_1m : null,
                'image_input_cost' => $model->latestSpec->image_input_cost !== null ? (float) $model->latestSpec->image_input_cost : null,
                'image_output_cost' => $model->latestSpec->image_output_cost !== null ? (float) $model->latestSpec->image_output_cost : null,
                'audio_input_cost_per_1m' => $model->latestSpec->audio_input_cost_per_1m !== null ? (float) $model->latestSpec->audio_input_cost_per_1m : null,
                'audio_output_cost_per_1m' => $model->latestSpec->audio_output_cost_per_1m !== null ? (float) $model->latestSpec->audio_output_cost_per_1m : null,
                'pricing_overrides' => $model->latestSpec->pricing_overrides,
                'raw_pricing' => $model->latestSpec->raw_pricing,
            ] : null,
            'benchmark_general' => $model->benchmarkGeneral ? [
                'elo_rating' => $model->benchmarkGeneral->elo_rating !== null ? (float) $model->benchmarkGeneral->elo_rating : null,
                'intelligence_index' => $model->benchmarkGeneral->intelligence_index !== null ? (float) $model->benchmarkGeneral->intelligence_index : null,
                'coding_index' => $model->benchmarkGeneral->coding_index !== null ? (float) $model->benchmarkGeneral->coding_index : null,
                'agentic_index' => $model->benchmarkGeneral->agentic_index !== null ? (float) $model->benchmarkGeneral->agentic_index : null,
                'mmlu_score' => $model->benchmarkGeneral->mmlu_score !== null ? (float) $model->benchmarkGeneral->mmlu_score : null,
                'code_eval_score' => $model->benchmarkGeneral->code_eval_score !== null ? (float) $model->benchmarkGeneral->code_eval_score : null,
                'math_score' => $model->benchmarkGeneral->math_score !== null ? (float) $model->benchmarkGeneral->math_score : null,
                'design_arena' => $model->benchmarkGeneral->design_arena,
                'raw_benchmarks' => $model->benchmarkGeneral->raw_benchmarks,
                'source_updated_at' => $model->benchmarkGeneral->source_updated_at?->diffForHumans(),
            ] : null,
            'benchmark_arabic' => $model->benchmarkArabic ? [
                'arabic_tokens_per_1k_words' => (float) $model->benchmarkArabic->arabic_tokens_per_1k_words,
                'token_inflation_ratio' => (float) $model->benchmarkArabic->token_inflation_ratio,
                'effective_arabic_cost_per_1k_words' => (float) $model->benchmarkArabic->effective_arabic_cost_per_1k_words,
            ] : null,
            'speed' => $model->latestSpeed ? [
                'ttft_ms' => (float) $model->latestSpeed->ttft_ms,
                'tokens_per_sec' => (float) $model->latestSpeed->tokens_per_sec,
                'region' => $model->latestSpeed->region,
                'tested_at' => $model->latestSpeed->tested_at?->diffForHumans(),
            ] : null,
            'offerings' => $offerings,
            'raw_database_record' => [
                'model' => $model->toArray(),
                'latest_spec' => $model->latestSpec?->toArray(),
                'benchmark_general' => $model->benchmarkGeneral?->toArray(),
                'benchmark_arabic' => $model->benchmarkArabic?->toArray(),
                'latest_speed' => $model->latestSpeed?->toArray(),
            ],
        ]);
    }

    /**
     * Toggle the model's active visibility state.
     */
    public function toggleActive(Request $request, AiModel $model): JsonResponse|RedirectResponse
    {
        $model->is_active = ! $model->is_active;
        $model->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $model->is_active,
                'message' => "Model '{$model->name}' is now ".($model->is_active ? 'active' : 'inactive').'.',
            ]);
        }

        return back()->with('success', "Model '{$model->name}' active state updated.");
    }

    /**
     * Toggle the model's featured showcase state.
     */
    public function toggleFeatured(Request $request, AiModel $model): JsonResponse|RedirectResponse
    {
        $model->is_featured = ! $model->is_featured;
        $model->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_featured' => $model->is_featured,
                'message' => "Model '{$model->name}' is now ".($model->is_featured ? 'featured' : 'unfeatured').'.',
            ]);
        }

        return back()->with('success', "Model '{$model->name}' featured state updated.");
    }
}
