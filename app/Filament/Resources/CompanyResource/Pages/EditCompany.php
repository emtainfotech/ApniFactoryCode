<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $company = $this->record;
        if ($company && $company->user_id) {
            app(\App\Services\PaintPricingService::class)->syncDisplayPricesForCommissionChange('company', $company->id, (float)($company->comission ?? 0));
        }
    }
}
