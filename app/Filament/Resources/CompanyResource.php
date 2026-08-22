<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use App\Models\BankDetails;
use App\Models\India_pincode;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters;
use App\Models\MainCategory;

use Filament\Tables\Columns\ToggleColumn;
class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->relationship("user","name")
                    ->searchable(),
                TextInput::make('name')->required(),
                TextInput::make('email')->required(),
                TextInput::make('mobile')->required()->numeric(),
                TextInput::make('gst')->required(),
                TextInput::make('crn')->label('CRN (if crn is not available put it 0)')->default(0),
                Select::make('maincategory_id')
                    ->placeholder('Select a maincategory')
                    ->relationship("maincategory","name")->required(),
                TextInput::make('minordervalue')->required()->numeric(),
                 Select::make('state')
                    ->placeholder('Select a state')
                    ->options( India_pincode::select('state')->distinct()->pluck('state', 'state')
                    )->required()
                    ->reactive() // important to update city on state change
                    ->afterStateUpdated(fn ($state, callable $set) => $set('city', null)),
                Select::make('city')
                    ->label('City')
                    ->options(fn ($get) => India_pincode::where('state', $get('state'))->distinct()->pluck('city', 'city'))
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('pincode', null))
                    ->required(),
                 Select::make('pincode')
                    ->placeholder('Select a Pincode')
                     ->options(fn ($get) => India_pincode::where('city', $get('city'))->distinct()->pluck('pincode', 'pincode'))
                    ->required(),
                FileUpload::make('photo')
                                                ->label('Company Banner')
                                                ->image()
                                                ->directory('product'),
                Select::make('status')
                    ->placeholder('Select a status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                    ]),
                TextInput::make('comission')->numeric()->required(),
                /*    Forms\Components\Section::make('Account Details')
                            ->schema([
                             Forms\Components\Repeater::make('bank')
                                        ->relationship()
                                        ->schema([
                                            TextInput::make('bank.accountholder'),
                                            TextInput::make('bank.accountno'),
                                            TextInput::make('bank.bankname'),
                                            TextInput::make('bank.branch'),
                                            TextInput::make('bank.ifsc'),
                                             Select::make('bank.status')
                                            ->placeholder('Select a status')
                                            ->options([
                                                '1' => 'Active',
                                                '0' => 'Deactive',
                                            ]),
                                             Select::make('bank.user_id')
                                            ->relationship('user', 'name')
                                            ->default(auth()->id()), 
                                            ])
                                        ->columns(4)
                                        ->collapsible()
                                
                                ])  */
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('user.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('mobile')->searchable(),
                Tables\Columns\TextColumn::make('city')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->date(),
                ToggleColumn::make('status')
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                 Filters\SelectFilter::make('user')   
                ->label('User')
                 ->relationship("user","name")
                 ->searchable(),
                Filters\SelectFilter::make('status')
                ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                    ]),
                Filters\Filter::make('city')
                ->form([
                    TextInput::make('city')
                        ->label('Search By city')
                        ->placeholder('Enter City...')
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['city'], 
                            fn ($query) => $query->where('city', 'like', '%' . $data['city'] . '%')
                        );
                }),
                Filters\Filter::make('mobile')
                ->form([
                    TextInput::make('mobile')
                        ->label('Search By mobile')
                        ->placeholder('Enter mobile...')
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['mobile'], 
                            fn ($query) => $query->where('mobile', 'like', '%' . $data['mobile'] . '%')
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
            'index' => Pages\ListCompanies::route('/'),
            // 'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }    
}
