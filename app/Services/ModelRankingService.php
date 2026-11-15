<?php

namespace App\Services;

use App\Models\AiModel;
use App\Models\BenchmarkArabic;
use App\Models\BenchmarkGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ModelRankingService
{
    /**
     * Number of intelligence-ranked models included in the leaderboard payload.
     *
     * Arabic-tested models are always appended so the client can switch rankings.
     */
    public const GENERAL_LEADERBOARD_LIMIT = 50;

    /**
     * Maximum score a model can earn in the Arabic benchmark.
     */
    public const ARABIC_SCORE_MAX = 156;

    /**
     * Load the models that can appear on the public leaderboard, ranked both ways.
     *
     * The general ranking uses the Artificial Analysis intelligence index that OpenRouter
     * publishes per model. The Arabic ranking uses our own benchmark score. The pool is
     * the union of the top general models and every Arabic-tested model, so switching the
     * ranking on the landing page does not require another request.
     *
     * @return array{
     *     general: Collection<int, AiModel>,
     *     arabic: Collection<int, AiModel>,
     *     pool: Collection<int, AiModel>
     * }
     */
    public function leaderboards(): array
    {
        $models = AiModel::query()
            ->with(['provider', 'latestSpec', 'benchmarkGeneral', 'benchmarkArabic'])
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereIn('id', BenchmarkGeneral::query()->select('model_id')->whereNotNull('intelligence_index'))
                    ->orWhereIn('id', BenchmarkArabic::query()->select('model_id')->whereNotNull('tested_at'));
            })
            ->get();

        $general = $models
            ->filter(fn (AiModel $model): bool => $model->benchmarkGeneral?->intelligence_index !== null)
            ->sortByDesc(fn (AiModel $model): float => (float) $model->benchmarkGeneral->intelligence_index)
            ->values();

        $arabic = $models
            ->filter(fn (AiModel $model): bool => $model->benchmarkArabic?->tested_at !== null)
            ->sortBy([
                fn (AiModel $a, AiModel $b): int => (int) $b->benchmarkArabic->total_score <=> (int) $a->benchmarkArabic->total_score,
                fn (AiModel $a, AiModel $b): int => (float) $b->benchmarkArabic->tokens_per_second <=> (float) $a->benchmarkArabic->tokens_per_second,
            ])
            ->values();

        $pool = $general
            ->take(self::GENERAL_LEADERBOARD_LIMIT)
            ->merge($arabic)
            ->unique('id')
            ->values();

        return compact('general', 'arabic', 'pool');
    }

    /**
     * Shape leaderboard models into lightweight rows for the interactive table.
     *
     * @param  Collection<int, AiModel>  $models
     * @return list<array<string, mixed>>
     */
    public function rows(Collection $models): array
    {
        return $models->values()->map(function (AiModel $model): array {
            $benchmark = $model->benchmarkArabic;
            $general = $model->benchmarkGeneral;
            $hasArabicBenchmark = $benchmark?->tested_at !== null;

            // Stored as combined "mode:level" (e.g. "effort:xhigh")
            [$thinkingMode, $thinkingLevel] = array_pad(
                explode(':', (string) ($benchmark?->thinking_mode ?? 'none')),
                2,
                null
            );

            return [
                'name' => $model->name,
                'slug' => $model->slug,
                'url' => route('models.show', $model->slug),
                'provider' => $model->provider?->name ?? 'Unknown',
                'provider_slug' => $model->provider?->slug ?? '',
                'is_open_source' => (bool) $model->is_open_source,
                'supports_vision' => (bool) data_get($model->capabilities, 'supports_vision', false),
                'architecture' => $model->architecture,
                'description' => $model->description
                    ? Str::limit($model->description, 90)
                    : "نموذج من {$model->provider?->name} مفحوص ضمن منصة إدراك Lab.",
                'has_arabic' => $hasArabicBenchmark,
                'intelligence' => $general?->intelligence_index !== null ? (float) $general->intelligence_index : null,
                'coding_index' => $general?->coding_index !== null ? (float) $general->coding_index : null,
                'compression' => $hasArabicBenchmark ? (float) $benchmark->tokens_per_word : null,
                'score' => $hasArabicBenchmark ? (int) $benchmark->total_score : null,
                'score_max' => self::ARABIC_SCORE_MAX,
                'score_pct' => $hasArabicBenchmark && $benchmark->total_score > 0
                    ? round((int) $benchmark->total_score / self::ARABIC_SCORE_MAX * 100, 1)
                    : null,
                'tps' => $hasArabicBenchmark ? (float) $benchmark->tokens_per_second : null,
                'latency' => $hasArabicBenchmark ? (float) $benchmark->latency_seconds : null,
                'thinking_mode' => $thinkingMode ?: 'none',
                'thinking_level' => $thinkingLevel,
                'in_price' => (float) ($model->latestSpec?->input_cost_per_1m ?? 0),
                'out_price' => (float) ($model->latestSpec?->output_cost_per_1m ?? 0),
                'context' => $model->latestSpec?->context_window,
                'tested_at' => $benchmark?->tested_at?->toDateString(),
            ];
        })->all();
    }

    /**
     * Shape three distinct models for the interactive cost calculator.
     *
     * Each slot prefers its true superlative (cheapest, fastest, smartest) but falls
     * back to the next-best model when that model already fills another slot, with
     * an honest tag reflecting the fallback.
     *
     * @param  Collection<int, AiModel>  $models
     * @return list<array<string, mixed>>
     */
    public function calculatorModels(Collection $models): array
    {
        $priced = $models->filter(fn (AiModel $model): bool => $model->latestSpec?->input_cost_per_1m !== null);

        $byCheapest = $priced->sortBy(fn (AiModel $model): float => (float) $model->latestSpec->input_cost_per_1m)->values();
        $byFastest = $models->sortByDesc(fn (AiModel $model): float => (float) $model->benchmarkArabic->tokens_per_second)->values();
        $byScore = $models->sortByDesc(fn (AiModel $model): int => (int) $model->benchmarkArabic->total_score)->values();

        $slots = [
            ['queue' => $byCheapest, 'tag' => 'الأوفر على الإطلاق', 'fallbackTag' => 'خيار اقتصادي'],
            ['queue' => $byFastest, 'tag' => 'الأعلى سرعة', 'fallbackTag' => 'سرعة عالية'],
            ['queue' => $byScore, 'tag' => 'الأذكى والأدق', 'fallbackTag' => 'دقة عالية'],
        ];

        $picked = collect();
        $rows = [];

        foreach ($slots as $slot) {
            $model = $slot['queue']->first(fn (AiModel $candidate): bool => ! $picked->contains('id', $candidate->id));
            if ($model === null) {
                continue;
            }
            $picked->push($model);

            $rows[] = [
                'name' => $model->name,
                'tag' => $model->is($slot['queue']->first()) ? $slot['tag'] : $slot['fallbackTag'],
                'tokens_per_word' => (float) ($model->benchmarkArabic?->tokens_per_word ?? 2.0),
                'in_price' => (float) ($model->latestSpec?->input_cost_per_1m ?? 0),
                'out_price' => (float) ($model->latestSpec?->output_cost_per_1m ?? 0),
            ];
        }

        return $rows;
    }
}
