<?php

namespace App\Filament\Resources\AttributeResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Str;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'options';
    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255)
                    ->lazy()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('value', Str::slug($state, '_'))),

                Forms\Components\TextInput::make('value')
                    ->required()
                    ->maxLength(255),

                Forms\Components\ColorPicker::make('color_hex')
                    ->label('Swatch Color (Optional)'),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(1),

                Forms\Components\Toggle::make('is_default')
                    ->label('Default Selected'),

                Forms\Components\Select::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ])
                    ->default('Active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('value')->searchable(),
                Tables\Columns\ColorColumn::make('color_hex')->label('Color'),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\IconColumn::make('is_default')->boolean(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'Active',
                        'danger' => 'Inactive',
                    ]),
            ])
            ->defaultSort('sort_order', 'asc')
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
