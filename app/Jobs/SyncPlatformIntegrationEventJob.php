<?php

namespace App\Jobs;

use App\Models\IntegrationSyncEvent;
use App\Services\CrossPlatformSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPlatformIntegrationEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Exponential retry backoff intervals in seconds.
     */
    public array $backoff = [15, 60, 180];

    public function __construct(
        public int $syncEventId
    ) {
    }

    public function handle(CrossPlatformSyncService $syncService): void
    {
        $event = IntegrationSyncEvent::find($this->syncEventId);
        if (!$event) {
            return;
        }

        if (in_array($event->status, [
            IntegrationSyncEvent::STATUS_PROCESSED,
            IntegrationSyncEvent::STATUS_SKIPPED_DISABLED,
            IntegrationSyncEvent::STATUS_REJECTED_CONFLICT,
        ], true)) {
            return;
        }

        $syncService->processSyncEvent($event);
    }

    public function failed(\Throwable $exception): void
    {
        $event = IntegrationSyncEvent::find($this->syncEventId);
        if (!$event) {
            return;
        }

        $sanitized = app(CrossPlatformSyncService::class)->sanitizeErrorMessage($exception->getMessage());
        $event->update([
            'status'     => IntegrationSyncEvent::STATUS_DEAD_LETTER,
            'last_error' => $sanitized,
        ]);
    }
}

