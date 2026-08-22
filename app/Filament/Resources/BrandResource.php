<?php
namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
// use Filament\Tables\Enums\ActionsPosition;
//   use Filament\Tables\Actions\Position;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use App\Models\Company;
use App\Models\Category;
use DB;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters;
use Filament\Forms\Get; // For reactive state state inspection
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\ViewField; 
use Filament\Forms\Components\Toggle; 
class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';      
    protected static ?string $navigationBadgeTooltip = 'Number of pending orders';

    public static function getNavigationBadge(): ?string
    {
        return 'Processing : '.static::getModel()::where('type', 'Processing')->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning'; // Options: danger, gray, info, primary, success, warning
    }
    public static function form(Form $form): Form
{ 
     return $form
        ->schema([
            Forms\Components\Card::make()
                ->schema([
                    Select::make('company_id')
                        ->label('Company')
                        ->relationship('company_ineditpage', 'name') // Pulls Company Name, stores company_id
                        ->searchable()
                        ->preload()
                        ->required()->disabled(),
                ]),

            // SEPARATE SECTION: Custom view table placed right before the save buttons
            ViewField::make('company_brands_comparison')
                ->label('All Company Brands Comparison')
                ->view('filament.forms.components.company-brands-table') // Points to the blade view
                ->columnSpanFull(), // Forces the table to expand to full width
        ]);
}

public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->select('brands.*')
        ->groupBy('name');
}
public static function table(Table $table): Table
{
     return $table
        // ->modifyQueryUsing() has been removed from here completely
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Brand Name')
                ->searchable()
                ->sortable(),
            // Company ID Column
            Tables\Columns\TextColumn::make('company.name')
                ->label('Company ID')
                ->sortable(),

            // Trademark Column
            Tables\Columns\TextColumn::make('maincategory.name')
                ->label('MainCategory')
                ->searchable(),
            BadgeColumn::make('type')
                ->label('Type')
                ->searchable()
                ->colors([
                    'warning' => 'Processing',   // Amber color
                    'success' => 'Registered',   // Green color
                    'danger'  => 'Unregistred',  // Red color
                ]),
            // Status Column (Renders a visual icon based on 1 or 0)
            Tables\Columns\IconColumn::make('status')
                ->label('Status')
                ->options([
                    'heroicon-o-check-circle' => fn ($state): bool => $state === 1,
                    'heroicon-o-x-circle' => fn ($state): bool => $state !== 1,
                ])
                ->colors([
                    'success' => fn ($state): bool => $state === 1,
                    'danger' => fn ($state): bool => $state !== 1,
                ]),
        ])
        ->filters([
            Filters\SelectFilter::make('company_id')   
                ->label('Company')
                 ->relationship("company","name")
                 ->searchable(),
            Filters\SelectFilter::make('category_id')
                ->label('Category')
                 ->relationship("category","name")
                 ->searchable(),  
                 Filters\SelectFilter::make('type')
                    ->placeholder('Select a type')
                    ->options([
                        'Registered' => 'Registered',
                        'Unregistered' => 'Unregistered',
                        'Processing' => 'Processing'
                    ]),
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }    
public function getCachedTabs(): array
{
    return $this->getTabs();
}
}
