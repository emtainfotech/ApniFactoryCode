<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertisementResource\Pages;
use App\Filament\Resources\AdvertisementResource\RelationManagers;
use App\Models\Advertisement;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters;
use Carbon\Carbon;
class AdvertisementResource extends Resource
{
    
    protected static ?string $model = Advertisement::class;
    protected static ?string $navigationLabel = 'Advertisements';
    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $pluralModelLabel = 'Advertisements (1080*450)';
     public static function getNavigationBadge(): ?string
    {
        return 'Pending : '.static::getModel()::where("adminmsg","")->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success'; // Options: danger, gray, info, primary, success, warning
    }
    public static function form(Form $form): Form
    {     ///`name`, `content`, `file`, `addby`, `status`, `created_at`, `updated_at`, `adminmsg`, `screen`
        return $form
             ->schema([
                TextInput::make('name')->required(),
                RichEditor::make('content'),
                     TextInput::make('adminmsg'),
                Select::make('screen')
                    ->placeholder('Select a screen')
                    ->options([
                        'HomeScreen' => 'HomeScreen',
                        'CategoryScreen' => 'CategoryScreen',
                        // 'BrandScreen' => 'BrandScreen',
                        'CompanyScreen' => 'CompanyScreen'
                    ]),
                DatePicker::make('startdate')
                ->minDate(today()),
                DatePicker::make('enddate')
                ->minDate(now()),
                Select::make('user_id')
                    ->relationship("user","name")
                ->required()
                ->disabled()
                ->default(auth()->id()), 
                
                Select::make('status')
                    ->placeholder('Select a status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Deactive'
                    ]),
                    FileUpload::make('file')->label('Image')->directory('slider'),
            ]);
    }

    public static function table(Table $table): Table
    {
        
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('file')->square(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable()->limit(20),
                Tables\Columns\TextInputColumn::make('sequence')
                ->type('number')
                ->rules(['numeric', 'min:0'])  // validation rules
                ->extraAttributes([
                    'style' => 'max-width: 70px; white-space: normal;',
                ])
                ->updateStateUsing(function (Advertisement $record, $state) {
                    $record->sequence = $state;
                    $record->save();
                    return $state;
                }),
                Tables\Columns\TextColumn::make('startdate')->searchable()->date(),
                Tables\Columns\TextColumn::make('enddate')->searchable()->date()
                ->extraAttributes(function ($record) {
                        if ($record->enddate && Carbon::parse($record->enddate)->isPast()) {
                            return [
                                // Adds a soft red background with dark red text for readability
                               'style' => 'background-color: #fee2e2; color: #991b1b; font-weight: bold;',
                            ];
                        }
                        return [];
                    }),
                Tables\Columns\TextColumn::make('screen')->searchable(),
                ToggleColumn::make('status'),
                Tables\Columns\TextColumn::make('user.name')->sortable()->searchable()
                
            ])
            ->defaultSort('id', 'desc')
        ->filters([
            Filters\SelectFilter::make('screen')
                ->label('Screen Type')
                ->options([
                        'HomeScreen' => 'HomeScreen',
                        'CategoryScreen' => 'CategoryScreen',
                        // 'BrandScreen' => 'BrandScreen',
                        'CompanyScreen' => 'CompanyScreen'
                ]),
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
            'index' => Pages\ListAdvertisements::route('/'),
            'create' => Pages\CreateAdvertisement::route('/create'),
            'edit' => Pages\EditAdvertisement::route('/{record}/edit'),
        ];
    }    
}
