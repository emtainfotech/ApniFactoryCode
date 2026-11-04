<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SellerListingResource\Pages;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerListing;
use App\Models\User;
use App\Services\InventoryService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SellerListingResource extends Resource
{
    protected static ?string $model = SellerListing::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Catalogue Management';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('seller_user_id')
                            ->label('Seller User')
                            ->options(fn () => User::whereNotNull('email')->pluck('email', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('company_id')
                            ->label('Seller Company')
                            ->options(fn () => Company::pluck('name', 'id'))
                            ->searchable(),

                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->options(fn () => Product::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->required(),

                        Forms\Components\Select::make('product_variant_id')
                            ->label('Product Variant')
                            ->options(function (callable $get) {
                                $prodId = $get('product_id');
                                if ($prodId) {
                                    return ProductVariant::where('product_id', $prodId)->pluck('variant_sku', 'id');
                                }
                                return ProductVariant::limit(50)->pluck('variant_sku', 'id');
                            })
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('seller_sku')
                            ->label('Seller SKU Code')
                            ->default(fn () => 'SKU-' . Str::upper(Str::random(8)))
                            ->required(),

                        Forms\Components\TextInput::make('barcode')
                            ->label('Barcode / GTIN'),

                        Forms\Components\TextInput::make('seller_price')
                            ->label('Seller Cost Price (₹)')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $rate = (float) $get('commission_rate');
                                $set('selling_price', round((float)$state * (1 + ($rate / 100)), 2));
                            }),

                        Forms\Components\TextInput::make('commission_rate')
                            ->label('Commission Rate (%)')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $cost = (float) $get('seller_price');
                                $set('selling_price', round($cost * (1 + ((float)$state / 100)), 2));
                            }),

                        Forms\Components\TextInput::make('selling_price')
                            ->label('Calculated Selling Price (₹)')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('mrp')
                            ->label('MRP (₹)')
                            ->numeric(),

                        Forms\Components\Section::make('Packaging and Quantity Rules')
                            ->schema([
                                Forms\Components\TextInput::make('unit_of_measurement')
                                    ->label('Unit of Measurement')
                                    ->default('Unit'),

                                Forms\Components\TextInput::make('min_order_quantity')
                                    ->label('MOQ (Min Order Qty)')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),

                                Forms\Components\TextInput::make('order_multiple')
                                    ->label('Order Multiple Increment')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),

                                Forms\Components\TextInput::make('case_pack_quantity')
                                    ->label('Units per Case/Carton')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),

                                Forms\Components\Toggle::make('allow_unit_orders')
                                    ->label('Allow Single Unit Orders')
                                    ->default(true),

                                Forms\Components\Toggle::make('allow_carton_orders')
                                    ->label('Allow Carton Orders Only'),
                            ])->columns(3),

                        Forms\Components\Section::make('Stock and Inventory')
                            ->schema([
                                Forms\Components\TextInput::make('stock_quantity')
                                    ->label('Total Physical Stock')
                                    ->numeric()
                                    ->default(100)
                                    ->required(),

                                Forms\Components\TextInput::make('reserved_stock')
                                    ->label('Reserved in Active Checkouts')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled(),

                                Forms\Components\TextInput::make('low_stock_threshold')
                                    ->label('Low Stock Warning Level')
                                    ->numeric()
                                    ->default(10),

                                Forms\Components\TextInput::make('dispatch_lead_days')
                                    ->label('Dispatch Lead Time (Days)')
                                    ->numeric()
                                    ->default(2),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'Active' => 'Active',
                                        'Inactive' => 'Inactive',
                                        'Archived' => 'Archived',
                                    ])
                                    ->default('Active')
                                    ->required(),
                            ])->columns(3),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('seller_sku')->label('Seller SKU')->weight('bold')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('seller.name')->label('Seller / Company')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('product.name')->label('Product')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('variant.variant_sku')->label('Variant')->searchable(),
                Tables\Columns\TextColumn::make('stock_quantity')->label('Total Stock')->sortable(),
                Tables\Columns\BadgeColumn::make('available_stock')
                    ->label('Available')
                    ->colors([
                        'success' => fn ($state): bool => $state > 10,
                        'warning' => fn ($state): bool => $state > 0 && $state <= 10,
                        'danger' => fn ($state): bool => $state <= 0,
                    ]),
                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Price (₹)')
                    ->money('inr', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_order_quantity')->label('MOQ'),
                Tables\Columns\TextColumn::make('order_multiple')->label('Multiple'),
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
                Tables\Filters\Filter::make('in_stock')
                    ->label('In Stock Only')
                    ->query(fn (Builder $q) => $q->where('stock_quantity', '>', 0)),
            ])
            ->actions([
                // Real-time stock adjustment modal with audit logging
                Tables\Actions\Action::make('adjustStock')
                    ->label('Adjust Stock')
                    ->icon('heroicon-o-refresh')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('current_stock')
                            ->label('Current Physical Stock')
                            ->default(fn (SellerListing $record) => $record->stock_quantity)
                            ->disabled(),
                        Forms\Components\TextInput::make('new_stock')
                            ->label('New Physical Stock')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('reason')
                            ->label('Audit Reason / Notes')
                            ->placeholder('e.g. Physical inventory count, warehouse replenishment')
                            ->required(),
                    ])
                    ->action(function (SellerListing $record, array $data): void {
                        $inventoryService = app(InventoryService::class);
                        $inventoryService->adjustStock(
                            $record->id,
                            (int) $data['new_stock'],
                            $data['reason'],
                            auth()->id() ?: 1
                        );

                        Notification::make()
                            ->title('Stock Adjusted Successfully')
                            ->body("SKU {$record->seller_sku} stock updated to {$data['new_stock']}.")
                            ->success()
                            ->send();
                    }),

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
            'index' => Pages\ListSellerListings::route('/'),
            'create' => Pages\CreateSellerListing::route('/create'),
            'edit' => Pages\EditSellerListing::route('/{record}/edit'),
        ];
    }
}
