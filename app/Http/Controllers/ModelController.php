<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use App\Services\ModelRankingService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ModelController extends Controller
{
    /**
     * Capability flags exposed on the profile page, mapped to Arabic labels.
     *
     * @var array<string, string>
     */
    private const CAPABILITY_LABELS = [
        'supports_vision' => 'دعم الصور',
        'supports_reasoning' => 'استدلال متقدم',
        'supports_web_search' => 'بحث على الويب',
        'supports_function_calling' => 'استدعاء الأدوات',
        'supports_tool_choice' => 'اختيار الأدوات',
        'supports_prompt_caching' => 'تخزين الموجهات مؤقتاً',
        'supports_response_schema' => 'مخرجات منظمة (JSON Schema)',
        'supports_pdf_input' => 'إدخال ملفات PDF',
        'supports_audio_input' => 'إدخال صوتي',
        'supports_audio_output' => 'إخراج صوتي',
        'supports_computer_use' => 'التحكم بالحاسوب',
        'supports_native_streaming' => 'بث أصلي',
    ];

    /**
     * Display the public profile page for a single model.
     */
    public function show(AiModel $model, ModelRankingService $ranking): View
    {
        abort_unless($model->is_active, 404);

        $model->load(['provider', 'latestSpec', 'benchmarkGeneral', 'benchmarkArabic', 'latestBenchmarkRun']);

        ['general' => $general, 'arabic' => $arabic, 'pool' => $pool] = $ranking->leaderboards();

        $spec = $model->latestSpec;
        $benchmark = $model->benchmarkArabic;
        $run = $model->latestBenchmarkRun;
        $hasArabic = $benchmark?->tested_at !== null;

        $categories = collect(data_get($run?->scores_breakdown, 'categories', []))
            ->map(fn (array $category, string $key): array => [
                'key' => $key,
                'name' => $category['name'] ?? $key,
                'earned' => (float) ($category['earned'] ?? 0),
                'points' => (float) ($category['points'] ?? 0),
                'percentage' => (float) ($category['percentage'] ?? 0),
                'status' => $category['status'] ?? '',
            ])
            ->values()
            ->all();

        $tokensPerWord = $hasArabic ? (float) $benchmark->tokens_per_word : null;
        $inPrice = $spec?->input_cost_per_1m !== null ? (float) $spec->input_cost_per_1m : null;
        $outPrice = $spec?->output_cost_per_1m !== null ? (float) $spec->output_cost_per_1m : null;

        $costPer1kWords = $benchmark?->effective_arabic_cost_per_1k_words !== null
            ? (float) $benchmark->effective_arabic_cost_per_1k_words
            : ($tokensPerWord !== null && $inPrice !== null && $outPrice !== null
                ? round($tokensPerWord * 1000 * (($inPrice * 0.75) + ($outPrice * 0.25)) / 1_000_000, 6)
                : null);

        return view('models.show', [
            'model' => $model,
            'hasArabic' => $hasArabic,
            'rank' => [
                'general' => $this->rankIn($general, $model),
                'arabic' => $this->rankIn($arabic, $model),
                'total' => $pool->count(),
            ],
            'categories' => $categories,
            'capabilities' => $this->capabilities($model),
            'pricing' => [
                'input' => $inPrice,
                'output' => $outPrice,
                'cache_read' => $spec?->input_cache_read_cost_per_1m !== null ? (float) $spec->input_cache_read_cost_per_1m : null,
                'cache_write' => $spec?->input_cache_write_cost_per_1m !== null ? (float) $spec->input_cache_write_cost_per_1m : null,
                'tokens_per_word' => $tokensPerWord,
                'cost_per_1k_words' => $costPer1kWords,
                'cost_per_1k_words_sar' => $costPer1kWords !== null ? round($costPer1kWords * 3.75, 6) : null,
            ],
            'alternatives' => $this->alternatives($pool, $model),
            'accentVar' => $this->accentVar($model->provider?->slug),
            'services' => BlogController::promotedServices(),
            'relatedPosts' => collect(BlogController::posts())->take(2)->values()->all(),
        ]);
    }

    /**
     * Position of a model inside a ranked collection (1-based), or null when unranked.
     *
     * @param  Collection<int, AiModel>  $models
     */
    private function rankIn(Collection $models, AiModel $model): ?int
    {
        $index = $models->search(fn (AiModel $candidate): bool => $candidate->is($model));

        return $index === false ? null : $index + 1;
    }

    /**
     * Arabic labels for the capabilities this model advertises.
     *
     * @return list<string>
     */
    private function capabilities(AiModel $model): array
    {
        return collect(self::CAPABILITY_LABELS)
            ->filter(fn (string $label, string $flag): bool => (bool) data_get($model->capabilities, $flag, false))
            ->values()
            ->all();
    }

    /**
     * Pick up to three comparable models, ranked by how close their intelligence
     * index is to this model (falling back to Arabic score when it is missing).
     *
     * @param  Collection<int, AiModel>  $pool
     * @return list<array{name: string, slug: string, provider: string, intelligence: float|null, score: int|null, in_price: float|null, is_open_source: bool}>
     */
    private function alternatives(Collection $pool, AiModel $model): array
    {
        $targetIntelligence = (float) ($model->benchmarkGeneral?->intelligence_index ?? 0);

        $ordered = $pool
            ->reject(fn (AiModel $candidate): bool => $candidate->is($model))
            ->sortBy(function (AiModel $candidate) use ($targetIntelligence): float {
                $intelligence = $candidate->benchmarkGeneral?->intelligence_index;

                if ($intelligence !== null) {
                    return abs((float) $intelligence - $targetIntelligence);
                }

                return 500 - (float) ($candidate->benchmarkArabic?->total_score ?? 0) * 3;
            })
            ->take(3);

        return $ordered->map(fn (AiModel $candidate): array => [
            'name' => $candidate->name,
            'slug' => $candidate->slug,
            'provider' => $candidate->provider?->name ?? 'Unknown',
            'intelligence' => $candidate->benchmarkGeneral?->intelligence_index !== null
                ? (float) $candidate->benchmarkGeneral->intelligence_index
                : null,
            'score' => $candidate->benchmarkArabic?->total_score,
            'in_price' => $candidate->latestSpec?->input_cost_per_1m !== null
                ? (float) $candidate->latestSpec->input_cost_per_1m
                : null,
            'is_open_source' => (bool) $candidate->is_open_source,
        ])->values()->all();
    }

    /**
     * CSS custom property holding the provider accent color for this model.
     */
    private function accentVar(?string $providerSlug): string
    {
        $known = ['openai', 'anthropic', 'google', 'deepseek', 'meta'];

        return in_array($providerSlug, $known, true)
            ? "--color-provider-{$providerSlug}"
            : '--color-primary';
    }
}
