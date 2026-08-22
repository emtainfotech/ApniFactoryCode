<?php

namespace App\Filament\Resources\BoxPackingResource\Pages;

use App\Filament\Resources\BoxPackingResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBoxPackings extends ListRecords
{
    protected static string $resource = BoxPackingResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
