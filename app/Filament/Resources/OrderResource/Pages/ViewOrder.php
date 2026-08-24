<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use App\Models\order;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Profile;
use DB;
class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    // Use custom view instead of form schema
    protected static string $view = 'filament.pages.view-order';

    protected function getViewData(): array
    {
        $order = order::where("id", $this->record->id)->first();
        $cartattribut = DB::table("orderdetail")->where("order_id", $order->id)->get();
        $seller = Company::where("user_id", $order->user_id)->first();
        $track = DB::table("order_tracks")->where("order_id", $order->id)->first();
        $status = DB::table("order_status")->where("order_id", $order->id)->orderBy('id', 'asc')->get();
        $transection = DB::table("transections")->where("order_id", $order->id)->first();

        // Check for rejection records
        $rejection = DB::table("order_status")
            ->where("order_id", $order->id)
            ->where(function ($q) {
                $q->where('status', 'like', '%reject%')
                  ->orWhere('status', 'like', '%cancel%');
            })
            ->latest('id')
            ->first();

        // Seller performance metrics
        $totalSellerOrders = order::where("user_id", $order->user_id)->count();
        $rejectedSellerOrders = DB::table("order_status")
            ->where("user_id", $order->user_id)
            ->where(function ($q) {
                $q->where('status', 'like', '%reject%')
                  ->orWhere('status', 'like', '%cancel%');
            })
            ->distinct('order_no')
            ->count('order_no');

        $rejectionRate = $totalSellerOrders > 0 ? round(($rejectedSellerOrders / $totalSellerOrders) * 100, 1) : 0;

        return [
            'record'               => $this->record,
            'orderdetail'          => $cartattribut,
            'seller'               => $seller,
            'track'                => $track,
            'status'               => $status,
            'transections'         => $transection,
            'rejection'            => $rejection,
            'totalSellerOrders'    => $totalSellerOrders,
            'rejectedSellerOrders' => $rejectedSellerOrders,
            'rejectionRate'        => $rejectionRate,
        ];
    }
  
}