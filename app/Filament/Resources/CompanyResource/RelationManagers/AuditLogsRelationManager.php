<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class AuditLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'auditLogs';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Company Change Logs & Activity History';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->disabled(),
                Forms\Components\Textarea::make('description')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('actor_role')
                    ->label('Role')
                    ->colors([
                        'primary' => 'seller',
                        'warning' => 'admin',
                        'secondary' => 'system',
                    ])
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('actor_name')
                    ->label('Changed By')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('action_type')
                    ->label('Action')
                    ->colors([
                        'success' => 'category_price_adjustment',
                        'primary' => 'price_adjustment',
                        'warning' => 'min_order_value_change',
                        'danger'  => 'commission_change',
                        'info'    => 'profile_update',
                        'secondary' => 'banner_update',
                    ])
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('title')
                    ->label('Title & Summary')
                    ->description(fn ($record) => $record->description)
                    ->wrap(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->color('secondary')
                    ->size('sm'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action_type')
                    ->label('Action Type')
                    ->options([
                        'price_adjustment' => 'Price Adjustment',
                        'category_price_adjustment' => 'Category Price Adjustment',
                        'min_order_value_change' => 'Min Order Value Change',
                        'commission_change' => 'Commission Change',
                        'profile_update' => 'Profile Update',
                        'banner_update' => 'Banner Update',
                    ]),
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view_payload')
                    ->label('View Changes')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->modalHeading('Change Details & Values')
                    ->modalContent(function ($record) {
                        return view('filament.components.audit-log-modal', ['record' => $record]);
                    }),
            ])
            ->bulkActions([
                //
            ]);
    }
}
