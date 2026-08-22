<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BoxPackingResource\Pages;
use App\Filament\Resources\BoxPackingResource\RelationManagers;
use App\Models\BoxPacking;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters;

class BoxPackingResource extends Resource
{
    protected static ?string $model = BoxPacking::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('pcs')->required()->numeric(),
                Select::make('maincategory_id')
                    ->placeholder('Common To All') 
                    ->relationship("maincategory","name")->default(0),
                Select::make('status')
                    ->placeholder('Select a status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('pcs')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('maincategory_id')
                ->label('Main Category') // Set the label for the column
               ->formatStateUsing(function ($state, $record) { // Use $state and $record
                    if ($record->maincategory_id === 0) {
                        return 'Common To All';
                    }

                    // Assumes a 'maincategory' relationship is defined in your model
                    return $record->maincategory->name;
                }),
                Tables\Columns\TextColumn::make('status')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->date(),
                ToggleColumn::make('status')
            ])
            ->defaultSort('id', 'desc')
            ->filters([
            Filters\SelectFilter::make('status')
                ->label('Status')
                ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                ]),
                Filters\Filter::make('name')
                ->form([
                    TextInput::make('name')
                        ->label('Search By Name')
                        ->placeholder('Enter Name...')
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['name'], 
                            fn ($query) => $query->where('name', 'like', '%' . $data['name'] . '%')
                        );
                }),
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
            'index' => Pages\ListBoxPackings::route('/'),
            'create' => Pages\CreateBoxPacking::route('/create'),
            'edit' => Pages\EditBoxPacking::route('/{record}/edit'),
        ];
    }    
}
