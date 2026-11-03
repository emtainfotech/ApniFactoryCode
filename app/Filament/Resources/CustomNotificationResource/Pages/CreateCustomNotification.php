<?php

namespace App\Filament\Resources\CustomNotificationResource\Pages;

use App\Filament\Resources\CustomNotificationResource;
use App\Services\NotificationService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification as FilamentNotify;

class CreateCustomNotification extends CreateRecord
{
    protected static string $resource = CustomNotificationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        try {
            $notificationService = app()->make(NotificationService::class);
            $targetAudience = ($record->type === 'promo') ? 'specific_customer' : 'all_customers';

            $result = $notificationService->broadcastCustomPush(
                $record->title ?? 'New Notification',
                $record->msg ?? '',
                $record->image,
                $record->screen,
                $record->screenid,
                $targetAudience,
                $record->customer_id
            );

            $deviceCount = $result['sent_count'] ?? ($result['tokens_found'] ?? 0);

            FilamentNotify::make()
                ->title('Notification Dispatched Successfully!')
                ->body("Targeted to {$deviceCount} registered devices. Saved to user notification feed.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            FilamentNotify::make()
                ->title('Warning: FCM Dispatch Notice')
                ->body('Saved to database, but push delivery encountered: ' . $e->getMessage())
                ->warning()
                ->send();
        }
    }
}
