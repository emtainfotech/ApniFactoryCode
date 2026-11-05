<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartAttribute;
use App\Services\BulkVariantMatrixService;
use App\Services\BuyerCartValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BuyerCartApiController extends Controller
{
    public function __construct(
        protected BuyerCartValidationService $cartService,
        protected BulkVariantMatrixService $matrixService
    ) {
    }

    /**
     * Resolve buyer/customer ID securely from authentication or request.
     */
    protected function resolveCustomerId(Request $request): int
    {
        $user = $request->user();
        if ($user) {
            return (int)$user->id;
        }

        $paramId = $request->input('customer_id') ?: $request->input('userid');
        if ($paramId && is_numeric($paramId)) {
            return (int)$paramId;
        }

        abort(401, 'Unauthenticated. Buyer identification required.');
    }

    /**
     * GET /api/v2/cart
     * Retrieve the buyer's cart grouped by seller with MOV evaluation.
     */
    public function index(Request $request): JsonResponse
    {
        $customerId = $this->resolveCustomerId($request);
        $cartData = $this->cartService->getGroupedCart($customerId);

        return response()->json($cartData);
    }

    /**
     * POST /api/v2/cart/items
     * Add an item (SKU / SellerListing) to the cart with MOQ and packaging validation.
     */
    public function addItem(Request $request): JsonResponse
    {
        $customerId = $this->resolveCustomerId($request);

        $validated = $request->validate([
            'seller_listing_id' => 'required|integer|exists:seller_listings,id',
            'quantity'          => 'required|integer|min:1',
            'address_id'        => 'nullable|integer',
        ]);

        try {
            $updatedCart = $this->cartService->addItemToCart(
                $customerId,
                (int)$validated['seller_listing_id'],
                (int)$validated['quantity'],
                isset($validated['address_id']) ? (int)$validated['address_id'] : null
            );

            return response()->json([
                'status'  => true,
                'message' => 'Item successfully added to cart.',
                'data'    => $updatedCart,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed for item addition.',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * PUT /api/v2/cart/items/{itemId}
     * Update quantity of an individual cart attribute item with server-side validation.
     */
    public function updateItem(Request $request, int $itemId): JsonResponse
    {
        $customerId = $this->resolveCustomerId($request);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        // IDOR check: verify ownership
        $cartItem = CartAttribute::where('id', $itemId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'status'  => false,
                'message' => 'Cart item not found or does not belong to you.',
            ], 404);
        }

        try {
            $updatedCart = $this->cartService->updateItemQuantity(
                $customerId,
                $itemId,
                (int)$validated['quantity']
            );

            return response()->json([
                'status'  => true,
                'message' => 'Cart item updated successfully.',
                'data'    => $updatedCart,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Quantity update rejected by validation rules.',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * DELETE /api/v2/cart/items/{itemId}
     * Remove an individual item from the cart.
     */
    public function removeItem(Request $request, int $itemId): JsonResponse
    {
        $customerId = $this->resolveCustomerId($request);

        $cartItem = CartAttribute::where('id', $itemId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'status'  => false,
                'message' => 'Cart item not found or does not belong to you.',
            ], 404);
        }

        $updatedCart = $this->cartService->removeItemFromCart($customerId, $itemId);

        return response()->json([
            'status'  => true,
            'message' => 'Item removed from cart.',
            'data'    => $updatedCart,
        ]);
    }

    /**
     * DELETE /api/v2/cart
     * Clear the buyer's entire cart.
     */
    public function clearCart(Request $request): JsonResponse
    {
        $customerId = $this->resolveCustomerId($request);
        $emptyCart = $this->cartService->clearCart($customerId);

        return response()->json([
            'status'  => true,
            'message' => 'Cart cleared successfully.',
            'data'    => $emptyCart,
        ]);
    }

    /**
     * POST /api/v2/cart/revalidate
     * Pre-transaction revalidation service detecting price drift, stock shortages, and MOV compliance.
     */
    public function revalidate(Request $request): JsonResponse
    {
        $customerId = $this->resolveCustomerId($request);
        $revalidationReport = $this->cartService->revalidateCart($customerId);

        return response()->json([
            'status' => true,
            'data'   => $revalidationReport,
        ]);
    }

    /**
     * GET /api/v2/cart/products/{productId}/variant-matrix
     * Retrieve the bulk quantity matrix for compatible variants of a product.
     */
    public function getVariantMatrix(Request $request, int $productId): JsonResponse
    {
        $companyId = $request->query('company_id') ? (int)$request->query('company_id') : null;
        $customerId = $request->user() ? (int)$request->user()->id : null;

        $matrix = $this->matrixService->getVariantMatrixForProduct($productId, $companyId, $customerId);

        return response()->json([
            'status' => true,
            'data'   => $matrix,
        ]);
    }

    /**
     * POST /api/v2/cart/bulk-matrix
     * Submit multiple variant selections simultaneously from the bulk quantity matrix.
     */
    public function submitBulkMatrix(Request $request): JsonResponse
    {
        $customerId = $this->resolveCustomerId($request);

        $validated = $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.seller_listing_id' => 'required|integer|exists:seller_listings,id',
            'items.*.quantity'          => 'required|integer|min:0',
        ]);

        try {
            $result = $this->matrixService->submitBulkMatrix($customerId, $validated['items']);

            if (!$result['success']) {
                return response()->json([
                    'status'     => false,
                    'message'    => $result['message'],
                    'row_errors' => $result['row_errors'],
                ], 422);
            }

            return response()->json([
                'status'  => true,
                'message' => $result['message'],
                'data'    => $result['cart_summary'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Bulk matrix submission failed validation.',
                'errors'  => $e->errors(),
            ], 422);
        }
    }
}
