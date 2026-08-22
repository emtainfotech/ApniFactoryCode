<?php

namespace App\Filament\Resources\WalletResource\Pages;

use App\Filament\Resources\WalletResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Models\Wallet;
class CreateWallet extends CreateRecord
{
    protected static string $resource = WalletResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $latestBalance = Wallet::where('user_id', $data['user_id'])
            ->latest()
            ->value('balance') ?? 0;
        if ($data['action'] === 'debit' && $latestBalance < $data['amount']) {
            throw ValidationException::withMessages([
                'amount' => "Insufficient balance. Current balance: $" . number_format($latestBalance, 2),
            ]);
        }
        return $data;
    }
    protected function onValidationError(ValidationException $exception): void
    {
        // Notification::make()
        //     ->title($exception->getMessage())
        //     ->danger()
        //     ->send();
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Wallet transaction successfully recorded';

        }

}
