<?php

namespace App\Http\Controllers;

use App\Models\FulfilmentClaim;
use App\Services\BuyerDisputeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerClaimWebController extends Controller
{
    public function __construct(
        protected BuyerDisputeService $disputeService
    ) {
    }

    /**
     * Display list of claims filed against the seller's fulfilment orders.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $sellerUserId = $user ? (int)$user->id : 0;

        $status = $request->input('status');
        $claims = $this->disputeService->getSellerClaims($sellerUserId, $status);

        return view('seller.claims.index', compact('claims'));
    }

    /**
     * Show claim details and seller response form.
     */
    public function show(int $id): View
    {
        $claim = FulfilmentClaim::with(['items', 'evidence', 'history', 'sellerFulfilment.company', 'order'])->findOrFail($id);
        $user = auth()->user();

        // Enforce seller isolation
        if ($user && !str_contains($user->email ?? '', 'admin') && $claim->seller_user_id !== (int)$user->id) {
            abort(403, 'Unauthorized. You can only view claims regarding your own seller orders.');
        }

        unset($claim->admin_notes); // Strip confidential admin notes

        return view('seller.claims.show', compact('claim'));
    }

    /**
     * Submit seller response remarks and counter-evidence.
     */
    public function submitResponse(Request $request, int $id): RedirectResponse
    {
        $claim = FulfilmentClaim::findOrFail($id);
        $user = auth()->user();

        if ($user && !str_contains($user->email ?? '', 'admin') && $claim->seller_user_id !== (int)$user->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'response'   => 'required|string|min:5|max:2000',
            'evidence'   => 'nullable|array|max:5',
            'evidence.*' => 'file|mimes:jpeg,jpg,png,webp,pdf|max:5120',
        ]);

        $sellerUserId = $user ? (int)$user->id : $claim->seller_user_id;
        $sellerName = $user ? ($user->name ?? $user->email) : 'Seller';

        $evidenceFiles = $request->file('evidence') ?? [];
        $this->disputeService->submitSellerResponse(
            $claim,
            $validated['response'],
            $sellerUserId,
            $sellerName,
            $evidenceFiles
        );

        return redirect()->route('seller.claims.show', $claim->id)
            ->with('success', 'Your response and evidence have been recorded. Platform operations and the buyer have been informed.');
    }
}

