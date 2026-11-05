<?php

namespace App\Console\Commands;

use App\Models\PriceRevision;
use App\Services\DynamicPriceRevisionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledPriceRevisions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'price-revisions:process {--revision-id= : Optional specific revision ID to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes and activates scheduled dynamic price revisions whose effective timestamp has arrived.';

    protected DynamicPriceRevisionService $pricingService;

    public function __construct(DynamicPriceRevisionService $pricingService)
    {
        parent::__construct();
        $this->pricingService = $pricingService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $specificId = $this->option('revision-id');

        if ($specificId) {
            $revisions = PriceRevision::where('id', $specificId)->get();
        } else {
            $revisions = PriceRevision::whereIn('status', ['scheduled', 'approved'])
                ->where('effective_at', '<=', now())
                ->orderBy('effective_at', 'asc')
                ->get();
        }

        if ($revisions->isEmpty()) {
            $this->info('No pending scheduled price revisions to process.');
            return 0;
        }

        $this->info(sprintf('Processing %d scheduled price revision(s)...', $revisions->count()));

        $successCount = 0;
        $failedCount = 0;

        foreach ($revisions as $revision) {
            $this->line(sprintf('Executing Revision [%s] (ID: %d, Effective: %s)...',
                $revision->revision_code,
                $revision->id,
                $revision->effective_at ? $revision->effective_at->toIso8601String() : 'immediate'
            ));

            try {
                $result = $this->pricingService->executeRevision($revision);
                if ($result) {
                    $successCount++;
                    $this->info(sprintf('✓ Revision [%s] activated successfully.', $revision->revision_code));
                } else {
                    $failedCount++;
                    $this->warn(sprintf('⚠ Revision [%s] was skipped or not in executable state.', $revision->revision_code));
                }
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error("Failed to execute scheduled price revision {$revision->id}: {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error(sprintf('✗ Revision [%s] failed: %s', $revision->revision_code, $e->getMessage()));
            }
        }

        $this->info(sprintf('Finished. Success: %d, Failed/Skipped: %d', $successCount, $failedCount));
        return 0;
    }
}
