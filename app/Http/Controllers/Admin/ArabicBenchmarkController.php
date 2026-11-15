<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\ArabicBenchmarkRun;
use App\Models\Provider;
use App\Services\ArabicBenchmarkService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArabicBenchmarkController extends Controller
{
    public function __construct(
        protected ArabicBenchmarkService $benchmarkService
    ) {}

    /**
     * Display the Arabic Benchmark Lab test execution environment and historical runs.
     */
    public function index(Request $request): View
    {
        $hasConfiguredApiKey = ! empty($this->benchmarkService->resolveApiKey());

        // Retrieve active models with their specifications and provider metadata
        $models = AiModel::with(['provider', 'latestSpec', 'benchmarkArabic'])
            ->whereNotNull('openrouter_id')
            ->orderBy('name')
            ->get(['id', 'provider_id', 'name', 'slug', 'openrouter_id', 'architecture']);

        // Historical benchmark runs across all tested models
        $runs = ArabicBenchmarkRun::with(['aiModel.provider'])->latest()->paginate(20)->withQueryString();

        // Platform Arabic Benchmark Summary Metrics
        $totalRuns = ArabicBenchmarkRun::count();
        $successfulRuns = ArabicBenchmarkRun::where('status', 'completed');

        $avgTokensPerWord = $successfulRuns->where('tokens_per_word', '>', 0)->avg('tokens_per_word');
        $avgTokensPerSec = $successfulRuns->where('tokens_per_second', '>', 0)->avg('tokens_per_second');
        $avgLatency = $successfulRuns->where('latency_seconds', '>', 0)->avg('latency_seconds');
        $perfectScoreRuns = ArabicBenchmarkRun::where('score_percentage', '>=', 100)->orWhere('total_score', '>=', 156)->count();

        // Formatted lightweight model list for searchable client-side combobox
        $modelsList = $models->map(function (AiModel $m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'openrouter_id' => $m->openrouter_id,
                'provider' => $m->provider?->name ?? 'Unknown',
                'provider_slug' => $m->provider?->slug ?? '',
                'input_cost' => (float) ($m->latestSpec?->input_cost_per_1m ?? 0.0),
                'output_cost' => (float) ($m->latestSpec?->output_cost_per_1m ?? 0.0),
                'context_window' => $m->latestSpec?->context_window ? number_format($m->latestSpec->context_window) : null,
            ];
        })->values();

        // Check if an existing run is requested to be initially inspected/loaded in dashboard
        $initialResult = null;
        if ($request->filled('run')) {
            $requestedRun = ArabicBenchmarkRun::with(['aiModel.provider', 'aiModel.latestSpec'])->find($request->input('run'));
            if ($requestedRun) {
                $initialResult = $this->benchmarkService->formatRunAsResult($requestedRun);
            }
        }

        return view('admin.benchmarks.arabic', [
            'models' => $models,
            'modelsList' => $modelsList,
            'runs' => $runs,
            'masterPrompt' => ArabicBenchmarkService::MASTER_PROMPT,
            'promptWordCount' => $this->benchmarkService->getPromptWordCount(),
            'hasConfiguredApiKey' => $hasConfiguredApiKey,
            'initialResult' => $initialResult,
            'stats' => [
                'total_runs' => $totalRuns,
                'avg_tokens_per_word' => $avgTokensPerWord ? round($avgTokensPerWord, 2) : 0,
                'avg_tokens_per_sec' => $avgTokensPerSec ? round($avgTokensPerSec, 1) : 0,
                'avg_latency' => $avgLatency ? round($avgLatency, 2) : 0,
                'perfect_score_runs' => $perfectScoreRuns,
            ],
        ]);
    }

    /**
     * Execute the 5-task Arabic benchmark test for the chosen model.
     */
    public function run(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model_id' => ['nullable', 'integer'],
            'openrouter_model_id' => ['required', 'string', 'max:255'],
            'thinking_mode' => ['nullable', 'string', 'in:none,effort,custom_tokens'],
            'thinking_level' => ['nullable', 'string', 'in:none,minimal,low,medium,high,xhigh'],
            'reasoning_tokens' => ['nullable', 'integer', 'min:1', 'max:64000'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'input_cost_per_1m' => ['nullable', 'numeric', 'min:0'],
            'output_cost_per_1m' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $result = $this->benchmarkService->runBenchmark($validated);

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * View detailed JSON data for an individual benchmark run.
     */
    public function showRun(ArabicBenchmarkRun $run): JsonResponse|RedirectResponse
    {
        $run->load(['aiModel.provider', 'aiModel.latestSpec']);

        if (! request()->wantsJson() && ! request()->ajax()) {
            return redirect()->route('admin.benchmarks.arabic', ['run' => $run->id]);
        }

        return response()->json([
            'success' => true,
            'run' => $run,
            'formatted' => $this->benchmarkService->formatRunAsResult($run),
        ]);
    }

    /**
     * Delete an individual benchmark run record.
     */
    public function destroyRun(ArabicBenchmarkRun $run): RedirectResponse|JsonResponse
    {
        $run->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.benchmarks.arabic')
            ->with('status', 'Benchmark run deleted successfully.');
    }
}
