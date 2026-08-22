<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Customer;
use App\Models\User;
use App\Models\Company;
use App\Models\Category;
use App\Models\Brand; 
use App\Models\order; 
use App\Models\Ticket; 
use App\Models\Product;
use DB;
class MyCounterWidget extends BaseWidget
{
    //  protected static string $view = 'filament.widgets.admin-dashboard';

    // // Sets widget width to take up the full screen width
    // protected int | string | array $columnSpan = 'full';
    //  protected function getViewData(): array
    // {
    //     return [
    //         // Section 1: Sales & Orders
    //         'sales_volume'   => 34,
    //         'active_orders'  => 45,
    //         'pending_orders' => 7,
    //         'returns'        => 9,

    //         // Section 2: Sellers & Catalog
    //         'total_sellers'  => 56,
    //         'total_brands'   => Brand::count(),
    //         'total_category' => Category::count(),
    //     ];
    // }
    protected function getCards(): array
    {
        $pendingord = DB::table('order_status as os1')
                ->select('os1.order_no', 'os1.status')
                ->join(DB::raw('(SELECT order_no, MAX(created_at) AS last_status_time 
                                 FROM order_status 
                                 GROUP BY order_no) as os2'), function($join) {
                    $join->on('os1.order_no', '=', 'os2.order_no')
                    ->where('os1.status', '=', 'pending')
                         ->on('os1.created_at', '=', 'os2.last_status_time');
                });
         $processingord = DB::table('order_status as os1')
                ->select('os1.order_no', 'os1.status')
                ->join(DB::raw('(SELECT order_no, MAX(created_at) AS last_status_time 
                                 FROM order_status 
                                 GROUP BY order_no) as os2'), function($join) {
                    $join->on('os1.order_no', '=', 'os2.order_no')
                    ->where('os1.status', '=', 'Order Processed')
                         ->on('os1.created_at', '=', 'os2.last_status_time');
                });
        return [
                Card::make('Total App Customer', Customer::count())
                // ->description('All orders in the system')
                // ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),
                Card::make('Total Web Sellers', User::count()),
                Card::make('Total Category', Category::count()),
                Card::make('Active Company', Company::where('status', '1')->count()),
                Card::make('Total Brand', Brand::count()),
                Card::make('Active Ticket', Ticket::where('status', '1')->count()),
                Card::make('Total Product', Product::count()),
                Card::make('Total order', order::count()),
                Card::make('Processing Orders', $processingord->count()),
                Card::make('Pending Orders', $pendingord->count()),
                Card::make('Total Revenue',number_format(Order::sum('grandtotal'), 2)),
        ];
    }
}
