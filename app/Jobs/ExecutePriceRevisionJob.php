<?php

namespace App\Jobs;

use App\Models\PriceRevision;
use App\Services\DynamicPriceRevisionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExecutePriceRevisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $revisionId;
    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(int $revisionId)
    {
        $this->revisionId = $revisionId;
    }

    /**
     * Execute the job.
     */
    public function handle(DynamicPriceRevisionService $pricingService): void
    {
        $revision = PriceRevision::find($this->revisionId);

        if (!$revision) {
            Log::warning("ExecutePriceRevisionJob: Revision {$this->revisionId} not found. Skipping.");
            return;
        }

        // Idempotency check: if already active, do nothing
        if ($revision->status === 'active') {
            Log::info("ExecutePriceRevisionJob: Revision {$revision->revision_code} is already active. No action taken.");
            return;
        }

        $pricingService->executeRevision($revision);
    }
}
