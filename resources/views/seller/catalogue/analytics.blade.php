@extends('layout')
@section('title', 'Catalogue Analytics & Health Dashboard')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Seller Portal</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ url('seller/dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('product.list') }}">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Catalogue Analytics</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('seller.catalogue.submissions') }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-clipboard-check me-1"></i> Approval Submissions
                </a>
                <a href="{{ route('seller.catalogue.bulk') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-list-check me-1"></i> Bulk Manager
                </a>
                <a href="{{ route('seller.catalogue.export') }}" class="btn btn-outline-success">
                    <i class="fa-solid fa-file-arrow-down me-1"></i> Export Catalogue
                </a>
            </div>
        </div>
        <!-- End Breadcrumb -->

        <!-- Filters & Date Range Bar -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <form method="GET" action="{{ route('seller.catalogue.analytics') }}" id="analyticsFilterForm" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-1">
                            <i class="fa-solid fa-calendar-days me-1 text-primary"></i> Reporting Period
                        </label>
                        <select name="period" id="periodSelect" class="form-select">
                            <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="last_7_days" {{ $period === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="last_30_days" {{ $period === 'last_30_days' ? 'selected' : '' }}>Last 30 Days (Default)</option>
                            <option value="last_90_days" {{ $period === 'last_90_days' ? 'selected' : '' }}>Last 90 Days</option>
                            <option value="all_time" {{ $period === 'all_time' ? 'selected' : '' }}>All Time</option>
                            <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                        </select>
                    </div>

                    <div class="col-md-3 custom-date-col" style="{{ $period === 'custom' ? '' : 'display: none;' }}">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-1">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>

                    <div class="col-md-3 custom-date-col" style="{{ $period === 'custom' ? '' : 'display: none;' }}">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-1">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-filter me-1"></i> Apply Filter
                        </button>
                        @if($period !== 'last_30_days' || $dateFrom || $dateTo)
                            <a href="{{ route('seller.catalogue.analytics') }}" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-rotate-left me-1"></i> Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        @php
            $summary = $analytics['summary'] ?? [];
            $distribution = $analytics['status_distribution'] ?? [];
            $health = $analytics['catalogue_health'] ?? [];
            $approval = $analytics['approval_performance'] ?? [];
            $commercial = $analytics['product_performance'] ?? [];
            $categories = $analytics['category_insights'] ?? [];
            $brands = $analytics['brand_insights'] ?? [];
            $healthScore = $health['health_score'] ?? 100;
        @endphp

        <!-- 1. KPI Overview Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-primary shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary fw-semibold">Total Products</p>
                                <h4 class="my-1 text-primary">{{ number_format($summary['total_products'] ?? 0) }}</h4>
                                <p class="mb-0 font-13 text-muted">
                                    <span class="text-success fw-bold">{{ $summary['active_products'] ?? 0 }} Active</span> | 
                                    <span class="text-secondary">{{ $summary['inactive_products'] ?? 0 }} Inactive</span>
                                </p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-success shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary fw-semibold">Live on Marketplace</p>
                                <h4 class="my-1 text-success">{{ number_format($summary['live_marketplace_products'] ?? 0) }}</h4>
                                <p class="mb-0 font-13 text-muted">Active & Approved Products</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary fw-semibold">Variants & Listings</p>
                                <h4 class="my-1 text-info">{{ number_format($summary['total_variants'] ?? 0) }} <span class="fs-6 text-muted">Variants</span></h4>
                                <p class="mb-0 font-13 text-muted">
                                    <span class="fw-semibold text-dark">{{ $summary['total_seller_skus'] ?? 0 }}</span> SKUs ({{ $summary['active_seller_skus'] ?? 0 }} Active)
                                </p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                                <i class="fa-solid fa-barcode"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-warning shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary fw-semibold">Pending / In Review</p>
                                <h4 class="my-1 text-warning">{{ number_format($summary['pending_approval_products'] ?? 0) }}</h4>
                                <p class="mb-0 font-13 text-muted">
                                    <span class="text-danger fw-semibold">{{ $summary['corrections_required_products'] ?? 0 }} Need Fixes</span>
                                </p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Catalogue Health & Completeness Section -->
        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center p-4">
                        <h6 class="card-title fw-bold text-uppercase text-muted mb-3">Catalogue Health Score</h6>
                        
                        <div class="position-relative d-inline-block my-2">
                            <div class="health-score-circle mx-auto d-flex align-items-center justify-content-center rounded-circle 
                                {{ $healthScore >= 80 ? 'bg-light-success text-success border border-success border-3' : ($healthScore >= 50 ? 'bg-light-warning text-warning border border-warning border-3' : 'bg-light-danger text-danger border border-danger border-3') }}"
                                style="width: 140px; height: 140px;">
                                <div>
                                    <h2 class="mb-0 fw-bold">{{ $healthScore }}%</h2>
                                    <span class="small fw-semibold text-uppercase">
                                        {{ $healthScore >= 80 ? 'Optimal' : ($healthScore >= 50 ? 'Fair' : 'Critical') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted small mt-3 mb-3">
                            Assessed across <strong>{{ $health['total_assessed'] ?? 0 }}</strong> products covering media assets, dynamic mandatory attributes, descriptions, and validation checks.
                        </p>

                        <div class="progress mb-2" style="height: 10px;">
                            <div class="progress-bar {{ $healthScore >= 80 ? 'bg-success' : ($healthScore >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                 role="progressbar" style="width: {{ $healthScore }}%;" 
                                 aria-valuenow="{{ $healthScore }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <div class="d-flex justify-content-between text-muted small">
                            <span>0% (Poor)</span>
                            <span>50% (Acceptable)</span>
                            <span>100% (Perfect)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-lg-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-shield-halved me-2 text-primary"></i>Completeness & Diagnostic Breakdown</h6>
                            <div class="ms-auto">
                                @if(($health['needs_attention_count'] ?? 0) > 0)
                                    <span class="badge bg-danger rounded-pill px-3 py-1">
                                        {{ $health['needs_attention_count'] }} Issues Detected
                                    </span>
                                @else
                                    <span class="badge bg-success rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> All Healthy
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small text-uppercase">
                                        <th>Diagnostic Metric</th>
                                        <th class="text-center">Count</th>
                                        <th>Status / Impact</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <i class="fa-solid fa-image me-2 text-secondary"></i>
                                            <span>Missing Primary / Multiple Images</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold {{ ($health['missing_images_count'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $health['missing_images_count'] ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(($health['missing_images_count'] ?? 0) > 0)
                                                <span class="badge bg-light-danger text-danger">Blocks Approval</span>
                                            @else
                                                <span class="badge bg-light-success text-success">Compliant</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('seller.catalogue.submissions', ['status' => 'all']) }}" class="btn btn-sm btn-link text-decoration-none">Review</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="fa-solid fa-align-left me-2 text-secondary"></i>
                                            <span>Missing Title or Detailed Description</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold {{ ($health['missing_descriptions_count'] ?? 0) > 0 ? 'text-warning' : 'text-success' }}">
                                                {{ $health['missing_descriptions_count'] ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(($health['missing_descriptions_count'] ?? 0) > 0)
                                                <span class="badge bg-light-warning text-warning">Poor Search Rank</span>
                                            @else
                                                <span class="badge bg-light-success text-success">Compliant</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('seller.catalogue.submissions', ['status' => 'all']) }}" class="btn btn-sm btn-link text-decoration-none">Review</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="fa-solid fa-tags me-2 text-secondary"></i>
                                            <span>Missing Required Category Attributes</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold {{ ($health['missing_required_attributes_count'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $health['missing_required_attributes_count'] ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(($health['missing_required_attributes_count'] ?? 0) > 0)
                                                <span class="badge bg-light-danger text-danger">Blocks Submission</span>
                                            @else
                                                <span class="badge bg-light-success text-success">Compliant</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('seller.catalogue.submissions', ['status' => 'draft']) }}" class="btn btn-sm btn-link text-decoration-none">Review</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="fa-solid fa-triangle-exclamation me-2 text-secondary"></i>
                                            <span>Active Validation Errors</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold {{ ($health['products_with_validation_errors'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $health['products_with_validation_errors'] ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(($health['products_with_validation_errors'] ?? 0) > 0)
                                                <span class="badge bg-light-danger text-danger">Requires Fix</span>
                                            @else
                                                <span class="badge bg-light-success text-success">0 Errors</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('seller.catalogue.submissions', ['status' => 'all']) }}" class="btn btn-sm btn-link text-decoration-none">Review</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="fa-solid fa-clipboard-question me-2 text-secondary"></i>
                                            <span>Products Awaiting Corrections</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold {{ ($health['products_awaiting_corrections'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $health['products_awaiting_corrections'] ?? 0 }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(($health['products_awaiting_corrections'] ?? 0) > 0)
                                                <span class="badge bg-light-danger text-danger">Action Required</span>
                                            @else
                                                <span class="badge bg-light-success text-success">None Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('seller.catalogue.submissions', ['status' => 'corrections_requested']) }}" class="btn btn-sm btn-danger px-2 py-0 text-white">Fix Now</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Workflow & Approval Analytics + Status Distribution -->
        <div class="row g-3 mb-4">
            <!-- Approval Turnaround & Flow -->
            <div class="col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-hourglass-half me-2 text-primary"></i>Approval Performance & Turnaround</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 text-center mb-3">
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light">
                                    <small class="text-muted d-block">Avg Turnaround</small>
                                    <h5 class="my-1 text-primary fw-bold">{{ $approval['average_turnaround_formatted'] ?? 'N/A' }}</h5>
                                    <span class="badge bg-light-secondary text-secondary small">Submitted to Decision</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light">
                                    <small class="text-muted d-block">Awaiting Review</small>
                                    <h5 class="my-1 text-warning fw-bold">{{ $approval['products_awaiting_review'] ?? 0 }}</h5>
                                    <span class="badge bg-light-warning text-warning small">In Admin Queue</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light">
                                    <small class="text-muted d-block">Approved / Rejected</small>
                                    <h5 class="my-1 text-dark fw-bold">
                                        <span class="text-success">{{ $approval['products_approved'] ?? 0 }}</span> / 
                                        <span class="text-danger">{{ $approval['products_rejected'] ?? 0 }}</span>
                                    </h5>
                                    <span class="badge bg-light-info text-info small">In Selected Period</span>
                                </div>
                            </div>
                        </div>

                        <!-- Daily Trend Chart -->
                        <div style="height: 220px; position: relative;">
                            <canvas id="approvalTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catalogue Status & Distribution -->
            <div class="col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Catalogue Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6 text-center">
                                <div style="height: 200px; max-width: 200px; margin: 0 auto; position: relative;">
                                    <canvas id="statusDistributionChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1">
                                        <span><i class="fa-solid fa-circle text-success me-2"></i>Approved</span>
                                        <span class="fw-bold">{{ $summary['approved_products'] ?? 0 }} ({{ $distribution['approval_percentages']['approved'] ?? 0 }}%)</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1">
                                        <span><i class="fa-solid fa-circle text-warning me-2"></i>Pending Review</span>
                                        <span class="fw-bold">{{ $summary['pending_approval_products'] ?? 0 }} ({{ $distribution['approval_percentages']['pending_review'] ?? 0 }}%)</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1">
                                        <span><i class="fa-solid fa-circle text-secondary me-2"></i>Draft</span>
                                        <span class="fw-bold">{{ $summary['draft_products'] ?? 0 }} ({{ $distribution['approval_percentages']['draft'] ?? 0 }}%)</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1">
                                        <span><i class="fa-solid fa-circle text-info me-2"></i>Corrections Needed</span>
                                        <span class="fw-bold">{{ $summary['corrections_required_products'] ?? 0 }} ({{ $distribution['approval_percentages']['corrections_requested'] ?? 0 }}%)</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-1">
                                        <span><i class="fa-solid fa-circle text-danger me-2"></i>Rejected</span>
                                        <span class="fw-bold">{{ $summary['rejected_products'] ?? 0 }} ({{ $distribution['approval_percentages']['rejected'] ?? 0 }}%)</span>
                                    </li>
                                </ul>

                                <div class="mt-3 pt-2 border-top">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Active vs Inactive Ratio</span>
                                        <span class="fw-semibold">
                                            {{ $summary['active_products'] ?? 0 }} Active / {{ $summary['inactive_products'] ?? 0 }} Inactive
                                        </span>
                                    </div>
                                    @php
                                        $tot = max(1, ($summary['total_products'] ?? 0));
                                        $activePct = round((($summary['active_products'] ?? 0) / $tot) * 100);
                                    @endphp
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: {{ $activePct }}%;"></div>
                                        <div class="progress-bar bg-secondary" style="width: {{ 100 - $activePct }}%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Product Commercial Performance (With Explicit Untracked Explanations) -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Commercial Performance & Transactional Analytics</h6>
                    <div class="ms-auto text-muted small">
                        Period: <span class="fw-bold">{{ $analytics['date_range']['label'] ?? 'Last 30 Days' }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-3 mb-3">
                    <div class="col">
                        <div class="p-3 border rounded text-center bg-light">
                            <i class="fa-solid fa-bag-shopping text-primary fs-4 mb-2"></i>
                            <div class="text-muted small fw-semibold">Orders Received</div>
                            <h4 class="my-1 fw-bold text-dark">{{ number_format($commercial['total_orders'] ?? 0) }}</h4>
                            <span class="small text-muted">From buyer orders</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 border rounded text-center bg-light">
                            <i class="fa-solid fa-boxes-stacked text-success fs-4 mb-2"></i>
                            <div class="text-muted small fw-semibold">Units Sold</div>
                            <h4 class="my-1 fw-bold text-success">{{ number_format($commercial['units_sold'] ?? 0) }}</h4>
                            <span class="small text-muted">Quantity confirmed</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 border rounded text-center bg-light">
                            <i class="fa-solid fa-indian-rupee-sign text-info fs-4 mb-2"></i>
                            <div class="text-muted small fw-semibold">Sales Revenue</div>
                            <h4 class="my-1 fw-bold text-info">₹{{ number_format($commercial['sales_value'] ?? 0, 2) }}</h4>
                            <span class="small text-muted">Net sales value</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 border rounded text-center bg-light">
                            <i class="fa-solid fa-cart-shopping text-warning fs-4 mb-2"></i>
                            <div class="text-muted small fw-semibold">Active Cart Interest</div>
                            <h4 class="my-1 fw-bold text-warning">{{ number_format($commercial['active_cart_count'] ?? 0) }}</h4>
                            <span class="small text-muted">Items currently in buyer carts</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 border rounded text-center bg-light">
                            <i class="fa-solid fa-star text-warning fs-4 mb-2"></i>
                            <div class="text-muted small fw-semibold">Catalogue Reviews</div>
                            <h4 class="my-1 fw-bold text-dark">
                                {{ $commercial['product_reviews']['average_rating'] ? $commercial['product_reviews']['average_rating'] . ' ★' : 'N/A' }}
                            </h4>
                            <span class="small text-muted">{{ $commercial['product_reviews']['total_reviews'] ?? 0 }} customer ratings</span>
                        </div>
                    </div>
                </div>

                <!-- Explicit Untracked Metrics Notice (Enterprise Transparency) -->
                <div class="alert alert-light border shadow-none mb-0">
                    <div class="d-flex align-items-start">
                        <i class="fa-solid fa-circle-info text-primary fs-5 mt-1 me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Untracked Catalogue Metrics & Enterprise Data Integrity Notice</h6>
                            <p class="mb-2 small text-muted">
                                ApniFactory adheres strictly to truthful data integrity and does not fabricate metrics or display misleading zero counts for features not currently instrumented in the database:
                            </p>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="border rounded p-2 bg-white small">
                                        <div class="fw-semibold text-secondary"><i class="fa-regular fa-eye me-1"></i> Product Impressions</div>
                                        <span class="badge bg-light-secondary text-secondary">Unavailable</span>
                                        <p class="mb-0 text-muted mt-1" style="font-size: 0.8rem;">
                                            {{ $commercial['product_impressions']['description'] ?? 'Search and listing impression counters are not enabled in current database.' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-2 bg-white small">
                                        <div class="fw-semibold text-secondary"><i class="fa-solid fa-arrow-pointer me-1"></i> Detail Page Views</div>
                                        <span class="badge bg-light-secondary text-secondary">Unavailable</span>
                                        <p class="mb-0 text-muted mt-1" style="font-size: 0.8rem;">
                                            {{ $commercial['product_views']['description'] ?? 'Individual product click/view telemetry is not enabled in current database.' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-2 bg-white small">
                                        <div class="fw-semibold text-secondary"><i class="fa-regular fa-message me-1"></i> Direct Buyer Enquiries</div>
                                        <span class="badge bg-light-secondary text-secondary">Unavailable</span>
                                        <p class="mb-0 text-muted mt-1" style="font-size: 0.8rem;">
                                            {{ $commercial['product_enquiries']['description'] ?? 'Direct buyer catalogue enquiries are not tracked in current database.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Category & Brand Insights Section -->
        <div class="row g-3">
            <div class="col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-layer-group me-2 text-primary"></i>Category Distribution & Breakdown</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(empty($categories))
                            <div class="text-center py-4 text-muted small">
                                <i class="fa-solid fa-folder-open fs-2 mb-2 d-block text-secondary"></i>
                                No categorized products found in your catalogue.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th>Category</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Approved</th>
                                            <th class="text-center">Pending</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(array_slice($categories, 0, 8) as $cat)
                                            <tr>
                                                <td class="fw-semibold text-dark">{{ $cat['category_name'] }}</td>
                                                <td class="text-center"><span class="badge bg-secondary">{{ $cat['total_products'] }}</span></td>
                                                <td class="text-center"><span class="badge bg-success">{{ $cat['approved_products'] }}</span></td>
                                                <td class="text-center">
                                                    @if($cat['pending_products'] > 0)
                                                        <span class="badge bg-warning text-dark">{{ $cat['pending_products'] }}</span>
                                                    @else
                                                        <span class="text-muted">0</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('seller.catalogue.submissions', ['category_id' => $cat['category_id']]) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-copyright me-2 text-primary"></i>Brand Distribution & Breakdown</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(empty($brands))
                            <div class="text-center py-4 text-muted small">
                                <i class="fa-solid fa-tag fs-2 mb-2 d-block text-secondary"></i>
                                No branded products found in your catalogue.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th>Brand</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Approved</th>
                                            <th class="text-center">Pending</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(array_slice($brands, 0, 8) as $brand)
                                            <tr>
                                                <td class="fw-semibold text-dark">{{ $brand['brand_name'] }}</td>
                                                <td class="text-center"><span class="badge bg-secondary">{{ $brand['total_products'] }}</span></td>
                                                <td class="text-center"><span class="badge bg-success">{{ $brand['approved_products'] }}</span></td>
                                                <td class="text-center">
                                                    @if($brand['pending_products'] > 0)
                                                        <span class="badge bg-warning text-dark">{{ $brand['pending_products'] }}</span>
                                                    @else
                                                        <span class="text-muted">0</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('seller.catalogue.submissions', ['brand_id' => $brand['brand_id']]) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Chart.js via CDN for responsive rendering -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Period Selector Toggle for Custom Range
    const periodSelect = document.getElementById('periodSelect');
    const customDateCols = document.querySelectorAll('.custom-date-col');

    if (periodSelect) {
        periodSelect.addEventListener('change', function () {
            if (this.value === 'custom') {
                customDateCols.forEach(col => col.style.display = 'block');
            } else {
                customDateCols.forEach(col => col.style.display = 'none');
                // Auto submit for preset periods
                document.getElementById('analyticsFilterForm').submit();
            }
        });
    }

    // 2. Status Distribution Doughnut Chart
    const statusCtx = document.getElementById('statusDistributionChart');
    if (statusCtx) {
        const distData = @json($distribution['approval_distribution'] ?? []);
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Draft', 'Corrections', 'Rejected'],
                datasets: [{
                    data: [
                        distData.approved || 0,
                        distData.pending_review || 0,
                        distData.draft || 0,
                        distData.corrections_requested || 0,
                        distData.rejected || 0
                    ],
                    backgroundColor: [
                        '#198754', // success (approved)
                        '#ffc107', // warning (pending)
                        '#6c757d', // secondary (draft)
                        '#0dcaf0', // info (corrections)
                        '#dc3545'  // danger (rejected)
                    ],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '70%'
            }
        });
    }

    // 3. Approval Trends Chart
    const trendsCtx = document.getElementById('approvalTrendsChart');
    if (trendsCtx) {
        const trendData = @json($approval['review_trends'] ?? []);
        const labels = trendData.map(item => item.day_label || item.date);
        const submissions = trendData.map(item => item.submissions || 0);
        const approvals = trendData.map(item => item.approvals || 0);
        const rejections = trendData.map(item => item.rejections || 0);

        new Chart(trendsCtx, {
            type: 'bar',
            data: {
                labels: labels.length > 0 ? labels : ['No Activity'],
                datasets: [
                    {
                        label: 'Submissions',
                        data: submissions.length > 0 ? submissions : [0],
                        backgroundColor: '#0d6efd',
                        borderRadius: 4
                    },
                    {
                        label: 'Approvals',
                        data: approvals.length > 0 ? approvals : [0],
                        backgroundColor: '#198754',
                        borderRadius: 4
                    },
                    {
                        label: 'Rejections',
                        data: rejections.length > 0 ? rejections : [0],
                        backgroundColor: '#dc3545',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 10 } }
                    }
                }
            }
        });
    }
});
</script>

<style>
.widgets-icons-2 {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
.bg-light-primary { background-color: rgba(13, 110, 253, 0.12); }
.bg-light-success { background-color: rgba(25, 135, 84, 0.12); }
.bg-light-info { background-color: rgba(13, 202, 240, 0.12); }
.bg-light-warning { background-color: rgba(255, 193, 7, 0.15); }
.bg-light-danger { background-color: rgba(220, 53, 69, 0.12); }
.bg-light-secondary { background-color: rgba(108, 117, 125, 0.12); }
.health-score-circle {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}
</style>
@endsection
