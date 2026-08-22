<?php

namespace App\Filament\Resources\AdvertisementResource\Pages;

use App\Filament\Resources\AdvertisementResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
class ListAdvertisements extends ListRecords
{
    protected static string $resource = AdvertisementResource::class;

    protected static ?string $navigationLabel = 'Custom Ad Name';
    public static function getNavigationLabel(): string
    {
        return 'Custom Ad Name';
    }
    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
  
    
}
