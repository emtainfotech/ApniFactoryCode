<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\Pages;
use App\Filament\Resources\AttributeResource\RelationManagers;
use App\Models\Attribute;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\Str;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments';
    protected static ?string $navigationGroup = 'Catalogue Management';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->lazy()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('code', Str::slug($state, '_'))),

                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(Attribute::class, 'code', ignoreRecord: true)
                            ->maxLength(100),

                        Forms\Components\Select::make('type')
                            ->options([
                                'text' => 'Text',
                                'number' => 'Number / Decimal',
                                'boolean' => 'Boolean (Yes/No)',
                                'single_select' => 'Single Select (Options)',
                                'multi_select' => 'Multi Select (Options)',
                                'date' => 'Date',
                                'measurement' => 'Measurement with Unit',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\TextInput::make('unit')
                            ->label('Unit of Measurement (e.g. Litre, Kg, mm, bar)')
                            ->visible(fn (callable $get) => in_array($get('type'), ['measurement', 'number'])),

                        Forms\Components\Textarea::make('description')
                            ->rows(3),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(1),

                        Forms\Components\Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
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
                Tables\Columns\TextColumn::make('name')->sortable()->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => fn ($state) => in_array($state, ['single_select', 'multi_select']),
                        'warning' => 'measurement',
                        'success' => 'text',
                        'secondary' => 'number',
                    ]),
                Tables\Columns\TextColumn::make('unit')->label('Unit'),
                Tables\Columns\TextColumn::make('options_count')
                    ->counts('options')
                    ->label('Options'),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'Active',
                        'danger' => 'Inactive',
                    ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'text' => 'Text',
                        'number' => 'Number',
                        'boolean' => 'Boolean',
                        'single_select' => 'Single Select',
                        'multi_select' => 'Multi Select',
                        'measurement' => 'Measurement',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\OptionsRelationManager::class,
            RelationManagers\CategoryMappingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
