<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerAddressResource\Pages;
use App\Filament\Resources\CustomerAddressResource\RelationManagers;
use App\Models\CustomerAddress;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters;

class CustomerAddressResource extends Resource
{
    protected static ?string $model = CustomerAddress::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form 
            ->schema([
                  TextInput::make('name')->required(),
                  TextInput::make('customer_id')->required(),
                  TextInput::make('identityname')->required(),
                  TextInput::make('landmark1')->required(),
                  TextInput::make('landmark2')->required(),
                  TextInput::make('city')->required(),
                  TextInput::make('state')->required(),
                  TextInput::make('country')->required(),
                  TextInput::make('pincode')->required(),
                  TextInput::make('phoneno')->required(),
                  TextInput::make('location')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('customer.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('city')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('pincode')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('phoneno')->sortable()->searchable(),
            ])
            ->filters([
                 Filters\SelectFilter::make('customer_id')
                ->label('Customer')
                 ->relationship("customer","name")
                 ->searchable(),
                Filters\Filter::make('city')
                ->form([
                    TextInput::make('city')
                        ->label('Search city')
                        ->placeholder('Enter city...')
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['city'], 
                            fn ($query) => $query->where('city', 'like', '%' . $data['city'] . '%')
                        );
                }),
                Filters\Filter::make('pincode')
                ->form([
                    TextInput::make('pincode')
                        ->label('Search pincode')
                        ->placeholder('Enter pincode...')
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['pincode'], 
                            fn ($query) => $query->where('pincode', 'like', '%' . $data['pincode'] . '%')
                        );
                }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
      public static function canCreate(): bool
    {
        // Logic to determine if the create button should be shown
        return false; // Change this to your actual logic
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerAddresses::route('/'),
            'create' => Pages\CreateCustomerAddress::route('/create'),
            'edit' => Pages\EditCustomerAddress::route('/{record}/edit'),
        ];
    }    
}
