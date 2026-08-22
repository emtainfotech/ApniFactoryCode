<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubCatgoryResource\Pages;
use App\Filament\Resources\SubCatgoryResource\RelationManagers;
use App\Models\SubCatgory;
use App\Models\Category;
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

class SubCatgoryResource extends Resource
{
    protected static ?string $model = SubCatgory::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

     public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('title')->required(),
                Select::make('mid')
                    ->placeholder('Select a maincategory')
                    ->options(MainCategory::all()->pluck('name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('cid', null)),
                 Select::make('cid')
                    ->placeholder('Select a category')
                    ->options(function (callable $get){
                                                    $cityst = Category::where('mid', $get('mid'))->pluck('name','id');
                                                    if(!$cityst){
                                                        return Category::all()->pluck('name','id');
                                                    }
                                                     return $cityst;
                                                })
                    ->searchable(),
                       
                FileUpload::make('image')
                                                ->label('Image')
                                                ->image()
                                                ->directory('category'),
                Select::make('status')
                    ->placeholder('Select a status')
                    ->options([
                        'Active' => 'Active',
                        'Deactive' => 'Deactive',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                Tables\Columns\ImageColumn::make('image')->square(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('sid'),
                Tables\Columns\TextColumn::make('status')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->date(),
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
            'index' => Pages\ListSubCatgories::route('/'),
            'create' => Pages\CreateSubCatgory::route('/create'),
            'edit' => Pages\EditSubCatgory::route('/{record}/edit'),
        ];
    }    
}
