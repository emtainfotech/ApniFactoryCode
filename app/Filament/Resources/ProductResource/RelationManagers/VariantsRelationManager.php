<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\BoxPacking;
use App\Models\ProductVariant;
use App\Models\ShadeCard;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Str;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';
    protected static ?string $recordTitleAttribute = 'variant_sku';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('variant_sku')
                    ->label('Variant SKU')
                    ->default(fn () => 'VAR-' . Str::upper(Str::random(8)))
                    ->required(),

                Forms\Components\TextInput::make('variant_name')
                    ->label('Variant Name')
                    ->maxLength(255),

                Forms\Components\TextInput::make('pack_litres')
                    ->label('Pack Size (e.g. 1L, 4L, 20L)'),

                Forms\Components\Select::make('shade_card_id')
                    ->label('Color / Shade')
                    ->options(fn () => ShadeCard::pluck('name', 'id'))
                    ->searchable(),

                Forms\Components\Select::make('box_packing_id')
                    ->label('Box Packing')
                    ->options(fn () => BoxPacking::pluck('name', 'id'))
                    ->searchable(),

                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('product/variants'),

                Forms\Components\Select::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                        'Archived' => 'Archived',
                    ])
                    ->default('Active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->square(),
                Tables\Columns\TextColumn::make('variant_sku')->label('Variant SKU')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('variant_name')->searchable(),
                Tables\Columns\TextColumn::make('pack_litres')->label('Pack Size'),
                Tables\Columns\TextColumn::make('listings_count')
                    ->counts('listings')
                    ->label('Seller Listings')
                    ->badge(),
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

