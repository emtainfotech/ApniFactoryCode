<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        $userId = DB::table('users')->value('id') ?? 1;
        $customerId = DB::table('customers')->value('id') ?? 1;
        $mainCatId = DB::table('main_categories')->value('id') ?? 1;
        $catId = DB::table('categories')->value('id') ?? 1;

        // Seed Company
        $companyId = DB::table('companies')->insertGetId([
            'name'            => 'ApniFactory Prime Mills',
            'user_id'         => $userId,
            'email'           => 'seller@apnifactory.local',
            'mobile'          => '9876543210',
            'maincategory_id' => $mainCatId,
            'gst'             => '07AAAAA0000A1Z5',
            'crn'             => 'CRN123456',
            'minordervalue'   => 1000.00,
            'city'            => 'New Delhi',
            'state'           => 'Delhi',
            'pincode'         => '110001',
            'comission'       => 5.00,
            'status'          => 'Active',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Seed Brand
        DB::table('brands')->insert([
            'name'          => 'FactoryStandard',
            'user_id'       => $userId,
            'company_id'    => $companyId,
            'mid'           => $mainCatId,
            'category_id'   => $catId,
            'status'        => 'Active',
            'adminresponse' => 'Approved',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Seed Paint Product Family
        $midPaint = DB::table('main_categories')->where('name', 'paints')->value('id') ?? $mainCatId;
        $cidPaint = DB::table('categories')->where('name', 'Enamel Paints')->value('id') ?? $catId;

        $paintProductId = DB::table('products')->insertGetId([
            'maincategory_id' => $midPaint,
            'category_id'     => $cidPaint,
            'subcategory_id'  => 0,
            'name'            => 'Apcolite Premium Enamel',
            'slug'            => 'apcolite-premium-enamel',
            'title'           => 'Apcolite Premium Gloss Enamel Paint',
            'description'     => 'High-gloss solvent-based paint with superior durability for wood and metal surfaces.',
            'image'           => 'default.png',
            'multipleimages'  => json_encode(['default.png']),
            'status'          => '1',
            'brand_id'        => DB::table('brands')->value('id') ?? 1,
            'product_id'      => 101,
            'user_id'         => $userId,
            'hsncode'         => '3208',
            'tax'             => 18,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Get paint packings and shades
        $pack1L = DB::table('box_packings')->where('name', '1 Litre')->value('id') ?? 1;
        $pack4L = DB::table('box_packings')->where('name', '4 Litres')->value('id') ?? 2;
        $pack10L = DB::table('box_packings')->where('name', '10 Litres')->value('id') ?? 3;
        $pack20L = DB::table('box_packings')->where('name', '20 Litres')->value('id') ?? 4;

        $shdWhite = DB::table('shade_cards')->where('name', 'Super White')->value('id') ?? 1;
        $shdBlue = DB::table('shade_cards')->where('name', 'Royal Blue')->value('id') ?? 2;
        $shdOrange = DB::table('shade_cards')->where('name', 'Sunset Orange')->value('id') ?? 3;
        $shdGreen = DB::table('shade_cards')->where('name', 'Emerald Green')->value('id') ?? 4;

        // Pricing Matrix from specification:
        // White:  1L=450, 4L=1700, 10L=4100, 20L=7900
        // Blue:   1L=500, 4L=1900, 10L=4600, 20L=8900
        // Orange: 1L=480, 4L=1820, 10L=4400, 20L=8500
        // Green:  1L=470, 4L=1780, 10L=4300, 20L=8300
        $matrix = [
            ['color' => $shdWhite, 'pck' => $pack1L,  'litres' => 1.0,  'seller' => 400.00, 'cust' => 500.00],
            ['color' => $shdWhite, 'pck' => $pack4L,  'litres' => 4.0,  'seller' => 1600.00, 'cust' => 2000.00],
            ['color' => $shdWhite, 'pck' => $pack10L, 'litres' => 10.0, 'seller' => 3800.00, 'cust' => 4750.00],
            ['color' => $shdWhite, 'pck' => $pack20L, 'litres' => 20.0, 'seller' => 7400.00, 'cust' => 9250.00],

            ['color' => $shdBlue,  'pck' => $pack1L,  'litres' => 1.0,  'seller' => 450.00, 'cust' => 562.50],
            ['color' => $shdBlue,  'pck' => $pack4L,  'litres' => 4.0,  'seller' => 1800.00, 'cust' => 2250.00],
            ['color' => $shdBlue,  'pck' => $pack10L, 'litres' => 10.0, 'seller' => 4300.00, 'cust' => 5375.00],
            ['color' => $shdBlue,  'pck' => $pack20L, 'litres' => 20.0, 'seller' => 8400.00, 'cust' => 10500.00],

            ['color' => $shdOrange, 'pck' => $pack1L,  'litres' => 1.0,  'seller' => 420.00, 'cust' => 525.00],
            ['color' => $shdOrange, 'pck' => $pack4L,  'litres' => 4.0,  'seller' => 1680.00, 'cust' => 2100.00],
            ['color' => $shdOrange, 'pck' => $pack10L, 'litres' => 10.0, 'seller' => 4000.00, 'cust' => 5000.00],
            ['color' => $shdOrange, 'pck' => $pack20L, 'litres' => 20.0, 'seller' => 7800.00, 'cust' => 9750.00],

            ['color' => $shdGreen,  'pck' => $pack1L,  'litres' => 1.0,  'seller' => 410.00, 'cust' => 512.50],
            ['color' => $shdGreen,  'pck' => $pack4L,  'litres' => 4.0,  'seller' => 1640.00, 'cust' => 2050.00],
            ['color' => $shdGreen,  'pck' => $pack10L, 'litres' => 10.0, 'seller' => 3900.00, 'cust' => 4875.00],
            ['color' => $shdGreen,  'pck' => $pack20L, 'litres' => 20.0, 'seller' => 7600.00, 'cust' => 9500.00],
        ];

        foreach ($matrix as $row) {
            DB::table('product_attributes')->insert([
                'product_id'      => $paintProductId,
                'color'           => $row['color'],
                'quantity'        => $row['pck'],
                'pack_litres'     => $row['litres'],
                'seller_price'    => $row['seller'],
                'commission_rate' => 25.00,
                'oldprice'        => $row['cust'],
                'price'           => $row['cust'],
                'status'          => 'active',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // Seed Sample Order & Transactions
        $orderNo = 'AF' . now()->format('Ymd') . '-001';
        $orderId = DB::table('orders')->insertGetId([
            'orderno'            => $orderNo,
            'customer_id'        => $customerId,
            'user_id'            => $userId,
            'address'            => '123 Test Industrial Area, New Delhi, 110001',
            'sellercouponamount' => 0,
            'admincouponamount'  => 0,
            'netamount'          => 2500.00,
            'taxdetail'          => json_encode([['name' => 'GST @18%', 'value' => 450]]),
            'taxamount'          => 450.00,
            'grandtotal'         => 2950.00,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // Seed Order Status
        DB::table('order_status')->insert([
            'order_id'   => $orderId,
            'order_no'   => $orderNo,
            'user_id'    => $userId,
            'status'     => 'pending',
            'msg'        => 'Order placed successfully',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed Transection
        DB::table('transections')->insert([
            'order_id'    => $orderId,
            'order_no'    => $orderNo,
            'customer_id' => $customerId,
            'user_id'     => $userId,
            'status'      => 'success',
            'txnid'       => 'TXN_' . uniqid(),
            'txndetail'   => 'Paid via UPI Test Gateway',
            'txnresponse' => 'SUCCESS',
            'txnmethod'   => 'UPI',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Seed Order Detail
        DB::table('orderdetail')->insert([
            'order_id'     => $orderId,
            'orderno'      => $orderNo,
            'customer_id'  => $customerId,
            'product_id'   => 1,
            'productname'  => 'Pure Cotton Fabric',
            'hsn'          => '5208',
            'brdcmpcat'    => 'FactoryStandard / ApniFactory Prime Mills / Fabrics',
            'attribute'    => json_encode([['color' => 'White', 'qty' => 10, 'price' => 250]]),
            'productimage' => 'default.png',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Seed Wallet
        DB::table('wallet')->insert([
            'user_id'    => $userId,
            'order_id'   => $orderId,
            'orderno'    => $orderNo,
            'value'      => 2500.00,
            'commission' => 125.00,
            'debit'      => 0,
            'credit'     => 2375.00,
            'balance'    => 2375.00,
            'amount'     => 2375.00,
            'type'       => 'credit',
            'action'     => 'credit',
            'addby'      => 'system',
            'msg'        => 'Order credit for ' . $orderNo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed Ticket
        DB::table('tickets')->insert([
            'topic'      => 'Inquiry about bulk yarn order',
            'msg'        => 'Looking for 500 meters of pure linen fabric in custom beige shade.',
            'adminmsg'   => 'Sample sent for review.',
            'user_id'    => $userId,
            'status'     => 'Pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
