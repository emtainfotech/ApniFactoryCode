@extends('layout')
@section('title', 'Category Commission Tree Manager')
@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Admin Portal</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ url('seller/dashboard') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.commissions.rules.index') }}">Commissions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Category Commission Tree</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.commissions.rules.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-list-check me-1"></i> Commission Rules
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm mb-0">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small fw-semibold">Main Categories</p>
                                <h4 class="my-1 text-primary fw-bold">{{ $mainCategories->count() }}</h4>
                                <small class="text-muted">Top-level categories</small>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-info shadow-sm mb-0">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small fw-semibold">Categories (Level 2)</p>
                                <h4 class="my-1 text-info fw-bold">{{ $totalCategories }}</h4>
                                <small class="text-muted">Direct subcategories</small>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                                <i class="fa-solid fa-sitemap"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-success shadow-sm mb-0">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small fw-semibold">Nested Subcategories</p>
                                <h4 class="my-1 text-success fw-bold">{{ $totalSubcategories }}</h4>
                                <small class="text-muted">Deepest category nodes</small>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class="fa-solid fa-diagram-project"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-warning shadow-sm mb-0">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small fw-semibold">Custom Overrides</p>
                                <h4 class="my-1 text-warning fw-bold">{{ $configuredOverrides }}</h4>
                                <small class="text-muted">Node-specific rates</small>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                                <i class="fa-solid fa-sliders"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 border-bottom mb-3">
                    <div>
                        <h4 class="mb-1 text-dark fw-bold"><i class="fa-solid fa-tree text-primary me-2"></i>Category Commission Hierarchy & Cascading Manager</h4>
                        <p class="text-muted mb-0 small">Set commission % at the Main Category level to cascade down to all subcategories, or override specific subcategories.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnExpandAll">
                            <i class="fa-solid fa-angles-down me-1"></i> Expand All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCollapseAll">
                            <i class="fa-solid fa-angles-up me-1"></i> Collapse All
                        </button>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="alert alert-info border-0 bg-light-info text-dark d-flex align-items-center mb-4">
                    <i class="fa-solid fa-circle-info fs-4 text-info me-3"></i>
                    <div>
                        <strong>Hierarchical Cascading Rules:</strong>
                        Setting commission on a <strong>Main Category</strong> and checking <em>"Apply to all subcategories"</em> automatically updates all child Categories and Nested Subcategories. Child nodes can also have specific overrides.
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="row mb-3">
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" id="categoryTreeSearch" class="form-control" placeholder="Search categories or subcategories...">
                        </div>
                    </div>
                </div>

                <!-- Hierarchy Tree Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle border mb-0" id="commissionTreeTable">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 45%;">Category Name & Hierarchy</th>
                                <th style="width: 15%; text-align: center;">Children Count</th>
                                <th style="width: 15%; text-align: center;">Rate Status</th>
                                <th style="width: 25%;">Commission % & Cascade Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mainCategories as $mainCat)
                                @php
                                    $mainRate = $mainCat->commission_rate;
                                    $childCats = $mainCat->child_categories ?? collect();
                                    $totalNestedSubcats = $childCats->sum(fn($c) => ($c->nested_subcategories ?? collect())->count());
                                @endphp
                                <!-- Level 1: Main Category Row -->
                                <tr class="tree-main-row table-light" data-main-id="{{ $mainCat->id }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <button type="button" class="btn btn-sm btn-link text-dark p-0 me-2 btn-toggle-main" data-target="main_{{ $mainCat->id }}">
                                                <i class="fa-solid fa-chevron-right toggle-icon"></i>
                                            </button>
                                            <div class="rounded bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                                <i class="fa-solid fa-layer-group"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold fs-6 text-dark category-name">{{ $mainCat->name }}</span>
                                                <span class="badge bg-primary ms-1" style="font-size: 0.68rem;">Main Category</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">
                                            {{ $childCats->count() }} Cats / {{ $totalNestedSubcats }} Subcats
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($mainRate !== null)
                                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1">Set: {{ number_format($mainRate, 2) }}%</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">Default (25.00%)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <div class="input-group input-group-sm" style="max-width: 130px;">
                                                <input type="number" step="0.01" min="0" max="100" 
                                                    class="form-control fw-bold commission-input" 
                                                    value="{{ $mainRate !== null ? number_format($mainRate, 2, '.', '') : '' }}" 
                                                    placeholder="25.00">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-check form-check-inline mb-0 small">
                                                <input class="form-check-input cascade-check" type="checkbox" id="cascade_main_{{ $mainCat->id }}" checked>
                                                <label class="form-check-label small" for="cascade_main_{{ $mainCat->id }}">Apply to All</label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-primary btn-save-commission" 
                                                data-level="main" 
                                                data-id="{{ $mainCat->id }}" 
                                                data-name="{{ $mainCat->name }}">
                                                <i class="fa-solid fa-check"></i> Save
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Level 2: Categories under Main Category -->
                                @foreach($childCats as $cat)
                                    @php
                                        $catRate = $cat->commission_rate;
                                        $nestedSubcats = $cat->nested_subcategories ?? collect();
                                        $effectiveParentRate = $mainRate !== null ? $mainRate : 25.00;
                                    @endphp
                                    <tr class="tree-cat-row child-of-main-{{ $mainCat->id }} d-none" 
                                        data-main-id="{{ $mainCat->id }}" 
                                        data-cat-id="{{ $cat->id }}">
                                        <td style="padding-left: 2.5rem;">
                                            <div class="d-flex align-items-center">
                                                @if($nestedSubcats->isNotEmpty())
                                                    <button type="button" class="btn btn-sm btn-link text-secondary p-0 me-2 btn-toggle-cat" data-target="cat_{{ $cat->id }}">
                                                        <i class="fa-solid fa-chevron-right toggle-icon-sub"></i>
                                                    </button>
                                                @else
                                                    <span class="me-3 text-muted" style="font-size: 0.75rem;">└─</span>
                                                @endif
                                                <div class="rounded bg-info text-white d-flex align-items-center justify-content-center me-2" style="width: 26px; height: 26px; font-size: 0.75rem;">
                                                    <i class="fa-solid fa-folder"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold text-dark category-name">{{ $cat->name }}</span>
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Category #{{ $cat->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-muted border" style="font-size: 0.75rem;">
                                                {{ $nestedSubcats->count() }} Subcats
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($catRate !== null)
                                                <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1">Override: {{ number_format($catRate, 2) }}%</span>
                                            @else
                                                <span class="badge bg-light text-muted border px-2 py-1">Inherited ({{ number_format($effectiveParentRate, 2) }}%)</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <div class="input-group input-group-sm" style="max-width: 120px;">
                                                    <input type="number" step="0.01" min="0" max="100" 
                                                        class="form-control commission-input" 
                                                        value="{{ $catRate !== null ? number_format($catRate, 2, '.', '') : '' }}" 
                                                        placeholder="{{ number_format($effectiveParentRate, 2) }}">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                                @if($nestedSubcats->isNotEmpty())
                                                    <div class="form-check form-check-inline mb-0 small">
                                                        <input class="form-check-input cascade-check" type="checkbox" id="cascade_cat_{{ $cat->id }}" checked>
                                                        <label class="form-check-label small" for="cascade_cat_{{ $cat->id }}">Cascade</label>
                                                    </div>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-save-commission" 
                                                    data-level="category" 
                                                    data-id="{{ $cat->id }}" 
                                                    data-name="{{ $cat->name }}">
                                                    <i class="fa-solid fa-check"></i> Save
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Level 3: Nested Subcategories under Category -->
                                    @foreach($nestedSubcats as $subcat)
                                        @php
                                            $subcatRate = $subcat->commission_rate;
                                            $effectiveCatRate = $catRate !== null ? $catRate : $effectiveParentRate;
                                        @endphp
                                        <tr class="tree-subcat-row child-of-cat-{{ $cat->id }} d-none" 
                                            data-main-id="{{ $mainCat->id }}" 
                                            data-cat-id="{{ $cat->id }}" 
                                            data-subcat-id="{{ $subcat->id }}">
                                            <td style="padding-left: 4.5rem;">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2 text-muted" style="font-size: 0.75rem;">└──</span>
                                                    <div class="rounded bg-success text-white d-flex align-items-center justify-content-center me-2" style="width: 22px; height: 22px; font-size: 0.65rem;">
                                                        <i class="fa-solid fa-tag"></i>
                                                    </div>
                                                    <div>
                                                        <span class="text-dark category-name">{{ $subcat->name }}</span>
                                                        <small class="text-muted d-block" style="font-size: 0.68rem;">Nested Subcat #{{ $subcat->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center text-muted small">-</td>
                                            <td class="text-center">
                                                @if($subcatRate !== null)
                                                    <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-1">Override: {{ number_format($subcatRate, 2) }}%</span>
                                                @else
                                                    <span class="badge bg-light text-muted border px-2 py-1">Inherited ({{ number_format($effectiveCatRate, 2) }}%)</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="input-group input-group-sm" style="max-width: 120px;">
                                                        <input type="number" step="0.01" min="0" max="100" 
                                                            class="form-control commission-input" 
                                                            value="{{ $subcatRate !== null ? number_format($subcatRate, 2, '.', '') : '' }}" 
                                                            placeholder="{{ number_format($effectiveCatRate, 2) }}">
                                                        <span class="input-group-text">%</span>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-success btn-save-commission" 
                                                        data-level="subcategory" 
                                                        data-id="{{ $subcat->id }}" 
                                                        data-name="{{ $subcat->name }}">
                                                        <i class="fa-solid fa-check"></i> Save
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No categories configured.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('commissionTreeTable');
    if (!table) return;

    // Toggle Main Category expansion
    table.addEventListener('click', function (e) {
        const btnMain = e.target.closest('.btn-toggle-main');
        if (btnMain) {
            const row = btnMain.closest('tr');
            const mainId = row.getAttribute('data-main-id');
            const icon = btnMain.querySelector('.toggle-icon');
            const childCatRows = table.querySelectorAll(`.child-of-main-${mainId}`);

            const isExpanded = icon.classList.contains('fa-chevron-down');
            if (isExpanded) {
                // Collapse
                icon.classList.replace('fa-chevron-down', 'fa-chevron-right');
                childCatRows.forEach(r => {
                    r.classList.add('d-none');
                    // Also collapse nested subcategories under those
                    const catId = r.getAttribute('data-cat-id');
                    if (catId) {
                        table.querySelectorAll(`.child-of-cat-${catId}`).forEach(sr => sr.classList.add('d-none'));
                        const subIcon = r.querySelector('.toggle-icon-sub');
                        if (subIcon) subIcon.classList.replace('fa-chevron-down', 'fa-chevron-right');
                    }
                });
            } else {
                // Expand
                icon.classList.replace('fa-chevron-right', 'fa-chevron-down');
                childCatRows.forEach(r => r.classList.remove('d-none'));
            }
            return;
        }

        // Toggle Category expansion
        const btnCat = e.target.closest('.btn-toggle-cat');
        if (btnCat) {
            const row = btnCat.closest('tr');
            const catId = row.getAttribute('data-cat-id');
            const icon = btnCat.querySelector('.toggle-icon-sub');
            const childSubcatRows = table.querySelectorAll(`.child-of-cat-${catId}`);

            const isExpanded = icon.classList.contains('fa-chevron-down');
            if (isExpanded) {
                icon.classList.replace('fa-chevron-down', 'fa-chevron-right');
                childSubcatRows.forEach(r => r.classList.add('d-none'));
            } else {
                icon.classList.replace('fa-chevron-right', 'fa-chevron-down');
                childSubcatRows.forEach(r => r.classList.remove('d-none'));
            }
            return;
        }

        // Save Commission Handler
        const btnSave = e.target.closest('.btn-save-commission');
        if (btnSave) {
            const row = btnSave.closest('tr');
            const level = btnSave.getAttribute('data-level');
            const id = btnSave.getAttribute('data-id');
            const name = btnSave.getAttribute('data-name');
            const input = row.querySelector('.commission-input');
            const cascadeCheck = row.querySelector('.cascade-check');
            const cascade = cascadeCheck ? cascadeCheck.checked : false;

            const val = input.value.trim();
            const rate = val !== '' ? parseFloat(val) : null;

            if (val !== '' && (isNaN(rate) || rate < 0 || rate > 100)) {
                alert('Please enter a valid commission percentage between 0 and 100.');
                return;
            }

            const originalHtml = btnSave.innerHTML;
            btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
            btnSave.disabled = true;

            fetch("{{ route('admin.commissions.categories.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    level: level,
                    id: id,
                    commission_rate: rate,
                    cascade: cascade ? 1 : 0
                })
            })
            .then(res => res.json())
            .then(data => {
                btnSave.disabled = false;
                if (data.status) {
                    btnSave.innerHTML = '<i class="fa-solid fa-check text-success"></i> Saved';
                    btnSave.classList.add('btn-success');
                    setTimeout(() => {
                        btnSave.innerHTML = originalHtml;
                        btnSave.classList.remove('btn-success');
                    }, 1500);

                    // If cascade was enabled, update child input placeholders and values
                    if (cascade && rate !== null) {
                        if (level === 'main') {
                            const childRows = table.querySelectorAll(`tr[data-main-id="${id}"]:not(.tree-main-row)`);
                            childRows.forEach(cr => {
                                const crInput = cr.querySelector('.commission-input');
                                if (crInput) {
                                    crInput.value = rate.toFixed(2);
                                    crInput.placeholder = rate.toFixed(2);
                                }
                            });
                        } else if (level === 'category') {
                            const subRows = table.querySelectorAll(`.child-of-cat-${id}`);
                            subRows.forEach(sr => {
                                const srInput = sr.querySelector('.commission-input');
                                if (srInput) {
                                    srInput.value = rate.toFixed(2);
                                    srInput.placeholder = rate.toFixed(2);
                                }
                            });
                        }
                    }

                    // Show temporary toast or feedback
                    row.classList.add('table-success');
                    setTimeout(() => row.classList.remove('table-success'), 1200);
                } else {
                    btnSave.innerHTML = originalHtml;
                    alert(data.message || 'Failed to update commission rate.');
                }
            })
            .catch(err => {
                btnSave.disabled = false;
                btnSave.innerHTML = originalHtml;
                alert('Request failed. Check network or permissions.');
            });
        }
    });

    // Expand All Button
    document.getElementById('btnExpandAll').addEventListener('click', function () {
        table.querySelectorAll('.tree-cat-row, .tree-subcat-row').forEach(r => r.classList.remove('d-none'));
        table.querySelectorAll('.toggle-icon, .toggle-icon-sub').forEach(icon => {
            icon.classList.replace('fa-chevron-right', 'fa-chevron-down');
        });
    });

    // Collapse All Button
    document.getElementById('btnCollapseAll').addEventListener('click', function () {
        table.querySelectorAll('.tree-cat-row, .tree-subcat-row').forEach(r => r.classList.add('d-none'));
        table.querySelectorAll('.toggle-icon, .toggle-icon-sub').forEach(icon => {
            icon.classList.replace('fa-chevron-down', 'fa-chevron-right');
        });
    });

    // Search filter
    const searchInput = document.getElementById('categoryTreeSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const allRows = table.querySelectorAll('tbody tr');

            if (!query) {
                // Reset to default collapsed state
                allRows.forEach(r => {
                    if (r.classList.contains('tree-main-row')) {
                        r.classList.remove('d-none');
                        const icon = r.querySelector('.toggle-icon');
                        if (icon) icon.classList.replace('fa-chevron-down', 'fa-chevron-right');
                    } else {
                        r.classList.add('d-none');
                    }
                });
                return;
            }

            // If searching, show matching rows and their parents
            allRows.forEach(r => r.classList.add('d-none'));

            allRows.forEach(r => {
                const nameEl = r.querySelector('.category-name');
                if (nameEl && nameEl.textContent.toLowerCase().includes(query)) {
                    r.classList.remove('d-none');
                    const mainId = r.getAttribute('data-main-id');
                    const catId = r.getAttribute('data-cat-id');

                    if (mainId) {
                        const mainRow = table.querySelector(`.tree-main-row[data-main-id="${mainId}"]`);
                        if (mainRow) {
                            mainRow.classList.remove('d-none');
                            const icon = mainRow.querySelector('.toggle-icon');
                            if (icon) icon.classList.replace('fa-chevron-right', 'fa-chevron-down');
                        }
                    }
                    if (catId) {
                        const catRow = table.querySelector(`.tree-cat-row[data-cat-id="${catId}"]`);
                        if (catRow) {
                            catRow.classList.remove('d-none');
                            const subIcon = catRow.querySelector('.toggle-icon-sub');
                            if (subIcon) subIcon.classList.replace('fa-chevron-right', 'fa-chevron-down');
                        }
                    }
                }
            });
        });
    }
});
</script>
@endsection

