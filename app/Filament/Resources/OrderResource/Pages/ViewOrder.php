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
        $order = order::where("id",$this->record->id)->first();
        $cartattribut = DB::table("orderdetail")->where("order_id",$order->id)->get();
        $data['orderdetail'] = $cartattribut;
        $seller = Company::where("user_id",$order->user_id)->first();
        $track = DB::table("order_tracks")->where("order_id",$order->id)->first();
        $status = DB::table("order_status")->where("order_id",$order->id)->get();
        $transection = DB::table("transections")->where("order_id",$order->id)->first();
        return [
            'record' => $this->record,
            'orderdetail' => $cartattribut,
            'seller' => $seller,
            'track' => $track,
            'status' => $status,
            'transections'=> $transection
        ];
    }
  
}