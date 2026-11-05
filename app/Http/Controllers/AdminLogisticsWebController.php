<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SellerFulfilment;
use App\Models\User;
use App\Services\SellerFulfilmentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLogisticsWebController extends Controller
{
    public function __construct(
        protected SellerFulfilmentService $fulfilmentService
    ) {
    }

    /**
     * Display central logistics and multi-seller dispatch monitoring queue.
     */
    public function index(Request $request): View
    {
        $query = SellerFulfilment::with(['order', 'seller', 'customer', 'company'])
            ->latest('id');

        // Status Filter
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'pending') {
                $query->whereIn('fulfilment_status', [
                    SellerFulfilment::STATUS_PENDING_ACCEPTANCE,
                    SellerFulfilment::STATUS_ACCEPTED,
                    SellerFulfilment::STATUS_PROCESSING_PACKED,
                ]);
            } elseif ($status === 'disputed') {
                $query->whereNotNull('dispute_status');
            } else {
                $query->where('fulfilment_status', $status);
            }
        }

        // Search Filter (orderno, fulfilment_ref, lr_number, transporter_name)
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('fulfilment_ref', 'like', "%{$term}%")
                  ->orWhere('lr_number', 'like', "%{$term}%")
                  ->orWhere('transporter_name', 'like', "%{$term}%")
                  ->orWhereHas('order', function ($oq) use ($term) {
                      $oq->where('orderno', 'like', "%{$term}%");
                  });
            });
        }

        // Seller Filter
        if ($request->filled('seller_id')) {
            $query->where('seller_user_id', (int)$request->input('seller_id'));
        }

        $fulfilments = $query->paginate(15)->withQueryString();

        // Calculate KPI Metrics
        $metrics = [
            'total'         => SellerFulfilment::count(),
            'pending'       => SellerFulfilment::whereIn('fulfilment_status', [
                SellerFulfilment::STATUS_PENDING_ACCEPTANCE,
                SellerFulfilment::STATUS_ACCEPTED,
                SellerFulfilment::STATUS_PROCESSING_PACKED,
            ])->count(),
            'in_transit'    => SellerFulfilment::where('fulfilment_status', SellerFulfilment::STATUS_DISPATCHED)->count(),
            'acknowledged'  => SellerFulfilment::whereIn('fulfilment_status', [
                SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED,
                SellerFulfilment::STATUS_DELIVERED,
            ])->count(),
            'disputes'      => SellerFulfilment::whereNotNull('dispute_status')->count(),
        ];

        $sellers = User::select('id', 'name', 'email')->get();

        return view('admin.logistics.index', compact('fulfilments', 'metrics', 'sellers'));
    }

    /**
     * Show fulfilment details and document inspection.
     */
    public function show(int $id): View
    {
        $fulfilment = SellerFulfilment::with(['order.orderdetails', 'seller', 'customer', 'company', 'history' => function ($q) {
            $q->latest('id');
        }])->findOrFail($id);

        return view('admin.logistics.show', compact('fulfilment'));
    }
}
