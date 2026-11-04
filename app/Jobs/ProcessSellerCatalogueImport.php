<?php

namespace App\Jobs;

use App\Models\SellerCatalogueImport;
use App\Services\SellerCatalogueImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSellerCatalogueImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes max
    public int $tries = 1;

    public function __construct(public SellerCatalogueImport $import)
    {
    }

    public function handle(SellerCatalogueImportService $service): void
    {
        try {
            $service->executeImport($this->import);
        } catch (\Throwable $e) {
            Log::error("Failed to execute catalogue import #{$this->import->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->import->update([
                'status'        => 'failed',
                'error_summary' => ['fatal_error' => $e->getMessage()],
                'completed_at'  => now(),
            ]);
            throw $e;
        }
    }
}
