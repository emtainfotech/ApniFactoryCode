<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Str;

class SellerListingsRelationManager extends RelationManager
{
    protected static string $relationship = 'listings';
    protected static ?string $title = 'Seller Offer Listings';
    protected static ?string $recordTitleAttribute = 'seller_sku';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('seller_user_id')
                    ->label('Seller')
                    ->options(fn () => User::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('product_variant_id')
                    ->label('Variant')
                    ->options(function (RelationManager $livewire) {
                        return ProductVariant::where('product_id', $livewire->ownerRecord->id)->pluck('variant_sku', 'id');
                    })
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('seller_sku')
                    ->label('Seller SKU')
                    ->default(fn () => 'SKU-' . Str::upper(Str::random(6)))
                    ->required(),

                Forms\Components\TextInput::make('seller_price')
                    ->label('Seller Price (₹)')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('selling_price')
                    ->label('Selling Price (₹)')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('mrp')
                    ->label('MRP (₹)')
                    ->numeric(),

                Forms\Components\TextInput::make('stock_quantity')
                    ->label('Stock Quantity')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('min_order_quantity')
                    ->label('MOQ')
                    ->numeric()
                    ->default(1),

                Forms\Components\Select::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                        'Archived' => 'Archived',
                    ])
                    ->default('Active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('seller_sku')->label('SKU')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('seller.name')->label('Seller'),
                Tables\Columns\TextColumn::make('variant.variant_sku')->label('Variant SKU'),
                Tables\Columns\TextColumn::make('selling_price')->label('Selling Price')->money('inr', true),
                Tables\Columns\TextColumn::make('mrp')->label('MRP')->money('inr', true),
                Tables\Columns\TextColumn::make('stock_quantity')->label('Total Stock'),
                Tables\Columns\BadgeColumn::make('available_stock')
                    ->label('Available')
                    ->colors([
                        'success' => fn ($state): bool => $state > 10,
                        'warning' => fn ($state): bool => $state > 0 && $state <= 10,
                        'danger' => fn ($state): bool => $state <= 0,
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'Active',
                        'danger' => 'Inactive',
                        'secondary' => 'Archived',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
