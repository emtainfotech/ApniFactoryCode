<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShadeCardResource\Pages;
use App\Filament\Resources\ShadeCardResource\RelationManagers;
use App\Models\ShadeCard;
use App\Models\MainCategory;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use App\Models\Category;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\ColorPicker;
use Filament\Tables\Filters\SelectFilter;
class ShadeCardResource extends Resource
{
    protected static ?string $model = ShadeCard::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                ColorPicker::make('hexcode'),
                                                
                                                 Select::make('maincategory_id')
                    ->placeholder('Select a maincategory') 
                    ->relationship("maincategory","name")
                    //->options(MainCategory::all()->pluck('name', 'id'))
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('category_id', null)),
                 Select::make('category_id')
                    ->placeholder('Select a category')
                    ->options(function (callable $get){     
                                                    $cityst = Category::where('maincategory_id', $get('maincategory_id'))->pluck('name','id');
                                                    if(!$cityst){
                                                        return Category::all()->pluck('name','id');
                                                    }
                                                     return $cityst;
                                                })
                    ->searchable(),
                
                 FileUpload::make('image')->label('Image')
                                                ->directory('shadecard'),    
                    
                Select::make('status')
                    ->placeholder('Select a status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                    ]),
                Select::make('user_id')
                    ->relationship("user","name")
                ->required()
                ->default(auth()->id()),  
                TextInput::make('adminmsg'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->square(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('hexcode')->sortable()->searchable()
                 ->extraAttributes(function (ShadeCard $record) { 
                    $bgColor = $record->hexcode; 
                    return ['style' => "background-color: {$bgColor};border-radius: 10px;"];
                }),
                Tables\Columns\TextColumn::make('category.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->date(),
                ToggleColumn::make('status'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
               SelectFilter::make('status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                    ])
                    ->attribute('status'),
                SelectFilter::make('category_id')
                ->relationship('category', 'name')
                ->searchable()    
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListShadeCards::route('/'),
            'create' => Pages\CreateShadeCard::route('/create'),
            'edit' => Pages\EditShadeCard::route('/{record}/edit'),
        ];
    }    
}
