<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceApiController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {
    }

    /**
     * GET /api/v2/invoices/{id}
     * Retrieve full invoice record with line items.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::with(['items', 'order', 'seller', 'customer', 'company'])->findOrFail($id);

        // Authorization check
        $user = $request->user();
        if ($user && !str_contains($user->email, 'admin')) {
            if ($invoice->seller_user_id !== (int)$user->id && $invoice->customer_id !== (int)$user->id) {
                return response()->json(['status' => false, 'message' => 'Unauthorized access to invoice.'], 403);
            }
        }

        return response()->json([
            'status' => true,
            'data'   => $invoice,
        ]);
    }

    /**
     * GET /api/v2/orders/{orderId}/invoices
     * Retrieve invoices associated with an order.
     */
    public function orderInvoices(Request $request, int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $invoices = Invoice::with('items')
            ->where('order_id', $orderId)
            ->orWhere('parent_order_id', $orderId)
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $invoices,
        ]);
    }

    /**
     * POST /api/v2/orders/{orderId}/generate-invoice
     * Issue approved Goods Tax Invoice for a seller order.
     */
    public function generate(Request $request, int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $invoice = $this->invoiceService->generateGoodsInvoice($order);

        return response()->json([
            'status'  => true,
            'message' => 'Goods Tax Invoice generated successfully.',
            'data'    => $invoice,
        ], 201);
    }
}

