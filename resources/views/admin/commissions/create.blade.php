@extends('layout')
@section('title', 'New Commission Rule')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Administration</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.commissions.rules.index') }}">Commission Rules</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create Rule</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-white"><i class="bx bxs-message-square-x"></i></div>
                    <div class="ms-3 text-white">{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card radius-10">
            <div class="card-header bg-transparent py-3">
                <h6 class="mb-0 font-weight-bold">Configure Commercial Commission Agreement</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.commissions.rules.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Rule Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Standard Epoxy Category Commission (10%)" value="{{ old('name') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Agreement / Contract Ref</label>
                            <input type="text" name="agreement_ref" class="form-control" placeholder="e.g. AGR-2026-ALPHA-01" value="{{ old('agreement_ref') }}">
                        </div>

                        <!-- Scope Type Selection -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Scope Type (Precedence Level) <span class="text-danger">*</span></label>
                            <select name="scope_type" id="scopeTypeSelect" class="form-select" required>
                                <option value="sku" {{ old('scope_type') === 'sku' ? 'selected' : '' }}>SKU-Specific Agreement (Priority 1)</option>
                                <option value="seller" {{ old('scope_type') === 'seller' ? 'selected' : '' }}>Seller/Manufacturer Agreement (Priority 2)</option>
                                <option value="subcategory" {{ old('scope_type') === 'subcategory' ? 'selected' : '' }}>Subcategory Level (Priority 3)</option>
                                <option value="category" {{ old('scope_type') === 'category' ? 'selected' : '' }} selected>Category Level (Priority 4)</option>
                                <option value="global" {{ old('scope_type') === 'global' ? 'selected' : '' }}>Global Marketplace Default (Priority 5)</option>
                            </select>
                        </div>

                        <!-- Dynamic Scope Target Dropdown -->
                        <div class="col-md-6" id="scopeIdContainer">
                            <label class="form-label fw-bold" id="scopeIdLabel">Target Category <span class="text-danger">*</span></label>
                            
                            <!-- Category Select -->
                            <select name="scope_id" id="scopeCategorySelect" class="form-select scope-input">
                                <option value="">Select Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>

                            <!-- Seller Select -->
                            <select name="scope_id" id="scopeSellerSelect" class="form-select scope-input" style="display: none;" disabled>
                                <option value="">Select Seller Organization...</option>
                                @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                @endforeach
                            </select>

                            <!-- Subcategory Select -->
                            <select name="scope_id" id="scopeSubcatSelect" class="form-select scope-input" style="display: none;" disabled>
                                <option value="">Select Subcategory...</option>
                                @foreach($subcategories as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>

                            <!-- SKU Listing Select -->
                            <select name="scope_id" id="scopeSkuSelect" class="form-select scope-input" style="display: none;" disabled>
                                <option value="">Select Specific Listing SKU...</option>
                                @foreach($listings as $list)
                                    <option value="{{ $list->id }}">{{ $list->seller_sku }} - {{ $list->product ? $list->product->name : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Commercial Rates -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Commission Percentage (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">%</span>
                                <input type="number" step="0.01" min="0" max="100" name="commission_percentage" class="form-control" required placeholder="10.00" value="{{ old('commission_percentage', '10.00') }}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Fixed Platform Fee (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" min="0" name="fixed_fee" class="form-control" placeholder="0.00" value="{{ old('fixed_fee', '0.00') }}">
                            </div>
                            <small class="text-muted">Only applicable if explicitly approved in contract.</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Calculation Basis</label>
                            <select name="calculation_basis" class="form-select">
                                <option value="taxable_product_value" selected>Taxable Product Value (Excl. GST)</option>
                                <option value="selling_price_gross">Gross Selling Price (Incl. GST)</option>
                                <option value="net_seller_payable">Net Seller Payable</option>
                            </select>
                        </div>

                        <!-- GST & Tax Treatment -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">GST Rate on Platform Service (%)</label>
                            <div class="input-group">
                                <span class="input-group-text">%</span>
                                <input type="number" step="0.01" min="0" max="100" name="gst_rate" class="form-control" value="18.00">
                            </div>
                            <small class="text-muted">Standard B2B GST on marketplace service fee (18% GST invoice).</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tax Treatment Mode</label>
                            <select name="is_gst_inclusive" class="form-select">
                                <option value="0" selected>GST Exclusive (Service fee + 18% GST added)</option>
                                <option value="1">GST Inclusive (18% GST extracted from fee)</option>
                            </select>
                        </div>

                        <!-- Effective Dates -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Effective From <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="effective_from" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Effective To (Leave empty for open-ended)</label>
                            <input type="datetime-local" name="effective_to" class="form-control" value="{{ old('effective_to') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Remarks & Commercial Terms Notes</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Enter contract rationale or negotiation notes..."></textarea>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <a href="{{ route('admin.commissions.rules.index') }}" class="btn btn-outline-secondary px-4 me-2">Cancel</a>
                            <button type="submit" class="btn btn-success px-5">Create & Activate Rule</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scopeTypeSelect = document.getElementById('scopeTypeSelect');
    const scopeIdContainer = document.getElementById('scopeIdContainer');
    const scopeIdLabel = document.getElementById('scopeIdLabel');
    const categorySelect = document.getElementById('scopeCategorySelect');
    const sellerSelect = document.getElementById('scopeSellerSelect');
    const subcatSelect = document.getElementById('scopeSubcatSelect');
    const skuSelect = document.getElementById('scopeSkuSelect');

    function updateScopeInputs() {
        const type = scopeTypeSelect.value;
        const allInputs = [categorySelect, sellerSelect, subcatSelect, skuSelect];
        allInputs.forEach(input => {
            input.style.display = 'none';
            input.disabled = true;
        });

        if (type === 'global') {
            scopeIdContainer.style.display = 'none';
        } else {
            scopeIdContainer.style.display = 'block';
            if (type === 'category') {
                scopeIdLabel.innerHTML = 'Target Category <span class="text-danger">*</span>';
                categorySelect.style.display = 'block';
                categorySelect.disabled = false;
            } else if (type === 'seller') {
                scopeIdLabel.innerHTML = 'Target Seller Organization <span class="text-danger">*</span>';
                sellerSelect.style.display = 'block';
                sellerSelect.disabled = false;
            } else if (type === 'subcategory') {
                scopeIdLabel.innerHTML = 'Target Subcategory <span class="text-danger">*</span>';
                subcatSelect.style.display = 'block';
                subcatSelect.disabled = false;
            } else if (type === 'sku') {
                scopeIdLabel.innerHTML = 'Target Listing SKU <span class="text-danger">*</span>';
                skuSelect.style.display = 'block';
                skuSelect.disabled = false;
            }
        }
    }

    scopeTypeSelect.addEventListener('change', updateScopeInputs);
    updateScopeInputs();
});
</script>
@endsection
