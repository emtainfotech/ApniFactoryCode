<?php

namespace App\Jobs;

use App\Models\SellerCatalogueBulkOperation;
use App\Services\SellerCatalogueBulkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSellerCatalogueBulkOperation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes max
    public int $tries = 1;

    public function __construct(public SellerCatalogueBulkOperation $operation)
    {
    }

    public function handle(SellerCatalogueBulkService $service): void
    {
        try {
            $service->executeOperation($this->operation);
        } catch (\Throwable $e) {
            Log::error("Failed to execute bulk operation #{$this->operation->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->operation->update([
                'status'        => 'failed',
                'error_summary' => ['fatal_error' => $e->getMessage()],
                'completed_at'  => now(),
            ]);
            throw $e;
        }
    }
}

