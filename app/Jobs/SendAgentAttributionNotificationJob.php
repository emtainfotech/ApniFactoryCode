<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAgentAttributionNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 60, 180];

    public function __construct(
        public int $recipientUserId,
        public string $recipientType, // 'agent' | 'admin'
        public string $title,
        public string $message,
        public array $details = [],
        public ?string $actionUrl = null,
        public ?string $dedupKey = null
    ) {
    }

    public function handle(NotificationService $notificationService): void
    {
        $notificationService->sendAgentAttributionInAppAndEmail(
            $this->recipientUserId,
            $this->recipientType,
            $this->title,
            $this->message,
            $this->details,
            $this->actionUrl,
            $this->dedupKey
        );
    }
}

