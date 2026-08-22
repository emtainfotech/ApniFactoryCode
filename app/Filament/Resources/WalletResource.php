<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Filament\Resources\WalletResource\RelationManagers;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
// use Filament\Notifications\Notification;
// use App\Filament\Resources\WalletResource\Pages\Notification;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
        public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Card::make()
                    ->schema([
                        Components\Select::make('user_id')
                            ->label('User')
                            ->relationship("user","name")
                            // ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $balance = Wallet::where('user_id', $state)
                                    ->latest()
                                    ->value('balance') ?? 0;
                                $set('current_balance', number_format($balance, 2));
                            }),

                       
                        Components\Select::make('action')
                            ->label('Transaction Type')
                            ->options([
                                'credit' => 'Credit',
                                'debit' => 'Debit',
                            ])
                            ->required()
                            ->reactive(),

                        Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->prefix('$')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $action = $get('action');
                                $current = (float) str_replace(',', '', $get('current_balance') ?? '0');
                                
                                if ($action === 'debit' && $state > $current) {
                                    $set('amount', $current);
                                    return;
                                }
                                
                                $newBalance = $action === 'credit' 
                                    ? $current + $state 
                                    : $current - $state;
                                $set('new_balance', number_format($newBalance, 2));
                                $set('balance', $newBalance);
                                $set($action === 'credit' ? 'credit' : 'debit', $state);
                            }),
                         Components\TextInput::make('current_balance')
                            ->label('Current Balance')
                            ->prefix('$')
                            ->disabled(),

                        Components\TextInput::make('new_balance')
                            ->label('New Balance')
                            ->prefix('$')
                            ->disabled(),
                    ])
                    ->columns(2),

                Components\Card::make()
                    ->schema([
                        Components\Textarea::make('msg')
                            ->label('Transaction Note')
                            ->required()
                            ->columnSpanFull(),
                        Components\Hidden::make('credit')->default(0),
                        Components\Hidden::make('debit')->default(0),
                        Components\Hidden::make('order_id')->default(0),
                        Components\Hidden::make('value')->default(0),
                        Components\Hidden::make('commission')->default(0),
                        Components\Hidden::make('refundtobuyer')->default(0),
                        Components\Hidden::make('balance'),
                        Components\Hidden::make('addby')->default('admin'),
                        Components\Hidden::make('orderno')->default('AF' . now()->format('Ymd-His')),
                    ])
            ]);
    }
 public static function table(Table $table): Table
{
    return $table
        ->columns([
            Columns\TextColumn::make('user.name')
                ->label('User')
                ->searchable()
                ->sortable(),

            Columns\TextColumn::make('orderno')
                ->label('Order No')
                ->searchable()
                ->sortable(),

            Columns\TextColumn::make('amount')
                // ->money()
                ->color(function ($record) {
                    return $record->debit > 0 ? 'danger' : 'success';
                })
                ->formatStateUsing(function ($record) {
                    return $record->debit > 0 ? $record->debit : $record->credit;
                }),

            Columns\TextColumn::make('commission')->searchable(),
                // ->money(),

            Columns\BadgeColumn::make('type')
                ->label('Type')
                ->getStateUsing(function ($record) {
                    return $record->debit > 0 ? 'debit' : 'credit';
                })
                ->colors([
                    'success' => 'credit',
                    'danger' => 'debit',
                ])
                ->formatStateUsing(fn ($state) => strtoupper($state)),

            Columns\TextColumn::make('balance'),
                // ->money(),

            // Columns\TextColumn::make('msg')
            //     ->label('Note')
            //     ->limit(30)
            //     ->tooltip(fn ($record) => $record->msg),

            Columns\TextColumn::make('created_at')
                ->label('Date')
                ->dateTime()
                ->sortable(),

            Columns\TextColumn::make('addby')
                ->label('Processed By'),
        ])
        ->filters([
            Filters\SelectFilter::make('type')
                ->label('Transaction Type')
                ->options([
                    'credit' => 'Credit',
                    'debit' => 'Debit',
                ])
                ->query(function ($query, $data) {
                    $value = $data['value'];
                    if ($value === 'credit') {
                        return $query->where('credit', '>', 0);
                    }
                    if ($value === 'debit') {
                        return $query->where('debit', '>', 0);
                    }
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
            // Tables\Actions\EditAction::make(),
            Tables\Actions\ViewAction::make()
                ->icon('heroicon-o-document-text')
                ->color('secondary'),
                
                Tables\Actions\Action::make('download_pdf')
                ->label('')
                ->icon('heroicon-o-download') // Filament v2 ke liye download 
                ->button() // Isse yeh background box ke sath button jaisa dikhega
                ->color('success') // Aap apne hisab se color change kar sakte hain (e.g., primary
                ->url(fn ($record) => route('invoice.pdf', ['id' => $record->id]))
                ->openUrlInNewTab() // Agar aap chahte hain ki PDF naye tab me khule
                ->visible(fn ($record) => $record->addby === 'system')  
        ],position: \Filament\Tables\Actions\Position::BeforeColumns) // Isse icons sabse pehle (first
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
            'index' => Pages\ListWallets::route('/'),
            'create' => Pages\CreateWallet::route('/create'),
            'edit' => Pages\EditWallet::route('/{record}/edit'),
        ];
    }    
}
