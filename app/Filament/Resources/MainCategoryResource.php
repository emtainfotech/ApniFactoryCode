<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MainCategoryResource\Pages;
use App\Filament\Resources\MainCategoryResource\RelationManagers;
use App\Models\MainCategory;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;

class MainCategoryResource extends Resource
{
    protected static ?string $model = MainCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('title')->required(),
                FileUpload::make('image')
                                                ->label('Image')
                                                ->image()
                                                ->directory('category'),
                Select::make('status')
                    ->placeholder('Select a status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                    ]),
                    

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                Tables\Columns\ImageColumn::make('image')->square(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                ToggleColumn::make('status')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->date(),
                TextInputColumn::make('sequence')
                ->type('number')
                ->rules(['numeric', 'min:0'])  // validation rules
                ->extraAttributes([
                    'style' => 'max-width: 70px; white-space: normal;',
                ])
                ->updateStateUsing(function (MainCategory $record, $state) {
                    $record->sequence = $state;
                    $record->save();
                    return $state;
                }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMainCategories::route('/'),
            'create' => Pages\CreateMainCategory::route('/create'),
            'edit' => Pages\EditMainCategory::route('/{record}/edit'),
        ];
    }    
}
