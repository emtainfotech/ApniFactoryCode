<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Resources\CouponResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\WhatsappTraits;

class CreateCoupon extends CreateRecord
{
    use WhatsappTraits;
    protected static string $resource = CouponResource::class;
      protected function afterCreate(): void
    {
        // Check if the saved record's type is 'Registered'
        if ($this->record->status == 1) {
           $rt = $this->sendnotification_onmultipledevice('newcoupon',$this->record->id);
           
        }
    }
}
