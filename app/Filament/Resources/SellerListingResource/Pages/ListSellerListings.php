<?php

namespace App\Filament\Resources\SellerListingResource\Pages;

use App\Filament\Resources\SellerListingResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSellerListings extends ListRecords
{
    protected static string $resource = SellerListingResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

