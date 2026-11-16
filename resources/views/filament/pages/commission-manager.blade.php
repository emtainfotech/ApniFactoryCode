<x-filament::page>
    <div class="space-y-6">
        <!-- Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Main Categories</div>
                <div class="text-2xl font-bold text-primary-600 mt-1">{{ $mainCategories->count() }}</div>
                <div class="text-xs text-gray-400">Level 1 Top-Level</div>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Categories (Level 2)</div>
                <div class="text-2xl font-bold text-info-600 mt-1">{{ $totalCategories }}</div>
                <div class="text-xs text-gray-400">Direct Subcategories</div>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Nested Subcategories</div>
                <div class="text-2xl font-bold text-success-600 mt-1">{{ $totalSubcategories }}</div>
                <div class="text-xs text-gray-400">Deepest Nodes</div>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Custom Overrides</div>
                <div class="text-2xl font-bold text-warning-600 mt-1">{{ $configuredOverrides }}</div>
                <div class="text-xs text-gray-400">Node-Specific Overrides</div>
            </div>
        </div>

        <!-- Banner Info -->
        <div class="p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="text-sm text-blue-900 dark:text-blue-200">
                <strong>Cascading Commission Engine:</strong> Set a commission % on any <strong>Main Category</strong> and check <em>"Apply to All"</em> to automatically cascade down to all child Categories and nested Subcategories. Individual subcategories can also be overridden with custom rates.
            </div>
        </div>

        <!-- Tree Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap justify-between items-center gap-3">
                <div class="flex items-center gap-2">
                    <input type="text" id="filamentTreeSearch" placeholder="Search categories..." class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-1.5 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="btnFilamentExpandAll" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200">
                        Expand All
                    </button>
                    <button type="button" id="btnFilamentCollapseAll" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200">
                        Collapse All
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="filamentCommissionTree">
                    <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 font-semibold uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3" style="width: 45%;">Category Hierarchy</th>
                            <th class="px-4 py-3 text-center" style="width: 15%;">Children</th>
                            <th class="px-4 py-3 text-center" style="width: 15%;">Rate Status</th>
                            <th class="px-6 py-3" style="width: 25%;">Commission % & Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($mainCategories as $mainCat)
                            @php
                                $mainRate = $mainCat->commission_rate;
                                $childCats = $mainCat->child_categories ?? collect();
                                $totalNested = $childCats->sum(fn($c) => ($c->nested_subcategories ?? collect())->count());
                            @endphp
                            <!-- Main Category Row -->
                            <tr class="f-main-row bg-gray-50/70 dark:bg-gray-800/80 hover:bg-gray-100 dark:hover:bg-gray-750 transition" data-main-id="{{ $mainCat->id }}">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="f-btn-toggle-main text-gray-500 hover:text-gray-900 dark:hover:text-white p-1">
                                            <svg class="w-4 h-4 f-icon transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </button>
                                        <div class="w-7 h-7 rounded bg-primary-500 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                            L1
                                        </div>
                                        <div>
                                            <span class="font-bold text-gray-900 dark:text-white f-name text-base">{{ $mainCat->name }}</span>
                                            <span class="ml-1 text-xs px-2 py-0.5 rounded-full bg-primary-100 text-primary-800 dark:bg-primary-900/60 dark:text-primary-300">Main Category</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center text-xs text-gray-500">
                                    {{ $childCats->count() }} Cats / {{ $totalNested }} Subcats
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($mainRate !== null)
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                            {{ number_format($mainRate, 2) }}%
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            Default 25%
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="relative rounded-md shadow-sm w-28">
                                            <input type="number" step="0.01" min="0" max="100" 
                                                class="f-comm-input block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 pr-7 text-sm font-bold focus:border-primary-500 focus:ring-primary-500" 
                                                value="{{ $mainRate !== null ? number_format($mainRate, 2, '.', '') : '' }}" 
                                                placeholder="25.00">
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                                <span class="text-gray-400 text-xs font-semibold">%</span>
                                            </div>
                                        </div>
                                        <label class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300 cursor-pointer">
                                            <input type="checkbox" class="f-cascade-check rounded border-gray-300 text-primary-600 focus:ring-primary-500" checked>
                                            <span>Apply All</span>
                                        </label>
                                        <button type="button" class="f-btn-save px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary-600 hover:bg-primary-500 text-white shadow-sm"
                                            data-level="main" data-id="{{ $mainCat->id }}">
                                            Save
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Level 2: Categories -->
                            @foreach($childCats as $cat)
                                @php
                                    $catRate = $cat->commission_rate;
                                    $nestedSubcats = $cat->nested_subcategories ?? collect();
                                    $parentRate = $mainRate !== null ? $mainRate : 25.00;
                                @endphp
                                <tr class="f-cat-row f-child-of-main-{{ $mainCat->id }} hidden bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750" 
                                    data-main-id="{{ $mainCat->id }}" data-cat-id="{{ $cat->id }}">
                                    <td class="px-6 py-2.5" style="padding-left: 2.75rem;">
                                        <div class="flex items-center gap-2">
                                            @if($nestedSubcats->isNotEmpty())
                                                <button type="button" class="f-btn-toggle-cat text-gray-400 hover:text-gray-700 p-0.5">
                                                    <svg class="w-3.5 h-3.5 f-icon-sub transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                </button>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600 text-xs mr-2">└─</span>
                                            @endif
                                            <div class="w-5 h-5 rounded bg-blue-500 text-white flex items-center justify-center font-bold text-[10px] shrink-0">
                                                L2
                                            </div>
                                            <div>
                                                <span class="font-semibold text-gray-800 dark:text-gray-200 f-name text-sm">{{ $cat->name }}</span>
                                                <span class="text-gray-400 text-[11px] block">Category #{{ $cat->id }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-xs text-gray-400">
                                        {{ $nestedSubcats->count() }} subcats
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if($catRate !== null)
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                Override: {{ number_format($catRate, 2) }}%
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                                Inherited ({{ number_format($parentRate, 2) }}%)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="relative rounded-md shadow-sm w-24">
                                                <input type="number" step="0.01" min="0" max="100" 
                                                    class="f-comm-input block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 pr-6 text-xs font-semibold" 
                                                    value="{{ $catRate !== null ? number_format($catRate, 2, '.', '') : '' }}" 
                                                    placeholder="{{ number_format($parentRate, 2) }}">
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-1.5">
                                                    <span class="text-gray-400 text-[10px]">%</span>
                                                </div>
                                            </div>
                                            @if($nestedSubcats->isNotEmpty())
                                                <label class="flex items-center gap-1 text-[11px] text-gray-500 cursor-pointer">
                                                    <input type="checkbox" class="f-cascade-check rounded border-gray-300 text-primary-600" checked>
                                                    <span>Cascade</span>
                                                </label>
                                            @endif
                                            <button type="button" class="f-btn-save px-2.5 py-1 text-xs font-semibold rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200"
                                                data-level="category" data-id="{{ $cat->id }}">
                                                Save
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Level 3: Nested Subcategories -->
                                @foreach($nestedSubcats as $subcat)
                                    @php
                                        $subcatRate = $subcat->commission_rate;
                                        $effectiveParent = $catRate !== null ? $catRate : $parentRate;
                                    @endphp
                                    <tr class="f-subcat-row f-child-of-cat-{{ $cat->id }} hidden bg-gray-50/40 dark:bg-gray-900/30" 
                                        data-main-id="{{ $mainCat->id }}" data-cat-id="{{ $cat->id }}" data-subcat-id="{{ $subcat->id }}">
                                        <td class="px-6 py-2" style="padding-left: 4.75rem;">
                                            <div class="flex items-center gap-2">
                                                <span class="text-gray-300 dark:text-gray-600 text-xs">└──</span>
                                                <span class="text-gray-700 dark:text-gray-300 f-name text-xs">{{ $subcat->name }}</span>
                                                <span class="text-gray-400 text-[10px]">(#{{ $subcat->id }})</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-center text-xs text-gray-400">-</td>
                                        <td class="px-4 py-2 text-center">
                                            @if($subcatRate !== null)
                                                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                    {{ number_format($subcatRate, 2) }}%
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 text-[11px] rounded-full bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                                    Inherited ({{ number_format($effectiveParent, 2) }}%)
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-2">
                                            <div class="flex items-center gap-2">
                                                <div class="relative rounded-md shadow-sm w-24">
                                                    <input type="number" step="0.01" min="0" max="100" 
                                                        class="f-comm-input block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 pr-6 text-xs" 
                                                        value="{{ $subcatRate !== null ? number_format($subcatRate, 2, '.', '') : '' }}" 
                                                        placeholder="{{ number_format($effectiveParent, 2) }}">
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-1.5">
                                                        <span class="text-gray-400 text-[10px]">%</span>
                                                    </div>
                                                </div>
                                                <button type="button" class="f-btn-save px-2.5 py-1 text-xs font-semibold rounded bg-green-600 hover:bg-green-500 text-white"
                                                    data-level="subcategory" data-id="{{ $subcat->id }}">
                                                    Save
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.getElementById('filamentCommissionTree');
        if (!table) return;

        // Toggle Main Categories
        table.addEventListener('click', function (e) {
            const btnMain = e.target.closest('.f-btn-toggle-main');
            if (btnMain) {
                const row = btnMain.closest('tr');
                const mainId = row.getAttribute('data-main-id');
                const icon = btnMain.querySelector('.f-icon');
                const isExpanded = icon.classList.contains('rotate-90');
                const childCats = table.querySelectorAll(`.f-child-of-main-${mainId}`);

                if (isExpanded) {
                    icon.classList.remove('rotate-90');
                    childCats.forEach(cr => {
                        cr.classList.add('hidden');
                        const catId = cr.getAttribute('data-cat-id');
                        if (catId) {
                            table.querySelectorAll(`.f-child-of-cat-${catId}`).forEach(sr => sr.classList.add('hidden'));
                            const subIcon = cr.querySelector('.f-icon-sub');
                            if (subIcon) subIcon.classList.remove('rotate-90');
                        }
                    });
                } else {
                    icon.classList.add('rotate-90');
                    childCats.forEach(cr => cr.classList.remove('hidden'));
                }
                return;
            }

            // Toggle Categories
            const btnCat = e.target.closest('.f-btn-toggle-cat');
            if (btnCat) {
                const row = btnCat.closest('tr');
                const catId = row.getAttribute('data-cat-id');
                const icon = btnCat.querySelector('.f-icon-sub');
                const isExpanded = icon.classList.contains('rotate-90');
                const childSubcats = table.querySelectorAll(`.f-child-of-cat-${catId}`);

                if (isExpanded) {
                    icon.classList.remove('rotate-90');
                    childSubcats.forEach(sr => sr.classList.add('hidden'));
                } else {
                    icon.classList.add('rotate-90');
                    childSubcats.forEach(sr => sr.classList.remove('hidden'));
                }
                return;
            }

            // Save Commission Handler
            const btnSave = e.target.closest('.f-btn-save');
            if (btnSave) {
                const row = btnSave.closest('tr');
                const level = btnSave.getAttribute('data-level');
                const id = btnSave.getAttribute('data-id');
                const input = row.querySelector('.f-comm-input');
                const cascadeCheck = row.querySelector('.f-cascade-check');
                const cascade = cascadeCheck ? cascadeCheck.checked : false;

                const val = input.value.trim();
                const rate = val !== '' ? parseFloat(val) : null;

                const originalText = btnSave.innerHTML;
                btnSave.innerHTML = '...';
                btnSave.disabled = true;

                fetch("{{ route('admin.commissions.categories.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        level: level,
                        id: id,
                        commission_rate: rate,
                        cascade: cascade ? 1 : 0
                    })
                })
                .then(r => r.json())
                .then(data => {
                    btnSave.disabled = false;
                    btnSave.innerHTML = '✓ Saved';
                    setTimeout(() => { btnSave.innerHTML = originalText; }, 1500);

                    if (cascade && rate !== null) {
                        if (level === 'main') {
                            table.querySelectorAll(`tr[data-main-id="${id}"]:not(.f-main-row) .f-comm-input`).forEach(inp => {
                                inp.value = rate.toFixed(2);
                                inp.placeholder = rate.toFixed(2);
                            });
                        } else if (level === 'category') {
                            table.querySelectorAll(`.f-child-of-cat-${id} .f-comm-input`).forEach(inp => {
                                inp.value = rate.toFixed(2);
                                inp.placeholder = rate.toFixed(2);
                            });
                        }
                    }
                })
                .catch(() => {
                    btnSave.disabled = false;
                    btnSave.innerHTML = originalText;
                    alert('Save failed.');
                });
            }
        });

        // Expand All
        document.getElementById('btnFilamentExpandAll')?.addEventListener('click', function () {
            table.querySelectorAll('.f-cat-row, .f-subcat-row').forEach(r => r.classList.remove('hidden'));
            table.querySelectorAll('.f-icon, .f-icon-sub').forEach(i => i.classList.add('rotate-90'));
        });

        // Collapse All
        document.getElementById('btnFilamentCollapseAll')?.addEventListener('click', function () {
            table.querySelectorAll('.f-cat-row, .f-subcat-row').forEach(r => r.classList.add('hidden'));
            table.querySelectorAll('.f-icon, .f-icon-sub').forEach(i => i.classList.remove('rotate-90'));
        });
    });
    </script>
</x-filament::page>
