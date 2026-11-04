<?php

namespace App\Jobs;

use App\Models\SellerCatalogueExport;
use App\Services\SellerCatalogueExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSellerCatalogueExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes max for massive datasets
    public int $tries = 2;

    public function __construct(public SellerCatalogueExport $export)
    {
    }

    public function handle(SellerCatalogueExportService $service): void
    {
        try {
            $service->processExport($this->export);
        } catch (\Throwable $e) {
            Log::error("Failed to process catalogue export #{$this->export->id}: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            $this->export->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at'  => now(),
            ]);
            throw $e;
        }
    }
}

