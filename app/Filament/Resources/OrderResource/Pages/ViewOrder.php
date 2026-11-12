<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Traits\WhatsappTraits;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
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

    protected function getActions(): array
    {
        return [
            Action::make('updateOrderStatus')
                ->label('Update Order Status & Notify WhatsApp')
                ->icon('heroicon-o-chat-alt-2')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('status')
                        ->label('New Status')
                        ->options([
                            'Processing'       => 'Processing',
                            'Accepted'         => 'Accepted',
                            'Order Processed'  => 'Order Processed',
                            'In Transit'       => 'In Transit',
                            'Out For Delivery' => 'Out For Delivery',
                            'Delivered'        => 'Delivered',
                            'Rejected'         => 'Rejected',
                            'Cancelled'        => 'Cancelled',
                        ])
                        ->required(),
                    Forms\Components\Textarea::make('note')
                        ->label('Customer Note (sent on WhatsApp template)')
                        ->default('Order status updated by Apni Factory team.')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $order = $this->record;
                    DB::table('order_status')->insert([
                        'order_id'   => $order->id,
                        'order_no'   => $order->orderno,
                        'user_id'    => $order->user_id,
                        'status'     => $data['status'],
                        'msg'        => $data['note'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $buyer = Customer::find($order->customer_id);
                    $waSent = false;
                    if ($buyer && !empty($buyer->mobile)) {
                        $waSent = WhatsappTraits::sendtobuyerthatorderstatuschangedororderplaced(
                            $buyer->mobile,
                            $data['status'],
                            $order->orderno,
                            $data['note']
                        );
                    }

                    Notification::make()
                        ->title($waSent
                            ? "Status updated to {$data['status']} & WhatsApp sent"
                            : "Status updated to {$data['status']}")
                        ->success()
                        ->send();
                }),
        ];
    }

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