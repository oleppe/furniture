<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CatalogPricingFetcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogSyncController extends Controller
{
    /**
     * Trigger the OpenRouter & LiteLLM catalog and pricing sync pipeline.
     */
    public function sync(Request $request, CatalogPricingFetcherService $fetcher): JsonResponse|RedirectResponse
    {
        // Enforce lock to prevent simultaneous duplicate executions
        $lock = Cache::lock('catalog_sync_lock', 60);

        if (! $lock->get()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A sync operation is already in progress. Please wait a moment.',
                ], 429);
            }

            return back()->with('error', 'A sync operation is currently in progress.');
        }

        try {
            $stats = $fetcher->syncCatalog();
            Cache::put('last_catalog_sync_at', now()->toIso8601String(), now()->addDays(30));
            Cache::put('last_catalog_sync_stats', $stats, now()->addDays(30));

            $message = "Catalog synced successfully: {$stats['models']} models, {$stats['providers']} providers, and {$stats['offerings']} offerings updated.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'stats' => $stats,
                    'synced_at' => now()->diffForHumans(),
                ]);
            }

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sync failed: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Sync failed: '.$e->getMessage());
        } finally {
            $lock->release();
        }
    }
}
