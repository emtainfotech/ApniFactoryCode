<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource\RelationManagers;
use App\Models\Coupon;
use App\Models\Company;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Filters;
class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')->required(),
                TextInput::make('name')->required(),
                TextInput::make('title')->required(),
                Select::make('type')
                    ->placeholder('Select a Type')
                    ->options([
                        'percentage' => 'Percentage',
                        'amount' => 'Amount',
                    ])->required(),
                TextInput::make('price')->required()->type('number')      // forces html input type number
                    ->numeric()
                    ->minValue(1)         // positive numbers only (>= 1)=
                    ,
                 DatePicker::make('startdate')->required()->minDate(date('Y-m-d')),
                 DatePicker::make('expiry')->required()->minDate(date('Y-m-d')),	
                FileUpload::make('image')
                                                ->label('Image')
                                                ->image()
                                                ->directory('coupon'),
                RichEditor::make('description')->required(),
                Hidden::make('couponon')->default('total'),
                Hidden::make('couponapplyon')->default('total'),
                Select::make('status')
                    ->placeholder('Select a status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                    ]),
                Hidden::make('user_id')->default(auth()->id())
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
              
                Tables\Columns\ImageColumn::make('image')->square(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('user.company.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('price')->sortable(),
                Tables\Columns\TextColumn::make('expiry')->date(),
                Tables\Columns\TextColumn::make('created_at')->date(),
                ToggleColumn::make('status')
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                 Filters\SelectFilter::make('Company Name')
                ->options(
                    // Fetch all company names for the select filter options
                    // Ensure you are using the correct relationships
                    Company::all()->pluck('name', 'id')
                )
                ->query(function (Builder $query, array $data): Builder {
                    if (empty($data['value'])) { // Check if a value is selected
                        return $query;
                    }
                    return $query->whereHas('user.company', function (Builder $companyQuery) use ($data) {
                        $companyQuery->where('id', $data['value']); // Filter based on company id
                    });
                }),
                 Filters\SelectFilter::make('status')
                ->label('Status')
                ->options([
                        '1' => 'Active',
                        '0' => 'Deactive',
                ]),
                Filters\Filter::make('date_range')
                ->form([
                    DatePicker::make('expiry'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['expiry'], fn ($q, $date) => $q->whereDate('expiry', '>=', $date))
                        ->when($data['expiry'], fn ($q, $date) => $q->whereDate('expiry', '<=', $date));
                })
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }    
}
