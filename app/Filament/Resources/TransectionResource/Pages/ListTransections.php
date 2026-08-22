<?php

namespace App\Filament\Resources\TransectionResource\Pages;

use App\Filament\Resources\TransectionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransections extends ListRecords
{
    protected static string $resource = TransectionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
