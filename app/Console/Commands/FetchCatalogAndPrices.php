<?php

namespace App\Console\Commands;

use App\Services\CatalogPricingFetcherService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('catalog:fetch-prices {--limit= : Limit the number of models to process} {--prune : Prune corrupted and duplicate models before sync}')]
#[Description('Fetch and sync AI model catalog and pricing from OpenRouter and LiteLLM')]
class FetchCatalogAndPrices extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CatalogPricingFetcherService $fetcher): int
    {
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $shouldPrune = (bool) $this->option('prune');

        if ($shouldPrune) {
            $this->info('Pruning corrupted duplicate models...');
            $prunedCount = $fetcher->pruneInvalidDuplicateModels();
            $this->comment("Pruned {$prunedCount} corrupted duplicate models.");
        }

        $this->info('Starting catalog and pricing sync from OpenRouter & LiteLLM...');
        if ($limit !== null) {
            $this->comment("Limiting processing to {$limit} models.");
        }

        try {
            $stats = $fetcher->syncCatalog($limit);

            $this->newLine();
            $this->info('✓ Catalog and pricing sync completed successfully:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Providers Synced', $stats['providers']],
                    ['Models Synced', $stats['models']],
                    ['Specs & Baseline Pricing Recorded', $stats['specs']],
                    ['Hosting Offerings (Multi-Provider)', $stats['offerings']],
                    ['LiteLLM Cross-Referenced Matches', $stats['litellm_matched']],
                ]
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to sync catalog and prices: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
