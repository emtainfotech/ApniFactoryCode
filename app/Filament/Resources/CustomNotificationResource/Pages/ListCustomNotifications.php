<?php

namespace App\Filament\Resources\CustomNotificationResource\Pages;

use App\Filament\Resources\CustomNotificationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomNotifications extends ListRecords
{
    protected static string $resource = CustomNotificationResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Send New Push Notification'),
        ];
    }

    protected function getTableRecordsPerPage(): int
    {
        return 10;
    }
}

