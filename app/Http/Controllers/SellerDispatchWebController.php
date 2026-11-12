<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SellerFulfilment;
use App\Services\SellerFulfilmentService;
use App\Traits\WhatsappTraits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerDispatchWebController extends Controller
{
    public function __construct(
        protected SellerFulfilmentService $fulfilmentService
    ) {
    }

    /**
     * Display seller dispatch management screen for an order.
     */
    public function dispatchForm(Request $request, int|string $orderId): View|RedirectResponse
    {
        $order = Order::with(['orderdetails', 'user', 'sellerFulfilment'])->findOrFail($orderId);
        $user = auth()->user();

        // Enforce seller isolation
        if ($user && !str_contains($user->email ?? '', 'admin') && $order->user_id != $user->id) {
            abort(403, 'Unauthorized. You can only manage dispatch for your own seller orders.');
        }

        $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($order);
        $fulfilment->load(['history', 'company', 'customer']);

        return view('seller.orders.dispatch', [
            'order'      => $order,
            'fulfilment' => $fulfilment,
            'history'    => $fulfilment->history()->latest('id')->get(),
        ]);
    }

    /**
     * Submit dispatch information and transition order to dispatched.
     */
    public function submitDispatch(Request $request, int|string $orderId): RedirectResponse
    {
        $order = Order::findOrFail($orderId);
        $user = auth()->user();

        if ($user && !str_contains($user->email ?? '', 'admin') && $order->user_id != $user->id) {
            abort(403, 'Unauthorized. You can only manage dispatch for your own seller orders.');
        }

        $validated = $request->validate([
            'dispatch_origin_name'    => 'required|string|max:255',
            'dispatch_origin_address' => 'required|string',
            'dispatch_origin_city'    => 'nullable|string|max:100',
            'dispatch_origin_state'   => 'nullable|string|max:100',
            'dispatch_origin_pincode' => 'nullable|string|max:16',
            'transporter_name'        => 'required|string|max:255',
            'transporter_contact'     => 'nullable|string|max:64',
            'transporter_tracking_no' => 'nullable|string|max:100',
            'lr_number'               => 'nullable|string|max:100',
            'lr_document'             => 'nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:5120',
            'package_count'           => 'required|integer|min:1',
            'weight_volume'           => 'nullable|string|max:100',
            'vehicle_number'          => 'nullable|string|max:50',
            'dispatched_at'           => 'nullable|date',
            'estimated_delivery_date' => 'nullable|date',
            'notes'                   => 'nullable|string|max:1000',
        ]);

        $actorId = $user ? (int)$user->id : (int)$order->user_id;
        $actorName = $user ? ($user->name ?? $user->email) : 'Seller';

        $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($order);

        // 1. Update Origin snapshot
        $this->fulfilmentService->updateDispatchOrigin($fulfilment, [
            'type'    => 'factory',
            'name'    => $validated['dispatch_origin_name'],
            'address' => $validated['dispatch_origin_address'],
            'city'    => $validated['dispatch_origin_city'] ?? null,
            'state'   => $validated['dispatch_origin_state'] ?? null,
            'pincode' => $validated['dispatch_origin_pincode'] ?? null,
        ], $actorId, $actorName);

        // 2. Upload LR if provided
        if ($request->hasFile('lr_document')) {
            $this->fulfilmentService->uploadLrDocument(
                $fulfilment,
                $request->file('lr_document'),
                $validated['lr_number'] ?? null,
                $actorId,
                $actorName
            );
        }

        // 3. Record Dispatch
        $this->fulfilmentService->recordDispatch($fulfilment, [
            'transporter_name'        => $validated['transporter_name'],
            'transporter_contact'     => $validated['transporter_contact'] ?? null,
            'transporter_tracking_no' => $validated['transporter_tracking_no'] ?? null,
            'lr_number'               => $validated['lr_number'] ?? $fulfilment->lr_number,
            'package_count'           => $validated['package_count'],
            'weight_volume'           => $validated['weight_volume'] ?? null,
            'vehicle_number'          => $validated['vehicle_number'] ?? null,
            'dispatched_at'           => $validated['dispatched_at'] ?? null,
            'estimated_delivery_date' => $validated['estimated_delivery_date'] ?? null,
        ], $actorId, $actorName);

        try {
            $buyerMobile = $order->customer?->mobile ?? null;
            if ($buyerMobile) {
                $lr = $validated['lr_number'] ?? ($fulfilment->lr_number ?? 'Updated');
                WhatsappTraits::sendtobuyerthatorderstatuschangedororderplaced(
                    $buyerMobile,
                    'Dispatched',
                    $order->orderno,
                    "Transporter: {$validated['transporter_name']}, LR: {$lr}"
                );
            }
        } catch (\Throwable $e) {
        }

        return redirect()->route('seller.orders.dispatch', $order->id)
            ->with('success', 'Order marked as Dispatched successfully. Transporter and LR details recorded. Buyer has been notified.');
    }

    /**
     * Upload standalone LR document.
     */
    public function uploadLr(Request $request, int|string $orderId): RedirectResponse
    {
        $order = Order::findOrFail($orderId);
        $user = auth()->user();

        if ($user && !str_contains($user->email ?? '', 'admin') && $order->user_id != $user->id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'lr_document' => 'required|file|mimes:jpeg,jpg,png,webp,pdf|max:5120',
            'lr_number'   => 'nullable|string|max:100',
        ]);

        $actorId = $user ? (int)$user->id : (int)$order->user_id;
        $actorName = $user ? ($user->name ?? $user->email) : 'Seller';

        $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($order);
        $this->fulfilmentService->uploadLrDocument(
            $fulfilment,
            $request->file('lr_document'),
            $request->input('lr_number'),
            $actorId,
            $actorName
        );

        return redirect()->back()->with('success', 'LR / Bilty document uploaded successfully as dispatch evidence.');
    }
}
