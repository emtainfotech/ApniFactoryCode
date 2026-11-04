<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentProductsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        return 'Recently Updated Catalogue Products';
    }

    protected function getTableQuery(): Builder
    {
        return Product::query()
            ->with(['brand', 'category', 'listings'])
            ->withCount(['variants', 'listings'])
            ->latest('updated_at')
            ->limit(8);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\ImageColumn::make('image')->square()->label('Image'),
            Tables\Columns\TextColumn::make('name')->sortable()->searchable()->label('Product Name')->weight('bold'),
            Tables\Columns\TextColumn::make('brand.name')->label('Brand'),
            Tables\Columns\TextColumn::make('category.name')->label('Category'),
            Tables\Columns\BadgeColumn::make('variants_count')
                ->label('Variants')
                ->colors(['primary']),
            Tables\Columns\BadgeColumn::make('listings_count')
                ->label('Seller SKUs')
                ->colors(['success']),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'success' => fn ($state): bool => in_array(strtolower((string)$state), ['active', '1']),
                    'danger'  => fn ($state): bool => !in_array(strtolower((string)$state), ['active', '1']),
                ]),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->label('Last Updated'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('edit')
                ->url(fn (Product $record): string => ProductResource::getUrl('edit', $record))
                ->icon('heroicon-s-pencil')
                ->label('Edit'),
        ];
    }
}
