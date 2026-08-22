<?php

namespace App\Filament\Resources\BoxPackingResource\Pages;

use App\Filament\Resources\BoxPackingResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBoxPacking extends EditRecord
{
    protected static string $resource = BoxPackingResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
