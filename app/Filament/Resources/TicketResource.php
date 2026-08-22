<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationBadgeTooltip = 'Number of pending orders';

    public static function getNavigationBadge(): ?string
    {
        return 'Pending : '.static::getModel()::where('status', 'Pending')->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning'; // Options: danger, gray, info, primary, success, warning
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('topic')->required(),
                RichEditor::make('msg'),
                Select::make('status')
                    ->placeholder('Select a status')
                    ->options([
                        'Pending' => 'Pending',
                        'Completed' => 'Completed',
                        'Reject' => 'Reject',
                    ]),
                     TextInput::make('adminmsg')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
             ->columns([
                Tables\Columns\TextColumn::make('user.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('topic')->sortable()->searchable()->limit(20),
                Tables\Columns\TextColumn::make('created_at')->date(),
                Tables\Columns\TextColumn::make('status')
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
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
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }    
}
