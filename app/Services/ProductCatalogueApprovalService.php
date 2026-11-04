<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\Company;
use App\Models\CompanyAuditLog;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductReviewHistory;
use App\Models\ProductValidationResult;
use App\Models\SellerListing;
use App\Models\SubCatgory;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProductCatalogueApprovalService
{
    public function __construct(private SellerPermissionService $permissionService)
    {
    }

    /**
     * Run automatic catalogue validation on a product and its hierarchy.
     */
    public function validateProduct(Product $product, Company $company, ?User $user = null): ProductValidationResult
    {
        $passedChecks = [];
        $failedChecks = [];
        $warnings     = [];

        // 1. Product Core Information Checks
        if (!empty(trim($product->name))) {
            $passedChecks[] = 'Product name is defined.';
        } else {
            $failedChecks[] = [
                'rule'        => 'product.name.required',
                'field'       => 'name',
                'message'     => 'Product name is mandatory.',
                'entity_type' => 'product',
                'entity_id'   => $product->id,
                'is_blocking' => true,
            ];
        }

        if (!empty(trim($product->title ?? '')) || !empty(trim($product->description ?? ''))) {
            $passedChecks[] = 'Product description / title is provided.';
        } else {
            $warnings[] = [
                'rule'        => 'product.description.recommended',
                'field'       => 'description',
                'message'     => 'Product title or description is missing; providing details improves marketplace conversion.',
                'entity_type' => 'product',
                'entity_id'   => $product->id,
            ];
        }

        // 2. Category & Subcategory Checks
        $category = Category::find($product->category_id);
        if ($category && in_array(strtolower($category->status ?? ''), ['active', '1'])) {
            $passedChecks[] = "Valid category [{$category->name}] assigned.";
        } else {
            $failedChecks[] = [
                'rule'        => 'product.category.valid',
                'field'       => 'category_id',
                'message'     => 'Assigned category does not exist or is inactive.',
                'entity_type' => 'product',
                'entity_id'   => $product->id,
                'is_blocking' => true,
            ];
        }

        if (!empty($product->subcategory_id)) {
            $subcategory = SubCatgory::find($product->subcategory_id);
            if ($subcategory && ($subcategory->category_id == $product->category_id || $subcategory->cid == $product->category_id)) {
                $passedChecks[] = "Valid subcategory [{$subcategory->name}] assigned.";
            } else {
                $failedChecks[] = [
                    'rule'        => 'product.subcategory.mismatch',
                    'field'       => 'subcategory_id',
                    'message'     => 'Assigned subcategory does not belong to the selected category.',
                    'entity_type' => 'product',
                    'entity_id'   => $product->id,
                    'is_blocking' => true,
                ];
            }
        }

        // 3. Brand Checks
        $brand = Brand::find($product->brand_id);
        if ($brand && in_array(strtolower($brand->status ?? ''), ['active', '1'])) {
            $passedChecks[] = "Valid brand [{$brand->name}] assigned.";
        } else {
            $failedChecks[] = [
                'rule'        => 'product.brand.valid',
                'field'       => 'brand_id',
                'message'     => 'Assigned brand is inactive or not found.',
                'entity_type' => 'product',
                'entity_id'   => $product->id,
                'is_blocking' => true,
            ];
        }

        // 4. Product Media Checks
        if (!empty($product->image) || !empty($product->multipleimages)) {
            $passedChecks[] = 'Product media images attached.';
        } else {
            $failedChecks[] = [
                'rule'        => 'product.media.required',
                'field'       => 'image',
                'message'     => 'At least one product image is required for marketplace publication.',
                'entity_type' => 'product',
                'entity_id'   => $product->id,
                'is_blocking' => true,
            ];
        }

        // 5. Dynamic Attributes Checks
        $this->validateDynamicAttributes($product, $passedChecks, $failedChecks, $warnings);

        // 6. Variants & Listings Checks
        $this->validateVariantsAndListings($product, $company, $passedChecks, $failedChecks, $warnings);

        // 7. Determine Overall Validation Status
        $blockingErrors = array_filter($failedChecks, fn($f) => !empty($f['is_blocking']));
        $status = empty($blockingErrors)
            ? (empty($warnings) ? 'passed' : 'passed_with_warnings')
            : 'failed';

        // 8. Persist Validation Result Record
        $result = ProductValidationResult::create([
            'product_id'    => $product->id,
            'company_id'    => $company->id,
            'user_id'       => $user?->id ?: $company->user_id,
            'status'        => $status,
            'passed_checks' => $passedChecks,
            'failed_checks' => $failedChecks,
            'warnings'      => $warnings,
            'error_count'   => count($blockingErrors),
            'warning_count' => count($warnings),
        ]);

        $product->update(['last_validated_at' => now()]);

        return $result;
    }

    /**
     * Validate dynamic category attributes against configured mappings.
     */
    protected function validateDynamicAttributes(
        Product $product,
        array &$passedChecks,
        array &$failedChecks,
        array &$warnings
    ): void {
        $requiredMappings = CategoryAttribute::where(function ($q) use ($product) {
            $q->where('category_id', $product->category_id);
            if ($product->subcategory_id) {
                $q->orWhere('category_id', $product->subcategory_id);
            }
        })
        ->where('is_required', true)
        ->with('attribute.options')
        ->get();

        $attrValues = $product->attributeValues()->with('attribute.options', 'option')->get();
        $attrMap = $attrValues->keyBy('attribute_id');

        foreach ($requiredMappings as $mapping) {
            $attr = $mapping->attribute;
            if (!$attr || !in_array($attr->status, ['Active', '1', 1])) {
                continue;
            }

            $valRecord = $attrMap->get($attr->id);
            $hasVal = $valRecord && (
                !is_null($valRecord->attribute_option_id) ||
                !empty($valRecord->text_value) ||
                !is_null($valRecord->number_value) ||
                !is_null($valRecord->boolean_value)
            );

            if ($hasVal) {
                // If select attribute, check option validity
                if (in_array($attr->type, ['select', 'single_select', 'multi_select', 'multi-select']) && $valRecord->attribute_option_id) {
                    $optExists = \App\Models\AttributeOption::where('attribute_id', $attr->id)
                        ->where('id', $valRecord->attribute_option_id)
                        ->exists();
                    if ($optExists) {
                        $passedChecks[] = "Dynamic attribute [{$attr->name}] configured with valid option.";
                    } else {
                        $failedChecks[] = [
                            'rule'        => 'attribute.option.invalid',
                            'field'       => "dynamic_attribute.{$attr->code}",
                            'message'     => "Selected option for required attribute [{$attr->name}] is not valid.",
                            'entity_type' => 'attribute',
                            'entity_id'   => $attr->id,
                            'is_blocking' => true,
                        ];
                    }
                } else {
                    $passedChecks[] = "Dynamic attribute [{$attr->name}] provided.";
                }
            } else {
                $failedChecks[] = [
                    'rule'        => 'attribute.required.missing',
                    'field'       => "dynamic_attribute.{$attr->code}",
                    'message'     => "Required category attribute [{$attr->name}] has no value assigned.",
                    'entity_type' => 'attribute',
                    'entity_id'   => $attr->id,
                    'is_blocking' => true,
                ];
            }
        }
    }

    /**
     * Validate product variants and associated commercial seller listings.
     */
    protected function validateVariantsAndListings(
        Product $product,
        Company $company,
        array &$passedChecks,
        array &$failedChecks,
        array &$warnings
    ): void {
        $variants = $product->variants()->get();

        if ($variants->isEmpty()) {
            $failedChecks[] = [
                'rule'        => 'product.variants.none',
                'field'       => 'variants',
                'message'     => 'Product must have at least one variant configuration.',
                'entity_type' => 'product',
                'entity_id'   => $product->id,
                'is_blocking' => true,
            ];
            return;
        }

        $passedChecks[] = "Product has {$variants->count()} variant(s) defined.";

        $listings = SellerListing::where('company_id', $company->id)
            ->where('product_id', $product->id)
            ->get();

        if ($listings->isEmpty()) {
            $failedChecks[] = [
                'rule'        => 'product.listings.none',
                'field'       => 'listings',
                'message'     => 'Product has no sellable seller SKU listings associated with your company.',
                'entity_type' => 'product',
                'entity_id'   => $product->id,
                'is_blocking' => true,
            ];
            return;
        }

        $passedChecks[] = "Product has {$listings->count()} seller SKU listing(s).";

        // Check each listing for valid pricing, MOQ, and unique SKU
        foreach ($listings as $listing) {
            $sku = $listing->seller_sku ?: "SKU-{$listing->id}";

            // SKU format
            if (empty(trim($listing->seller_sku ?? ''))) {
                $warnings[] = [
                    'rule'        => 'listing.sku.empty',
                    'field'       => 'seller_sku',
                    'message'     => "Listing #{$listing->id} has no explicit seller SKU specified.",
                    'entity_type' => 'listing',
                    'entity_id'   => $listing->id,
                ];
            }

            // Pricing check
            if ((float)$listing->seller_price <= 0 || (float)$listing->selling_price <= 0) {
                $failedChecks[] = [
                    'rule'        => 'listing.pricing.zero',
                    'field'       => 'seller_price',
                    'message'     => "Listing [{$sku}] has price of ₹0.00. Selling price must be strictly greater than zero.",
                    'entity_type' => 'listing',
                    'entity_id'   => $listing->id,
                    'is_blocking' => true,
                ];
            } else {
                $passedChecks[] = "Listing [{$sku}] has valid pricing (₹{$listing->selling_price}).";
            }

            // MRP ceiling check
            if ((float)$listing->mrp > 0 && (float)$listing->mrp < (float)$listing->seller_price) {
                $failedChecks[] = [
                    'rule'        => 'listing.pricing.mrp_low',
                    'field'       => 'mrp',
                    'message'     => "Listing [{$sku}] MRP (₹{$listing->mrp}) cannot be lower than seller wholesale price (₹{$listing->seller_price}).",
                    'entity_type' => 'listing',
                    'entity_id'   => $listing->id,
                    'is_blocking' => true,
                ];
            }

            // MOQ check
            if ((int)$listing->min_order_quantity <= 0) {
                $failedChecks[] = [
                    'rule'        => 'listing.moq.invalid',
                    'field'       => 'min_order_quantity',
                    'message'     => "Listing [{$sku}] Minimum Order Quantity (MOQ) must be at least 1.",
                    'entity_type' => 'listing',
                    'entity_id'   => $listing->id,
                    'is_blocking' => true,
                ];
            }
        }
    }

    /**
     * Submit a valid product for Admin Review (Stage 1 validation -> Stage 2 submission).
     */
    public function submitForReview(Product $product, Company $company, User $user, ?string $notes = null): array
    {
        // 1. Verify tenant ownership
        $this->ensureProductOwnership($product, $company);

        // 2. Enforce Phase 2.1 permissions
        if (!$user->isAdmin()) {
            if (!$user->hasSellerPermission('products.create_listing') && !$user->hasSellerPermission('products.edit')) {
                abort(403, 'Permission denied: products.create_listing or products.edit is required to submit products for review.');
            }
        }

        // 3. Status transition check
        if (!$product->canBeSubmitted()) {
            throw ValidationException::withMessages([
                'status' => "Product #{$product->id} is currently in [{$product->approval_status}] status and cannot be submitted. Only draft, rejected, or returned products can be submitted.",
            ]);
        }

        // 4. Run automatic catalogue validation
        $validationResult = $this->validateProduct($product, $company, $user);

        if ($validationResult->hasBlockingErrors()) {
            $errorMsgs = collect($validationResult->failed_checks)
                ->where('is_blocking', true)
                ->pluck('message')
                ->all();

            throw ValidationException::withMessages([
                'validation' => $errorMsgs ?: ['Product has blocking validation errors that must be resolved before submission.'],
            ]);
        }

        $oldStatus = $product->approval_status;

        // 5. Update Product to pending_review
        $product->update([
            'approval_status'  => 'pending_review',
            'submitted_at'     => now(),
            'rejection_reason' => null,
            'correction_notes' => null,
        ]);

        // 6. Record in Product Review History
        $history = ProductReviewHistory::create([
            'product_id'           => $product->id,
            'company_id'           => $company->id,
            'seller_user_id'       => $user->id,
            'reviewer_id'          => null,
            'action'               => 'submitted',
            'previous_status'      => $oldStatus,
            'new_status'           => 'pending_review',
            'notes'                => $notes,
            'validation_result_id' => $validationResult->id,
        ]);

        // 7. Send notification to seller
        $this->notifySeller(
            $product->user_id,
            'Product Submitted for Admin Review',
            "Your product '{$product->name}' has been successfully submitted and is now queued for administrative review.",
            'submission_confirmed'
        );

        // 8. Record in Company Audit Log
        CompanyAuditLog::logChange(
            $company->id,
            'catalogue_product_submitted',
            "Product '{$product->name}' submitted for review",
            "Submitted with {$validationResult->warning_count} warnings and 0 blocking errors.",
            ['previous_status' => $oldStatus],
            ['approval_status' => 'pending_review', 'submitted_at' => now()->toIso8601String()],
            $user->id
        );

        return [
            'product_id'           => $product->id,
            'approval_status'      => $product->approval_status,
            'submitted_at'         => $product->submitted_at?->toIso8601String(),
            'validation_result_id' => $validationResult->id,
            'warnings_count'       => $validationResult->warning_count,
            'review_history_id'    => $history->id,
        ];
    }

    /**
     * Resubmit a corrected product for Admin Review.
     */
    public function resubmit(Product $product, Company $company, User $user, ?string $notes = null): array
    {
        $this->ensureProductOwnership($product, $company);

        if (!$user->isAdmin()) {
            if (!$user->hasSellerPermission('products.edit')) {
                abort(403, 'Permission denied: products.edit is required to resubmit corrected products.');
            }
        }

        if (!in_array($product->approval_status, ['corrections_requested', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'status' => "Product #{$product->id} is in [{$product->approval_status}] status and cannot be resubmitted. Resubmission is only allowed for products where corrections were requested or rejected.",
            ]);
        }

        // Automatic validation
        $validationResult = $this->validateProduct($product, $company, $user);

        if ($validationResult->hasBlockingErrors()) {
            $errorMsgs = collect($validationResult->failed_checks)
                ->where('is_blocking', true)
                ->pluck('message')
                ->all();

            throw ValidationException::withMessages([
                'validation' => $errorMsgs ?: ['Corrected product still contains blocking validation errors.'],
            ]);
        }

        $oldStatus = $product->approval_status;

        $product->update([
            'approval_status'  => 'resubmitted',
            'submitted_at'     => now(),
            'rejection_reason' => null,
            'correction_notes' => null,
        ]);

        $history = ProductReviewHistory::create([
            'product_id'           => $product->id,
            'company_id'           => $company->id,
            'seller_user_id'       => $user->id,
            'reviewer_id'          => null,
            'action'               => 'resubmitted',
            'previous_status'      => $oldStatus,
            'new_status'           => 'resubmitted',
            'notes'                => $notes,
            'validation_result_id' => $validationResult->id,
        ]);

        $this->notifySeller(
            $product->user_id,
            'Product Resubmitted for Review',
            "Your corrected product '{$product->name}' has been resubmitted and is awaiting admin re-evaluation.",
            'resubmission_confirmed'
        );

        CompanyAuditLog::logChange(
            $company->id,
            'catalogue_product_resubmitted',
            "Product '{$product->name}' resubmitted for review",
            "Resubmitted after seller corrections with notes: " . ($notes ?: 'No notes provided.'),
            ['previous_status' => $oldStatus],
            ['approval_status' => 'resubmitted', 'submitted_at' => now()->toIso8601String()],
            $user->id
        );

        return [
            'product_id'           => $product->id,
            'approval_status'      => $product->approval_status,
            'submitted_at'         => $product->submitted_at?->toIso8601String(),
            'validation_result_id' => $validationResult->id,
            'review_history_id'    => $history->id,
        ];
    }

    /**
     * Admin Review Queue Query with robust filtering, sorting, and pagination.
     */
    public function getReviewQueue(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::with(['brand', 'category', 'user.company', 'variants', 'listings'])
            ->withCount(['variants', 'listings']);

        // Filter by Review Status
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('approval_status', strtolower(trim($filters['status'])));
        } else {
            // Default queue shows items requiring attention
            if (!isset($filters['status'])) {
                $query->whereIn('approval_status', ['pending_review', 'resubmitted']);
            }
        }

        // Category Filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', (int)$filters['category_id']);
        }

        // Brand Filter
        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', (int)$filters['brand_id']);
        }

        // Seller Company Filter
        if (!empty($filters['company_id'])) {
            $query->whereHas('user.company', fn($q) => $q->where('id', (int)$filters['company_id']));
        }

        // Date Range
        if (!empty($filters['date_from'])) {
            $query->whereDate('submitted_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('submitted_at', '<=', $filters['date_to']);
        }

        // Keyword Search (Product name, title, SKU, seller name)
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', "%{$s}%")
                  ->orWhere('title', 'LIKE', "%{$s}%")
                  ->orWhereHas('user.company', fn($cq) => $cq->where('name', 'LIKE', "%{$s}%"))
                  ->orWhereHas('listings', fn($lq) => $lq->where('seller_sku', 'LIKE', "%{$s}%"));
            });
        }

        // Sorting
        $sortField = $filters['sort_by'] ?? 'submitted_at';
        $sortDir   = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if (in_array($sortField, ['submitted_at', 'created_at', 'id', 'name'], true)) {
            $query->orderBy($sortField, $sortDir);
        } else {
            $query->orderBy('submitted_at', 'desc')->orderBy('id', 'desc');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get comprehensive review inspection details for Admin.
     */
    public function getReviewDetails(Product $product): array
    {
        $product->load([
            'category',
            'subcategory',
            'brand',
            'user.company',
            'variants',
            'listings',
            'attributeValues.attribute.options',
            'attributeValues.option',
            'validationResults' => fn($q) => $q->take(5),
            'reviewHistories.reviewer',
            'reviewHistories.seller',
        ]);

        return [
            'product'                 => $product,
            'company'                 => $product->user?->company,
            'variants'                => $product->variants,
            'listings'                => $product->listings,
            'dynamic_attributes'      => $product->attributeValues,
            'latest_validation'       => $product->validationResults->first(),
            'recent_validations'      => $product->validationResults,
            'review_history'          => $product->reviewHistories,
        ];
    }

    /**
     * Admin Action: Approve Product and publish to marketplace.
     */
    public function approveProduct(Product $product, User $admin, ?string $approvalNotes = null): Product
    {
        $this->ensureAdminPrivilege($admin);

        // Security: Sellers cannot approve products
        if (!$admin->isAdmin()) {
            abort(403, 'Forbidden: Only administrators can approve catalogue products.');
        }

        // Must be in a reviewable state
        if (!in_array($product->approval_status, ['pending_review', 'resubmitted', 'draft'], true)) {
            throw ValidationException::withMessages([
                'approval' => "Product #{$product->id} cannot be approved from its current [{$product->approval_status}] status.",
            ]);
        }

        $company = Company::where('user_id', $product->user_id)->first();
        if (!$company) {
            $company = $admin->resolveSellerCompany() ?: new Company(['id' => 0, 'user_id' => $product->user_id]);
        }

        // Revalidate critical data before approval
        $validationResult = $this->validateProduct($product, $company, $admin);

        if ($validationResult->hasBlockingErrors()) {
            throw ValidationException::withMessages([
                'approval' => 'Product cannot be approved because it currently fails mandatory catalogue validation rules.',
            ]);
        }

        $oldStatus = $product->approval_status;

        DB::transaction(function () use ($product, $admin, $approvalNotes, $oldStatus, $company, $validationResult) {
            // Update product to approved & Active
            $product->update([
                'approval_status'  => 'approved',
                'status'           => 'Active',
                'reviewed_at'      => now(),
                'reviewed_by'      => $admin->id,
                'rejection_reason' => null,
                'correction_notes' => null,
            ]);

            // Activate associated seller listings that have valid pricing
            SellerListing::where('product_id', $product->id)
                ->where('seller_price', '>', 0)
                ->whereIn('status', ['Deactive', 'Inactive', 'Active'])
                ->update(['status' => 'Active']);

            // Record review history
            ProductReviewHistory::create([
                'product_id'           => $product->id,
                'company_id'           => $company->id ?: 0,
                'seller_user_id'       => $product->user_id,
                'reviewer_id'          => $admin->id,
                'action'               => 'approved',
                'previous_status'      => $oldStatus,
                'new_status'           => 'approved',
                'notes'                => $approvalNotes,
                'validation_result_id' => $validationResult->id,
            ]);

            // Log company audit
            if ($company->id) {
                CompanyAuditLog::logChange(
                    $company->id,
                    'catalogue_product_approved',
                    "Product '{$product->name}' approved by administration",
                    "Approved by {$admin->name}. Product and listings are now publicly live on the marketplace.",
                    ['previous_status' => $oldStatus],
                    ['approval_status' => 'approved', 'status' => 'Active', 'reviewed_by' => $admin->id],
                    $admin->id
                );
            }
        });

        // Notify Seller
        $this->notifySeller(
            $product->user_id,
            'Product Approved & Published',
            "Congratulations! Your product '{$product->name}' has been approved by our catalogue administration and is now live on the marketplace.",
            'approval_granted'
        );

        return $product->fresh();
    }

    /**
     * Admin Action: Reject Product with mandatory rejection reason.
     */
    public function rejectProduct(Product $product, User $admin, string $reason): Product
    {
        $this->ensureAdminPrivilege($admin);

        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'rejection_reason' => 'A clear rejection reason is mandatory when rejecting a product.',
            ]);
        }

        $oldStatus = $product->approval_status;
        $company = Company::where('user_id', $product->user_id)->first();

        DB::transaction(function () use ($product, $admin, $reason, $oldStatus, $company) {
            $product->update([
                'approval_status'  => 'rejected',
                'status'           => 'Deactive',
                'reviewed_at'      => now(),
                'reviewed_by'      => $admin->id,
                'rejection_reason' => $reason,
            ]);

            // Deactivate listings
            SellerListing::where('product_id', $product->id)->update(['status' => 'Deactive']);

            ProductReviewHistory::create([
                'product_id'      => $product->id,
                'company_id'      => $company?->id ?: 0,
                'seller_user_id'  => $product->user_id,
                'reviewer_id'     => $admin->id,
                'action'          => 'rejected',
                'previous_status' => $oldStatus,
                'new_status'      => 'rejected',
                'notes'           => $reason,
            ]);

            if ($company) {
                CompanyAuditLog::logChange(
                    $company->id,
                    'catalogue_product_rejected',
                    "Product '{$product->name}' rejected by administration",
                    "Reason: {$reason}",
                    ['previous_status' => $oldStatus],
                    ['approval_status' => 'rejected', 'status' => 'Deactive'],
                    $admin->id
                );
            }
        });

        $this->notifySeller(
            $product->user_id,
            'Product Submission Rejected',
            "Your product '{$product->name}' was rejected by catalogue administration. Reason: {$reason}",
            'rejection_notified'
        );

        return $product->fresh();
    }

    /**
     * Admin Action: Request Corrections from seller with instructions.
     */
    public function requestCorrections(Product $product, User $admin, string $instructions): Product
    {
        $this->ensureAdminPrivilege($admin);

        if (empty(trim($instructions))) {
            throw ValidationException::withMessages([
                'correction_notes' => 'Detailed correction instructions are required when returning a product for edits.',
            ]);
        }

        $oldStatus = $product->approval_status;
        $company = Company::where('user_id', $product->user_id)->first();

        DB::transaction(function () use ($product, $admin, $instructions, $oldStatus, $company) {
            $product->update([
                'approval_status'  => 'corrections_requested',
                'status'           => 'Deactive',
                'reviewed_at'      => now(),
                'reviewed_by'      => $admin->id,
                'correction_notes' => $instructions,
            ]);

            SellerListing::where('product_id', $product->id)->update(['status' => 'Deactive']);

            ProductReviewHistory::create([
                'product_id'      => $product->id,
                'company_id'      => $company?->id ?: 0,
                'seller_user_id'  => $product->user_id,
                'reviewer_id'     => $admin->id,
                'action'          => 'corrections_requested',
                'previous_status' => $oldStatus,
                'new_status'      => 'corrections_requested',
                'notes'           => $instructions,
            ]);

            if ($company) {
                CompanyAuditLog::logChange(
                    $company->id,
                    'catalogue_corrections_requested',
                    "Corrections requested for product '{$product->name}'",
                    "Instructions: {$instructions}",
                    ['previous_status' => $oldStatus],
                    ['approval_status' => 'corrections_requested'],
                    $admin->id
                );
            }
        });

        $this->notifySeller(
            $product->user_id,
            'Product Corrections Requested',
            "Catalogue administration requested corrections for '{$product->name}'. Feedback: {$instructions}. Please update and resubmit.",
            'corrections_requested'
        );

        return $product->fresh();
    }

    // ==========================================
    // INTERNAL HELPERS & SECURITY CHECKS
    // ==========================================

    /**
     * Prevent cross-seller access by checking product ownership.
     */
    public function ensureProductOwnership(Product $product, Company $company): void
    {
        if ($product->user_id != $company->user_id) {
            abort(404, 'Product not found or does not belong to your seller company.');
        }
    }

    /**
     * Enforce admin privilege for moderation actions.
     */
    protected function ensureAdminPrivilege(User $user): void
    {
        if (!$user->isAdmin()) {
            abort(403, 'Forbidden: Administrator privileges required to perform this catalogue review action.');
        }
    }

    /**
     * Send in-app notification to seller using existing notification system.
     */
    public function notifySeller(int $sellerUserId, string $title, string $message, string $type = 'catalogue_approval'): void
    {
        try {
            Notification::create([
                'user_id'  => $sellerUserId,
                'title'    => $title,
                'msg'      => $message,
                'type'     => $type,
                'msgread'  => 0,
            ]);
        } catch (\Throwable $e) {
            Log::warning("Could not dispatch catalogue notification to user #{$sellerUserId}: " . $e->getMessage());
        }
    }
}
