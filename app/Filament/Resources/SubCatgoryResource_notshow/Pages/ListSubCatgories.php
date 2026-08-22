<?php

namespace App\Filament\Resources\SubCatgoryResource\Pages;

use App\Filament\Resources\SubCatgoryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubCatgories extends ListRecords
{
    protected static string $resource = SubCatgoryResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
