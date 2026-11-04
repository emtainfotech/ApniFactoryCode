<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductVariantResource\Pages;
use App\Models\BoxPacking;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShadeCard;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Str;

class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;

    protected static ?string $navigationIcon = 'heroicon-o-color-swatch';
    protected static ?string $navigationGroup = 'Catalogue Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Parent Product')
                            ->options(fn () => Product::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('variant_sku')
                            ->label('Variant SKU')
                            ->default(fn () => 'VAR-' . Str::upper(Str::random(8)))
                            ->unique(ProductVariant::class, 'variant_sku', ignoreRecord: true)
                            ->required(),

                        Forms\Components\TextInput::make('variant_name')
                            ->label('Variant Name / Title')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('pack_litres')
                            ->label('Pack Size / Volume (e.g. 1L, 4L, 20L, 5Kg)'),

                        Forms\Components\Select::make('shade_card_id')
                            ->label('Color / Shade')
                            ->options(fn () => ShadeCard::pluck('name', 'id'))
                            ->searchable(),

                        Forms\Components\Select::make('box_packing_id')
                            ->label('Box Packing Container')
                            ->options(fn () => BoxPacking::pluck('name', 'id'))
                            ->searchable(),

                        Forms\Components\FileUpload::make('image')
                            ->label('Variant Image')
                            ->image()
                            ->directory('product/variants'),

                        Forms\Components\TextInput::make('barcode')
                            ->label('Barcode / EAN / GTIN'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                                'Archived' => 'Archived',
                            ])
                            ->default('Active')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->square()->label('Image'),
                Tables\Columns\TextColumn::make('variant_sku')->sortable()->searchable()->label('Variant SKU')->weight('bold'),
                Tables\Columns\TextColumn::make('product.name')->sortable()->searchable()->label('Parent Product'),
                Tables\Columns\TextColumn::make('variant_name')->searchable(),
                Tables\Columns\TextColumn::make('pack_litres')->label('Pack Size'),
                Tables\Columns\BadgeColumn::make('listings_count')
                    ->counts('listings')
                    ->label('Seller Listings'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'Active',
                        'danger' => 'Inactive',
                        'secondary' => 'Archived',
                    ]),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                        'Archived' => 'Archived',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariant::route('/create'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }
}
