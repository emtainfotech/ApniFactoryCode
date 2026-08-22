<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;

use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
 public static function getNavigationBadge(): ?string
    {
        return 'Total : '.static::getModel()::count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success'; // Options: danger, gray, info, primary, success, warning
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->required(),
                TextInput::make('mobile')->required()->numeric()
              // Use unique rule and exclude the current record's mobile number if editing
            ->rule('unique:customers,mobile,' . ($record->id ?? ''))
            ->reactive()
            ->afterStateUpdated(function (callable $set) {
                // Optionally set a custom error message if needed
                $set('mobile_error', 'This mobile number is already registered.');
            }),
                TextInput::make('password')->required()->password(),
                Select::make('type')
                    ->placeholder('Select a type')
                    ->options([
                        'user' => 'user',
                        'vendor' => 'vendor',
                    ]),
                Select::make('status')
                    ->placeholder('Select a status')
                    ->options([
                        'Active' => 'Active',
                        'Deactive' => 'Deactive',
                    ]),
                 TextInput::make('regby')->default('admin'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                 Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                 Tables\Columns\TextColumn::make('email')->searchable(),
                 Tables\Columns\TextColumn::make('mobile')->searchable(),
                 Tables\Columns\TextColumn::make('type')->searchable(),
                 Tables\Columns\TextColumn::make('created_at')
            ])
            ->filters([
                Filters\Filter::make('name')
                ->form([
                    TextInput::make('name')
                        ->label('Search name')
                        ->placeholder('Enter name...')
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['name'], 
                            fn ($query) => $query->where('name', 'like', '%' . $data['name'] . '%')
                        );
                }),
                Filters\Filter::make('location')
                ->form([
                    TextInput::make('location')
                        ->label('Search city')
                        ->placeholder('Enter city...')
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['location'], 
                            fn ($query) => $query->where('location', 'like', '%' . $data['location'] . '%')
                        );
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
      public static function canCreate(): bool
    {
        // Logic to determine if the create button should be shown
        return false; // Change this to your actual logic
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }   
       protected function getHeaderWidgets(): array
            {
                return [
                    MyCounterWidget::class, // Register your widget here
                ];
            }
}
