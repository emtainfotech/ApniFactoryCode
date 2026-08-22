<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
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
use App\Helper\Helper;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters;
class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationBadgeTooltip = 'Number of pending orders';

    public static function getNavigationBadge(): ?string
    {
        return 'Pending : '.static::getModel()::where('adminstatus', 'pending')->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning'; // Options: danger, gray, info, primary, success, warning
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('title')->required(),
                Select::make('maincategory_id')
                    ->placeholder('Select a maincategory')
                    // ->options(MainCategory::all()->pluck('name', 'id'))
                    ->relationship("maincategory","name")->searchable(),
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
                Select::make('adminstatus')
                    ->options([
                        'approved' => 'approved',
                        'reject' => 'reject',
                        'pending' => 'pending'
                    ]),
                TextInput::make('adminmsg'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->square(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('maincategory.name'),
                ToggleColumn::make('status'),
                TextInputColumn::make('sequence')
                ->type('number')
                ->extraAttributes([
                    'style' => 'max-width: 70px; white-space: normal;',
                ])
                ->rules(['numeric', 'min:0'])  // validation rules
                ->updateStateUsing(function (Category $record, $state) {
                    $record->sequence = $state;
                    $record->save();
                    return $state;
                }),
                Tables\Columns\TextColumn::make('adminstatus')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->date(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                 Filters\SelectFilter::make('maincategory_id')   
                ->label('Main Category')
                 ->relationship("maincategory","name")
                 ->searchable(),
                Filters\SelectFilter::make('status')
                ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                    ]),
                Filters\SelectFilter::make('adminstatus')
                    ->placeholder('Select a Admin Status')
                    ->options([
                        'approved' => 'approved',
                        'reject' => 'reject',
                        'pending' => 'pending'
                    ]),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }    
}
