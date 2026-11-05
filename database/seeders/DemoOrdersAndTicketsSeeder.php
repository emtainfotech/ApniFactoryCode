<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoOrdersAndTicketsSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('email', 'admin@apnifactory.com')->first() ?? User::first();
        $seller = User::where('email', 'seller@apnifactory.local')->first() ?? User::where('id', '!=', $admin->id)->first();
        $customer = Customer::first();

        if (!$customer) {
            $customer = Customer::create([
                'name'     => 'Metro Infrastructure Ltd',
                'email'    => 'procurement@metroinfra.in',
                'mobile'   => '9876501234',
                'password' => bcrypt('buyer@123'),
                'status'   => 'active',
            ]);
        }

        $sellerId = $seller ? $seller->id : 1;
        $customerId = $customer ? $customer->id : 1;

        // 1. Seed Demo Orders
        $orderConfigs = [
            [
                'orderno'        => 'ORD-2026-IND-0101',
                'net'            => 45000.00,
                'tax'            => 8100.00,
                'total'          => 53100.00,
                'payment_status' => 'Paid',
                'status'         => 'Order Processed',
                'product'        => 'Industrial Primer Coat (20L Drum)',
                'qty'            => 10,
                'price'          => 4500.00,
            ],
            [
                'orderno'        => 'ORD-2026-IND-0102',
                'net'            => 68000.00,
                'tax'            => 12240.00,
                'total'          => 80240.00,
                'payment_status' => 'Paid',
                'status'         => 'Order Processed',
                'product'        => 'Weather Shield Exterior Emulsion',
                'qty'            => 20,
                'price'          => 3400.00,
            ],
            [
                'orderno'        => 'ORD-2026-IND-0103',
                'net'            => 28000.00,
                'tax'            => 5040.00,
                'total'          => 33040.00,
                'payment_status' => 'Pending',
                'status'         => 'pending',
                'product'        => 'Epoxy Floor Coating (Kit)',
                'qty'            => 8,
                'price'          => 3500.00,
            ],
            [
                'orderno'        => 'ORD-2026-IND-0104',
                'net'            => 95000.00,
                'tax'            => 17100.00,
                'total'          => 112100.00,
                'payment_status' => 'Paid',
                'status'         => 'Order Processed',
                'product'        => 'Heavy Canvas Drop Sheets & Masking',
                'qty'            => 50,
                'price'          => 1900.00,
            ],
            [
                'orderno'        => 'ORD-2026-IND-0105',
                'net'            => 38000.00,
                'tax'            => 6840.00,
                'total'          => 44840.00,
                'payment_status' => 'Pending',
                'status'         => 'pending',
                'product'        => 'Polyurethane Clear Gloss Varnish',
                'qty'            => 15,
                'price'          => 2533.33,
            ],
        ];

        foreach ($orderConfigs as $cfg) {
            $order = Order::updateOrCreate(
                ['orderno' => $cfg['orderno']],
                [
                    'customer_id'    => $customerId,
                    'user_id'        => $sellerId,
                    'address'        => 'Transport Nagar Gate 4, Warehouse 8, Jaipur - 302022',
                    'netamount'      => $cfg['net'],
                    'taxamount'      => $cfg['tax'],
                    'grandtotal'     => $cfg['total'],
                    'payment_status' => $cfg['payment_status'],
                    'paid_at'        => $cfg['payment_status'] === 'Paid' ? now() : null,
                    'created_at'     => now()->subDays(rand(1, 10)),
                    'updated_at'     => now(),
                ]
            );

            OrderDetail::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'orderno'     => $order->orderno,
                    'customer_id' => $customerId,
                    'productname' => $cfg['product'],
                    'hsn'         => '3208',
                    'attribute'   => json_encode([['qty' => $cfg['qty'], 'price' => $cfg['price']]]),
                    'created_at'  => $order->created_at,
                    'updated_at'  => $order->updated_at,
                ]
            );

            // Seed order_status for Filament Overview Widget counter
            DB::table('order_status')->updateOrInsert(
                ['order_no' => $order->orderno],
                [
                    'order_id'   => $order->id,
                    'user_id'    => $sellerId,
                    'status'     => $cfg['status'],
                    'msg'        => "Order status updated to: {$cfg['status']}",
                    'created_at' => $order->created_at,
                    'updated_at' => now(),
                ]
            );
        }

        // 2. Seed Active Tickets for Counter Widget
        if (\Illuminate\Support\Facades\Schema::hasTable('tickets')) {
            $ticketTopics = [
                ['topic' => 'Bulk Pricing Inquiry for Institutional Project', 'msg' => 'Requesting commercial rate for 200 drums of Exterior Primer.'],
                ['topic' => 'Transporter Bilty Clarification', 'msg' => 'Kindly update tracking docket for consignment dispatched yesterday.'],
                ['topic' => 'GST Invoice Correction Request', 'msg' => 'Please re-issue B2B invoice reflecting revised GSTIN.'],
            ];

            foreach ($ticketTopics as $idx => $t) {
                DB::table('tickets')->updateOrInsert(
                    ['topic' => $t['topic']],
                    [
                        'msg'        => $t['msg'],
                        'adminmsg'   => 'Under review by platform support desk.',
                        'addby'      => 'seller',
                        'user_id'    => $sellerId,
                        'status'     => '1', // Active
                        'created_at' => now()->subDays($idx + 1),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // 3. Seed Sliders & Advertisements if empty
        if (\Illuminate\Support\Facades\Schema::hasTable('sliders')) {
            DB::table('sliders')->updateOrInsert(
                ['id' => 1],
                [
                    'title'      => 'Manufacturer Direct Wholesale Pricing',
                    'image'      => 'slider1.jpg',
                    'screen'     => 'home',
                    'sequence'   => 1,
                    'status'     => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('advertisements')) {
            DB::table('advertisements')->updateOrInsert(
                ['id' => 1],
                [
                    'name'       => 'B2B Monsoon Factory Clearance',
                    'content'    => 'Special discounts on bulk paints',
                    'file'       => 'ad1.jpg',
                    'addby'      => 'admin',
                    'user_id'    => $sellerId,
                    'screen'     => 'home',
                    'status'     => '1',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
