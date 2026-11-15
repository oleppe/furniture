<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use App\Models\ArabicBenchmarkRun;
use App\Services\ModelRankingService;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public Idraak Lab landing page with the live model leaderboard.
     */
    public function index(ModelRankingService $ranking): View
    {
        // Models ranked by the OpenRouter Artificial Analysis intelligence index,
        // plus every model that has a completed Arabic benchmark run.
        ['arabic' => $arabicLeaderboard, 'pool' => $leaderboardModels] = $ranking->leaderboards();

        // Platform-wide live telemetry statistics
        $successfulRuns = ArabicBenchmarkRun::where('status', 'completed');
        $stats = [
            'tested_models' => $arabicLeaderboard->count(),
            'total_runs' => ArabicBenchmarkRun::count(),
            'avg_tokens_per_word' => round((float) (clone $successfulRuns)->where('tokens_per_word', '>', 0)->avg('tokens_per_word'), 2),
            'avg_tokens_per_second' => round((float) (clone $successfulRuns)->where('tokens_per_second', '>', 0)->avg('tokens_per_second'), 2),
            'avg_latency' => round((float) (clone $successfulRuns)->where('latency_seconds', '>', 0)->avg('latency_seconds'), 3),
            'perfect_scores' => ArabicBenchmarkRun::where('score_percentage', '>=', 100)->count(),
            'catalog_models' => AiModel::where('is_active', true)->count(),
        ];

        // Weekly highlight cards derived from the live leaderboard data
        $bestArabic = $arabicLeaderboard->first();
        $fastestModel = $arabicLeaderboard
            ->sortByDesc(fn (AiModel $model): float => (float) $model->benchmarkArabic->tokens_per_second)
            ->first();
        $cheapestModel = $leaderboardModels
            ->filter(fn (AiModel $model): bool => $model->latestSpec?->input_cost_per_1m !== null)
            ->sortBy(fn (AiModel $model): float => (float) $model->latestSpec->input_cost_per_1m)
            ->first();

        return view('home', [
            'leaderboard' => $leaderboardModels->take(8),
            'rows' => $ranking->rows($leaderboardModels),
            'stats' => $stats,
            'highlights' => [
                'best_arabic' => $bestArabic,
                'fastest' => $fastestModel,
                'cheapest' => $cheapestModel,
            ],
            'calcModels' => $ranking->calculatorModels($arabicLeaderboard),
        ]);
    }
}
