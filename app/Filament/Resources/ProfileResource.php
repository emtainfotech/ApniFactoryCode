<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages;
use App\Filament\Resources\ProfileResource\RelationManagers;
use App\Models\Profile;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    
       public static function getNavigationLabel(): string
   {
       return 'Apnifactory Profile'; // Change this to your new navigation label
   }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('attribute')->required(), TextInput::make('value')->required(),
                Select::make('viewon')
                    ->placeholder('Select a viewon')
                    ->options([
                        'Sellerpanel' => 'Sellerpanel',
                        'Invoice' => 'Invoice',
                        'App' => 'App'
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                 Tables\Columns\TextColumn::make('attribute')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('value')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('viewon'),
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
            'index' => Pages\ListProfiles::route('/'),
            'create' => Pages\CreateProfile::route('/create'),
            'edit' => Pages\EditProfile::route('/{record}/edit'),
        ];
    }    
}
