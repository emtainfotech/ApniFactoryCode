<?php

namespace App\Filament\Resources\CustomerAddressResource\Pages;

use App\Filament\Resources\CustomerAddressResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerAddress extends EditRecord
{
    protected static string $resource = CustomerAddressResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
