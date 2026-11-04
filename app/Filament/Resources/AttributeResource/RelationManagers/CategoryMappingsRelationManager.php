<?php

namespace App\Filament\Resources\AttributeResource\RelationManagers;

use App\Models\Category;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class CategoryMappingsRelationManager extends RelationManager
{
    protected static string $relationship = 'categoryAttributes';
    protected static ?string $title = 'Category Mappings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(fn () => Category::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Toggle::make('is_required')
                    ->label('Mandatory for Products'),

                Forms\Components\Toggle::make('is_variant_generating')
                    ->label('Generates Product Variants (e.g. Size, Shade, Class)'),

                Forms\Components\Toggle::make('is_filterable')
                    ->label('Filterable in Storefront')
                    ->default(true),

                Forms\Components\Toggle::make('is_searchable')
                    ->label('Searchable in Search Bar'),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(1),

                Forms\Components\Select::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ])
                    ->default('Active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')->label('Category')->sortable()->searchable(),
                Tables\Columns\IconColumn::make('is_required')->boolean()->label('Required'),
                Tables\Columns\IconColumn::make('is_variant_generating')->boolean()->label('Variant-Gen'),
                Tables\Columns\IconColumn::make('is_filterable')->boolean()->label('Filterable'),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'Active',
                        'danger' => 'Inactive',
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
