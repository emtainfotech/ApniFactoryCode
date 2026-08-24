<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class PriceAdjustmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'priceAdjustments';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Pricing History & Adjustments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product / Target')
                    ->default(fn ($record) => $record->scope_type === 'category' ? ('Category #' . ($record->scope_json['category_id'] ?? '')) : 'All Products')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('scope_type')
                    ->label('Scope')
                    ->colors([
                        'primary' => 'family',
                        'warning' => 'category',
                        'success' => 'shades',
                        'info'    => 'packings',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\BadgeColumn::make('adjustment_type')
                    ->label('Type')
                    ->colors([
                        'success' => 'percentage',
                        'primary' => 'per_litre',
                        'secondary' => 'fixed',
                    ])
                    ->formatStateUsing(fn ($state) => str_replace('_', ' ', ucfirst($state))),
                Tables\Columns\TextColumn::make('adjustment_value')
                    ->label('Value Applied')
                    ->formatStateUsing(fn ($record) => 
                        $record->adjustment_type === 'percentage' 
                            ? (($record->adjustment_value > 0 ? '+' : '') . $record->adjustment_value . '%')
                            : ('₹' . number_format($record->adjustment_value, 2))
                    )
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('affected_count')
                    ->label('Affected SKUs')
                    ->color('success'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('SKU Breakdown')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading('SKU Price Change Details')
                    ->modalContent(function ($record) {
                        $items = is_array($record->preview_data) 
                            ? $record->preview_data 
                            : (json_decode($record->preview_data, true) ?? []);
                        return view('filament.components.price-adjustment-modal', ['items' => $items, 'record' => $record]);
                    }),
            ])
            ->bulkActions([
                //
            ]);
    }
}
