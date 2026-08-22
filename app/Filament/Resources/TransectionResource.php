<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransectionResource\Pages;
use App\Filament\Resources\TransectionResource\RelationManagers;
use App\Models\Transection;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters;
use Filament\Forms\Components;
class TransectionResource extends Resource
{
    protected static ?string $model = Transection::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationBadgeTooltip = 'Number of pending orders';
    // 1. Fixes the Sidebar / Menu Label
    protected static ?string $navigationLabel = 'Transaction';
    // 2. Fixes the Page Headings (Create, Edit, View, List titles)
    protected static ?string $modelLabel = 'Transaction';
    protected static ?string $pluralModelLabel = 'Transaction';

    public static function getNavigationBadge(): ?string
    {
        return 'Success : '.static::getModel()::where('status', 'success')->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success'; // Options: danger, gray, info, primary, success, warning
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_no')->searchable(),
                Tables\Columns\TextColumn::make('txnid')->searchable(),
                Tables\Columns\TextColumn::make('txndetail')->searchable(),
                Tables\Columns\TextColumn::make('txnmethod')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->date(),
                ToggleColumn::make('status')
            ])
            ->filters([
            Filters\Filter::make('orderno_search')
                ->form([
                    Components\TextInput::make('orderno')
                        ->label('Search Order No')
                        ->placeholder('Enter order number...')
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['orderno'], 
                            fn ($query) => $query->where('order_no', 'like', '%' . $data['orderno'] . '%')
                        );
                }),
             Filters\Filter::make('txnid')
                ->form([
                    Components\TextInput::make('txnid')
                        ->label('Search Txn Id')
                        ->placeholder('Enter  Txn Id...')
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['txnid'], 
                            fn ($query) => $query->where('txnid', 'like', '%' . $data['txnid'] . '%')
                        );
                }),
            Filters\Filter::make('date_range')
                ->form([
                    Components\DatePicker::make('created_at'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['created_at'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date));
                })
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
        return false; // Change this to your actual logic
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransections::route('/'),
            // 'create' => Pages\CreateTransection::route('/create'),
            // 'edit' => Pages\EditTransection::route('/{record}/edit'),
        ];
    }    
}
