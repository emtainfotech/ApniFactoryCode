<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class SubcategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'subcategories';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('title')
                    ->maxLength(255),

                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('subcategory'),

                Forms\Components\Select::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Deactive' => 'Deactive',
                    ])
                    ->default('Active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->square(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => fn ($state): bool => in_array(strtolower((string)$state), ['active', '1']),
                        'danger'  => fn ($state): bool => !in_array(strtolower((string)$state), ['active', '1']),
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

