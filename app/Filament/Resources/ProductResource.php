<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use App\Models\SubCatgory;
use App\Models\Category;
use App\Models\Company;
use App\Models\MainCategory;
use App\Models\BoxPacking;
use App\Models\Brand;
use App\Models\Size;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Str;
use Filament\Forms\Components\RichEditor;
use App\Models\ShadeCard;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters;
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
protected static ?string $navigationBadgeTooltip = 'Number of pending orders';

    public static function getNavigationBadge(): ?string
    {
        return 'Active : '.static::getModel()::where('status', '1')->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success'; // Options: danger, gray, info, primary, success, warning
    }
    public static function form(Form $form): Form
    {
        $pd = Product::orderby('id','desc')->first();
        $pid = $pd->id + 1;
        return $form
            ->schema([
                 TextInput::make('product_id')->default($pid),
                Select::make('user_id')
                    ->relationship("user","name")
                ->required()
                ->default(auth()->id()),  
                  Select::make('maincategory_id')
                    ->placeholder('Select a maincategory') 
                    ->relationship("maincategory","name")
                    //->options(MainCategory::all()->pluck('name', 'id'))
                    ->searchable()
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
              /*   Select::make('subcategory_id')
                    ->placeholder('Select a sub-category')
                    ->options(function (callable $get){
                                                    $subcityst = SubCatgory::where('category_id', $get('cid'))->pluck('name','id');
                                                    if(!$subcityst){
                                                        return SubCatgory::all()->pluck('name','id');
                                                    }
                                                     return $subcityst;
                                                })
                    ->searchable(),  */
                    Select::make('brand')
                    ->placeholder('Select a Brand')
                    ->relationship("brand","name")
                    ->searchable()
                    ->reactive(),
                TextInput::make('name')->required()->lazy()
                ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null),
                TextInput::make('title')->required(),
                Select::make('status')
                    ->placeholder('Select a status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                    ]),
                Select::make('tax')
                    ->placeholder('Select a Tax')
                    ->options([
                        '18' => '18%',
                        '20' => '20%',
                    ]),
                     TextInput::make('hsncode'),
                FileUpload::make('image')->label('Image')
                                                ->directory('product'),
                RichEditor::make('description'),
                TextInput::make('slug')->disabled()
                                    ->required()
                                    ->unique(Product::class, 'slug', ignoreRecord: true),
                 Forms\Components\Section::make('Product Attribute Information')
                            ->schema([
                                Forms\Components\Repeater::make('product_attributes')
                                        ->relationship()
                                        ->schema([
                                               Select::make('quantity')
                                                ->placeholder('Select a BoxPacking')
                                                ->options(BoxPacking::all()->pluck('name', 'id'))
                                                ->searchable()
                                                ->label('BoxPacking')
                                                ->reactive(),
                                            
                                               Select::make('color')
                                                ->options(function (callable $get){ 
                                                //                             $cityst1 = ShadeCard::where('subcategoryid', $get('cid'))->pluck('name','id');
                                                //                             if(!$cityst1){
                                                                                return ShadeCard::all()->pluck('name','id');
                                                                            // }
                                                                            //  return $cityst1;
                                                                        })
                                                ->searchable()
                                                ->label('Shade'),
                                            Forms\Components\TextInput::make('price')->required()->numeric()
                                                                ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/']),
                                            Forms\Components\TextInput::make('oldprice')->required()->numeric()
                                                        ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/']),
                                        ])
                                        ->columns(4)
                                        ->collapsible()
                                        ->createItemButtonLabel('Add Product Attribute'),
                            ])
                            ->collapsible()
                            ->columns(1),
               //   Forms\Components\Section::make('Product Shade Cards')
                            //             ->schema([
                            //                   Select::make('shadecard')
                            //                 ->placeholder('Select a Shades')
                            //                 ->options(function (callable $get){
                            //                                                 $cityst1 = ShadeCard::where('subcategoryid', $get('cid'))->pluck('name','id');
                            //                                                 if(!$cityst1){
                            //                                                     return ShadeCard::all()->pluck('name','id');
                            //                                                 }
                            //                                                  return $cityst1;
                            //                                             })
                            //                 ->searchable()
                            //                 ->multiple()
                                            
                            //             ])
                                       
                            // ->collapsible()
                            // ->columns(1),
                           
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->square(),
                Tables\Columns\TextColumn::make('brand.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('category.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('user.company.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->date(),
                ToggleColumn::make('status'),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Filters\SelectFilter::make('Company Name')
                ->options(
                    // Fetch all company names for the select filter options
                    // Ensure you are using the correct relationships
                    Company::all()->pluck('name', 'id')
                )
                ->query(function (Builder $query, array $data): Builder {
                    if (empty($data['value'])) { // Check if a value is selected
                        return $query;
                    }
                    return $query->whereHas('user.company', function (Builder $companyQuery) use ($data) {
                        $companyQuery->where('id', $data['value']); // Filter based on company id
                    });
                }),
                 Filters\SelectFilter::make('brand_id')
                ->label('Search By Brand')
                    ->relationship("brand","name")
                    ->searchable(),
                 Filters\SelectFilter::make('category_id')
                ->label('Search By Category')
                ->relationship("category","name")
                ->searchable(),
                 Filters\SelectFilter::make('status')
                ->label('Status')
                ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                ])
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
   
    public static function canCreate(): bool
    {
        // Logic to determine if the create button should be shown
        return false; // Change this to your actual logic
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }    
}
