<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Filament\Resources\SliderResource\RelationManagers;
use App\Models\Slider;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters;
use Carbon\Carbon;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationLabel = 'Slider';
    protected static ?string $pluralModelLabel = 'Slider (440*180)';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->required(),
                FileUpload::make('image')
                                                ->label('Image')
                                                ->image()
                                                ->directory('silder'),
                Select::make('status')
                    ->placeholder('Select a status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                    ]),
                DatePicker::make('startdate'),
                DatePicker::make('enddate')
                ->minDate(today()),
                Select::make('screen')
                    ->placeholder('Select a screen')
                    ->options([
                        'HomeScreen' => 'HomeScreen',
                        'CategoryScreen' => 'CategoryScreen',
                        // 'BrandScreen' => 'BrandScreen',
                        'CompanyScreen' => 'CompanyScreen'
                    ]),
                    Select::make('company_id')
                    ->placeholder('Select a Company')
                    ->relationship("company","name")->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->square(),
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
                Tables\Columns\TextInputColumn::make('sequence')
                ->type('number')
                ->rules(['numeric', 'min:0'])  // validation rules
                ->extraAttributes([
                    'style' => 'max-width: 70px; white-space: normal;',
                ])
                ->updateStateUsing(function (Slider $record, $state) {
                    $record->sequence = $state;
                    $record->save();
                    return $state;
                }),
                Tables\Columns\TextColumn::make('company.name')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->date(),
            ])
            ->filters([
                
            Filters\SelectFilter::make('screen')
                ->label('Screen Type')
                ->options([
                        'HomeScreen' => 'HomeScreen',
                        'CategoryScreen' => 'CategoryScreen',
                        // 'BrandScreen' => 'BrandScreen',
                        'CompanyScreen' => 'CompanyScreen'
                ]),
            Filters\SelectFilter::make('company_id')   
                ->label('Company')
                 ->relationship("company","name")
                 ->searchable(),
             Filters\Filter::make('enddate')
                ->form([
                    DatePicker::make('enddate'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['enddate'], fn ($q, $date) => $q->whereDate('enddate', '<=', $date));
                })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }    
}
