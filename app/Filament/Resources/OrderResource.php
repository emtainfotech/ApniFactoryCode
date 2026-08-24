<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Forms\Components\TextInput;
// use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use DB;
class OrderResource extends Resource
{
    protected static ?string $model = order::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationBadgeTooltip = 'Number of pending orders';

public static function getNavigationBadge(): ?string
{
    return 'Pending : '.(string) DB::table('orders')
        ->join('order_status', 'orders.orderno', '=', 'order_status.order_no')
        ->where('order_status.status', 'pending')
        ->whereIn('order_status.id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('order_status')
                ->groupBy('order_no');
        })
        ->count();
}


    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger'; // Options: danger, gray, info, primary, success, warning
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }


 public static function table(Table $table): Table
{ 
    return $table
        ->columns([
             Columns\TextColumn::make('orderno')
                ->label('Orderno')
                ->searchable()
                ->sortable(),
            Tables\Columns\BadgeColumn::make('status') 
                ->label('Status')
                ->getStateUsing(function ($record) {
                    $st = \DB::table('order_status')
                        ->where('order_no', $record->orderno)
                        ->latest('id')
                        ->value('status');
                    return $st ?: 'Pending Seller';
                })
                ->colors([
                    'warning'   => fn ($state) => in_array(strtolower($state), ['pending', 'pending seller', 'wait for confirmation', 'order received']),
                    'success'   => fn ($state) => in_array(strtolower($state), ['accepted', 'delivered', 'completed', 'success']),
                    'info'      => fn ($state) => in_array(strtolower($state), ['processing', 'order processed']),
                    'primary'   => fn ($state) => in_array(strtolower($state), ['in transit', 'out for delivery', 'shipped']),
                    'danger'    => fn ($state) => in_array(strtolower($state), ['rejected', 'failed']),
                    'secondary' => fn ($state) => in_array(strtolower($state), ['cancelled', 'expired']),
                ]),
            Columns\TextColumn::make('user.name')
                ->label('Seller')
                ->searchable()
                ->sortable(),
            Columns\TextColumn::make('customer.name')
                ->label('Customer')
                ->searchable()
                ->sortable(),
           
            Columns\TextColumn::make('netamount')->color('primary'),
            Columns\TextColumn::make('taxamount')->searchable(),
            Columns\TextColumn::make('grandtotal')->color('success'),
            Columns\TextColumn::make('created_at')
                ->label('Date')
                ->dateTime()
                ->sortable(),
       
        ])
        ->filters([
            Filters\SelectFilter::make('user_id')
                ->label('Seller')
                 ->relationship("user","name")
                 ->searchable(),
            Filters\SelectFilter::make('customer_id')
                ->label('Customer')
                 ->relationship("customer","name")
                 ->searchable(),
            Filters\Filter::make('orderno_search')
                ->form([
                    Components\TextInput::make('orderno')
                        ->label('Search Order No')
                        ->placeholder('Enter order number...')
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['orderno'], 
                            fn ($query) => $query->where('orderno', 'like', '%' . $data['orderno'] . '%')
                        );
                }),
            
            Filters\Filter::make('date_range')
                ->form([
                    Components\DatePicker::make('from'),
                    Components\DatePicker::make('to'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['to'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                })
        ])
        ->defaultSort('created_at', 'desc')
        ->actions([
        //      Action::make('view-order')
        // ->label('View')
        // ->icon('heroicon-o-eye')
        // ->url(fn ($record) =>  ViewOrder::getUrl([$record->getKey()]))
         Action::make('view-order')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => static::getUrl('view-order', $record))
                 
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view-order' => Pages\ViewOrder::route('/{record}')
        ];
    } 
    /*
     public static function infolist(Infolist $infolist): Infolist
    { 
        return $infolist
            ->schema([
                Section::make('Invoice Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('orderno')
                                    ->label('Order Number')
                                    ->weight('bold')
                                    ->size('lg'),
                                TextEntry::make('created_at')
                                    ->label('Order Date')
                                    ->dateTime('d M Y, H:i'),
                                TextEntry::make('id')
                                    ->label('Invoice ID')
                                    ->prefix('#INV-'),
                            ]),
                    ])
                    ->columnSpan(2),

                Section::make('Customer Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->label('Customer Name'),
                                TextEntry::make('customer.email')
                                    ->label('Email'),
                                TextEntry::make('customer.phone')
                                    ->label('Phone'),
                                TextEntry::make('address')
                                    ->label('Billing Address')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(1),

                Section::make('Order Summary')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('netamount')
                                    ->label('Net Amount')
                                    ->money('INR'),
                                TextEntry::make('taxamount')
                                    ->label('Tax Amount')
                                    ->money('INR'),
                                TextEntry::make('grandtotal')
                                    ->label('Grand Total')
                                    ->money('INR')
                                    ->weight('bold')
                                    ->color('success'),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('sellercouponamount')
                                    ->label('Seller Coupon Discount')
                                    ->money('INR')
                                    ->color('warning'),
                                TextEntry::make('admincouponamount')
                                    ->label('Admin Coupon Discount')
                                    ->money('INR')
                                    ->color('warning'),
                            ]),
                        
                        TextEntry::make('taxdetail')
                            ->label('Tax Details')
                            ->columnSpanFull(),
                        
                        TextEntry::make('admincoupondetail')
                            ->label('Admin Coupon Details')
                            ->columnSpanFull()
                            ->visible(fn ($record) => !empty($record->admincoupondetail)),
                    ])
                    ->columnSpan(3),

                // Product Details Table Section
                ViewEntry::make('product_details')
                    ->label('')
                    ->view('filament.resources.order.product-details-table')
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }
    */
}
