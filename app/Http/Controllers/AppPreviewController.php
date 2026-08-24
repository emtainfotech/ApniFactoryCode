<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Services\AlternativeSellerService;
use Illuminate\Support\Facades\DB;

class AppPreviewController extends Controller
{
    /**
     * Display live in-browser Customer Mobile App experience simulator.
     */
    public function index(Request $request)
    {
        $data['categories'] = Category::whereIn('status', ['1', 'Active', 'active'])->get();
        $data['products'] = Product::whereIn('status', ['1', 'Active', 'active'])->with('attributes')->limit(8)->get();
        
        $customer = Customer::first();
        $data['customer'] = $customer;
        
        if ($customer) {
            $data['orders'] = Order::where('customer_id', $customer->id)->orderBy('id', 'desc')->limit(5)->get();
            $data['notifications'] = DB::table('notifications')
                ->where('customer_id', $customer->id)
                ->orWhere('type', 'customer')
                ->orderBy('id', 'desc')
                ->limit(6)
                ->get();
        } else {
            $data['orders'] = Order::orderBy('id', 'desc')->limit(5)->get();
            $data['notifications'] = DB::table('notifications')->orderBy('id', 'desc')->limit(6)->get();
        }

        $altService = app()->make(AlternativeSellerService::class);
        $sampleRejectedStatus = DB::table('order_status')
            ->whereIn('status', ['Cancelled', 'Rejected', 'cancelled', 'rejected'])
            ->first();

        $sampleRejectedOrder = $sampleRejectedStatus ? Order::find($sampleRejectedStatus->order_id) : Order::first();

        $data['sampleAlternatives'] = $sampleRejectedOrder 
            ? $altService->getAlternativeSellers($sampleRejectedOrder, 3)
            : [];

        return view('customer.app_preview', $data);
    }
}
