<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
// use Filament\Tables\Enums\ActionsPosition;
//   use Filament\Tables\Actions\Position;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use App\Models\Company;
use DB;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters;
class ResourceBrand extends Resource  ///////////////////class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';      
    protected static ?string $navigationBadgeTooltip = 'Number of pending orders';

    public static function getNavigationBadge(): ?string
    {
        return 'Processing : '.static::getModel()::where('type', 'Processing')->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning'; // Options: danger, gray, info, primary, success, warning
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([ 
                Select::make('company_id')
                    ->placeholder('Select a Company')
                    // ->options(Company::all()->pluck('name', 'id'))
                    ->relationship("company","name")->required(),
                Select::make('cid')
                    ->placeholder('Select a Category')
                    // ->options(Company::all()->pluck('name', 'id'))
                    ->relationship("category","name")
                    ->searchable()
                    ->required(),
                TextInput::make('name')->required(),
                TextInput::make('trademarkno')->required(),
                FileUpload::make('image')->label('Brand Logo')->directory('brand'),
                Select::make('type')
                    ->placeholder('Select a type')
                    ->options([
                        'Registered' => 'Registered',
                        'Unregistered' => 'Unregistered',
                        'Processing' => 'Processing'
                    ]),
                Select::make('status')
                    ->placeholder('Select a status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                    ]),
                    TextInput::make('adminresponse')->label('Admin Response Shows To Seller'),
                Select::make('user_id')
                    ->relationship("user","name")
                ->required()
                ->default(auth()->id()),  
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->square()->disk('public'),
                Tables\Columns\TextColumn::make('company.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('category.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('type')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->date(),
                ToggleColumn::make('status')//->getStateUsing(fn($record) => Notification::make()->success()->title('Status Updated')->send())   ,
            ])
            ->defaultSort('id', 'desc')
            ->filters([
            Filters\SelectFilter::make('company_id')   
                ->label('Company')
                 ->relationship("company","name")
                 ->searchable(),
            Filters\SelectFilter::make('categoryr_id')
                ->label('Category')
                 ->relationship("category","name")
                 ->searchable(),  
                 Filters\SelectFilter::make('type')
                    ->placeholder('Select a type')
                    ->options([
                        'Registered' => 'Registered',
                        'Unregistered' => 'Unregistered',
                        'Processing' => 'Processing'
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }    
public function getCachedTabs(): array
{
    return $this->getTabs();
}
}
