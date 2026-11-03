<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomNotificationResource\Pages;
use App\Models\Notification;
use App\Models\Customer;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;

class CustomNotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'Push Notifications';
    protected static ?string $pluralModelLabel = 'Push Notifications';
    protected static ?string $modelLabel = 'Push Notification';
    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer'])
            ->whereIn('type', ['broadcast', 'promo', 'custom', 'system', 'customer']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    TextInput::make('title')
                        ->label('Notification Title')
                        ->required()
                        ->placeholder('e.g. 🪔 Factory Direct Mega Sale!')
                        ->columnSpan(2),

                    Textarea::make('msg')
                        ->label('Notification Message / Body')
                        ->required()
                        ->rows(3)
                        ->placeholder('e.g. Up to 40% OFF on all Exterior Primers & Industrial Paints today.')
                        ->columnSpan(2),

                    FileUpload::make('image')
                        ->label('Notification Banner Image (Optional)')
                        ->directory('notifications')
                        ->image()
                        ->imageResizeMode('cover')
                        ->maxSize(5120)
                        ->helperText('Displayed as Big Picture in mobile Notification Shade and In-App cards.')
                        ->columnSpan(2),

                    Grid::make(2)->schema([
                        Select::make('type')
                            ->label('Target Audience')
                            ->options([
                                'broadcast' => 'All App Users (Broadcast)',
                                'promo'     => 'Specific Customer',
                            ])
                            ->default('broadcast')
                            ->reactive()
                            ->required(),

                        Select::make('customer_id')
                            ->label('Select Recipient Customer')
                            ->options(fn () => Customer::pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (callable $get) => $get('type') === 'promo')
                            ->required(fn (callable $get) => $get('type') === 'promo'),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('screen')
                            ->label('Deep Link Action (When Tapped)')
                            ->options([
                                'none'        => 'Standard Notification (Open Home)',
                                'product'     => 'Open Product Details',
                                'category'    => 'Open Category',
                                'brand'       => 'Open Brand Page',
                                'coupon'      => 'Open Coupons & Offers',
                                'orders'      => 'Open Order History',
                                'wishlist'    => 'Open Wishlist',
                                'web'         => 'Open External Web URL',
                            ])
                            ->default('none')
                            ->reactive(),

                        TextInput::make('screenid')
                            ->label('Deep Link Target ID / Parameter')
                            ->placeholder('e.g. Product ID, Category ID, or URL')
                            ->visible(fn (callable $get) => $get('screen') && $get('screen') !== 'none'),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Banner')
                    ->square()
                    ->defaultImageUrl(url('storage/app/public/slider/banner.jpg')),

                TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('msg')
                    ->label('Message')
                    ->limit(40)
                    ->searchable(),

                BadgeColumn::make('type')
                    ->label('Audience')
                    ->enum([
                        'broadcast' => 'All Users',
                        'promo'     => 'Specific',
                        'customer'  => 'Customer',
                    ])
                    ->colors([
                        'success' => 'broadcast',
                        'warning' => 'promo',
                        'primary' => 'customer',
                    ]),

                TextColumn::make('screen')
                    ->label('Deep Link')
                    ->formatStateUsing(fn ($record) => ($record->screen && $record->screen !== 'none') ? "{$record->screen} (#{$record->screenid})" : 'Open App'),

                TextColumn::make('customer.name')
                    ->label('Recipient')
                    ->default('Broadcast (All)'),

                TextColumn::make('created_at')
                    ->label('Sent Date')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomNotifications::route('/'),
            'create' => Pages\CreateCustomNotification::route('/create'),
        ];
    }
}

