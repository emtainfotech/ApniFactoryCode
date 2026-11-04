<?php

namespace App\Filament\Resources\SellerListingResource\Pages;

use App\Filament\Resources\SellerListingResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSellerListing extends EditRecord
{
    protected static string $resource = SellerListingResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

