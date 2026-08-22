<?php

namespace App\Filament\Resources\ShadeCardResource\Pages;

use App\Filament\Resources\ShadeCardResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShadeCards extends ListRecords
{
    protected static string $resource = ShadeCardResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
