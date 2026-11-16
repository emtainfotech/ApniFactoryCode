<x-filament::page>
    <div class="space-y-6">
        <!-- Tab Navigation Bar -->
        <div class="flex items-center gap-3 border-b border-gray-200 dark:border-gray-700 pb-3">
            <button type="button" 
                onclick="switchReviewTab('pending')" 
                id="tab-btn-pending"
                class="px-4 py-2 text-sm font-bold rounded-lg transition-colors flex items-center gap-2 bg-primary-600 text-white shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Pending GST Reviews
                <span class="px-2 py-0.5 text-xs rounded-full bg-white/20 text-white font-extrabold">
                    {{ $pendingCount ?? count($products) }}
                </span>
            </button>
            <button type="button" 
                onclick="switchReviewTab('history')" 
                id="tab-btn-history"
                class="px-4 py-2 text-sm font-bold rounded-lg transition-colors flex items-center gap-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Review History & Audit Logs
                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-300 font-extrabold">
                    {{ $historyCount ?? 0 }}
                </span>
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: PENDING GST REVIEWS                 -->
        <!-- ========================================== -->
        <div id="tab-content-pending" class="space-y-6">
            <!-- Banner Info -->
            <div class="p-4 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-xl flex items-start gap-3">
                <svg class="w-6 h-6 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div class="text-sm text-amber-900 dark:text-amber-200">
                    <strong>GST Display Price Review Bucket:</strong> Only product packings with <em>"GST Included = Yes"</em> requiring admin verification are displayed here. You can review factory prices, adjust customer display prices, and approve the product live to the marketplace. Once approved, the record moves to <strong>Review History</strong>.
                </div>
            </div>

            @forelse($products as $prod)
                @php
                    $gstSkus = $prod->product_attributes->filter(fn($sku) => (int)$sku->is_gst_included === 1 || $sku->is_gst_included == '1');
                @endphp
                @if($gstSkus->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                        <div class="flex flex-wrap justify-between items-center gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div class="w-14 h-14 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex items-center justify-center shrink-0 border border-gray-200">
                                    <img src="{{ Helper::imageUrl($prod->image) }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='{{ asset('img/Apni-Factory-3.png') }}';">
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $prod->name }}</h3>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                            {{ $gstSkus->count() }} GST Packings Pending Review
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 flex flex-wrap gap-4 mt-0.5">
                                        <span>Seller: <strong>{{ $prod->user?->company?->name ?? 'User #' . $prod->user_id }}</strong></span>
                                        <span>Category: <strong>{{ $prod->category?->name ?? 'N/A' }}</strong></span>
                                        <span>Brand: <strong>{{ $prod->brand?->name ?? 'N/A' }}</strong></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.catalogue.reviews.approve', $prod->id) }}" onsubmit="return confirm('Approve this product and move it to history?');">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-green-600 hover:bg-green-500 text-white shadow-sm flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Approve & Publish Live
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Product Attributes / SKUs Table with ONLY GST Included = Yes -->
                        <form method="POST" action="{{ route('admin.catalogue.reviews.update-pricing', $prod->id) }}">
                            @csrf
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-semibold uppercase">
                                        <tr>
                                            <th class="px-3 py-2">Packing</th>
                                            <th class="px-3 py-2">Color / Shade</th>
                                            <th class="px-3 py-2">Factory Price (₹)</th>
                                            <th class="px-3 py-2 text-center">GST Incl.</th>
                                            <th class="px-3 py-2">Display Price (₹)</th>
                                            <th class="px-3 py-2">MRP (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach($gstSkus as $sku)
                                            <tr>
                                                <td class="px-3 py-2 font-semibold text-gray-800 dark:text-gray-200">
                                                    {{ $sku->boxPacking?->name ?? 'Pack #' . $sku->quantity }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-600 dark:text-gray-300">
                                                    {{ $sku->shadeCard?->name ?? 'Color #' . $sku->color }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" min="0" 
                                                        name="attributes[{{ $sku->id }}][seller_price]" 
                                                        value="{{ $sku->seller_price }}" 
                                                        class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-xs font-semibold py-1 px-2 w-28">
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                                        Yes
                                                    </span>
                                                    <input type="hidden" name="attributes[{{ $sku->id }}][is_gst_included]" value="1">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" min="0" 
                                                        name="attributes[{{ $sku->id }}][price]" 
                                                        value="{{ $sku->price }}" 
                                                        class="rounded border-primary-400 dark:border-primary-600 dark:bg-gray-700 text-xs font-bold text-primary-600 py-1 px-2 w-28">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" min="0" 
                                                        name="attributes[{{ $sku->id }}][mrp]" 
                                                        value="{{ $sku->mrp }}" 
                                                        placeholder="MRP"
                                                        class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-xs py-1 px-2 w-24">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex justify-end pt-3">
                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded bg-primary-600 hover:bg-primary-500 text-white shadow-sm flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                    Save Price Changes & Log
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-xl p-8 text-center border border-gray-200 dark:border-gray-700">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h4 class="text-gray-700 dark:text-gray-300 font-bold">No Products Pending GST Review</h4>
                    <p class="text-xs text-gray-500 mt-1">All GST-inclusive product packings have been reviewed and approved.</p>
                </div>
            @endforelse
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: REVIEW HISTORY & AUDIT LOGS         -->
        <!-- ========================================== -->
        <div id="tab-content-history" class="space-y-6 hidden">
            <!-- History Banner Info -->
            <div class="p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="text-sm text-blue-900 dark:text-blue-200">
                    <strong>Review History & Audit Trail:</strong> Complete log of all approved GST reviews, admin price adjustments, and marketplace publications. Every action is timestamped with the admin's identity and pricing snapshots.
                </div>
            </div>

            <!-- History Table -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Recent Review History ({{ count($historyLogs) }} Records)
                    </h4>
                    <span class="text-xs text-gray-500">Auto-generated upon review & save</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-semibold uppercase">
                            <tr>
                                <th class="px-4 py-3">Date & Time</th>
                                <th class="px-4 py-3">Product Name</th>
                                <th class="px-4 py-3">Seller / Company</th>
                                <th class="px-4 py-3">Action</th>
                                <th class="px-4 py-3">Reviewed By</th>
                                <th class="px-4 py-3">Notes & Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($historyLogs as $log)
                                @php
                                    $actionName = match($log->action) {
                                        'approved', 'approved_and_published' => 'Approved & Published Live',
                                        'pricing_adjusted_by_admin' => 'Pricing Adjusted by Admin',
                                        'rejected' => 'Rejected',
                                        'corrections_requested' => 'Corrections Requested',
                                        default => ucfirst(str_replace('_', ' ', $log->action)),
                                    };
                                    $actionColor = match($log->action) {
                                        'approved', 'approved_and_published' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                                        'pricing_adjusted_by_admin' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                                        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-500">
                                        {{ $log->created_at ? $log->created_at->format('d M Y, h:i A') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            @if($log->product?->image)
                                                <img src="{{ Helper::imageUrl($log->product->image) }}" class="w-8 h-8 rounded object-cover border shrink-0" onerror="this.onerror=null;this.src='{{ asset('img/Apni-Factory-3.png') }}';">
                                            @endif
                                            <div>
                                                <div class="font-bold text-gray-900 dark:text-white">
                                                    {{ $log->product?->name ?? 'Product #' . $log->product_id }}
                                                </div>
                                                <div class="text-[11px] text-gray-400">
                                                    {{ $log->product?->category?->name ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $log->company?->name ?? $log->seller?->name ?? 'User #' . $log->seller_user_id }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $actionColor }}">
                                            {{ $actionName }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300 font-medium">
                                        {{ $log->reviewer?->name ?? 'Platform Admin' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 max-w-xs truncate" title="{{ $log->notes }}">
                                        {{ $log->notes ?: 'Reviewed and logged.' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        No review history logs recorded yet. Once products are reviewed or pricing is adjusted, logs will appear here.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Approved Products Cards Section -->
            @if(isset($approvedProducts) && $approvedProducts->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Recently Approved GST Products Live in Marketplace ({{ $approvedProducts->count() }})
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($approvedProducts as $aprod)
                            @php
                                $aprodGst = $aprod->product_attributes->filter(fn($s) => (int)$s->is_gst_included === 1 || $s->is_gst_included == '1');
                            @endphp
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-gray-50/50 dark:bg-gray-750 flex flex-col justify-between">
                                <div class="flex items-start gap-3">
                                    <img src="{{ Helper::imageUrl($aprod->image) }}" class="w-12 h-12 rounded object-cover border shrink-0" onerror="this.onerror=null;this.src='{{ asset('img/Apni-Factory-3.png') }}';">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-gray-900 dark:text-white truncate" title="{{ $aprod->name }}">{{ $aprod->name }}</div>
                                        <div class="text-[11px] text-gray-500">{{ $aprod->user?->company?->name ?? 'Seller' }} &bull; {{ $aprod->category?->name ?? 'Category' }}</div>
                                        <span class="inline-flex mt-1 items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                            Active & Live
                                        </span>
                                    </div>
                                </div>
                                @if($aprodGst->count() > 0)
                                    <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 text-[11px] text-gray-600 dark:text-gray-300 space-y-1">
                                        @foreach($aprodGst->take(3) as $gsku)
                                            <div class="flex justify-between items-center">
                                                <span>{{ $gsku->boxPacking?->name ?? 'Pack' }} ({{ $gsku->shadeCard?->name ?? 'Color' }}):</span>
                                                <span class="font-bold text-primary-600">₹{{ number_format($gsku->price, 2) }}</span>
                                            </div>
                                        @endforeach
                                        @if($aprodGst->count() > 3)
                                            <div class="text-[10px] text-gray-400 text-right">+{{ $aprodGst->count() - 3 }} more packings</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- JavaScript for Tab Switching -->
    <script>
        function switchReviewTab(tab) {
            const pendingContent = document.getElementById('tab-content-pending');
            const historyContent = document.getElementById('tab-content-history');
            const pendingBtn = document.getElementById('tab-btn-pending');
            const historyBtn = document.getElementById('tab-btn-history');

            if (!pendingContent || !historyContent || !pendingBtn || !historyBtn) return;

            if (tab === 'pending') {
                pendingContent.classList.remove('hidden');
                historyContent.classList.add('hidden');

                pendingBtn.className = "px-4 py-2 text-sm font-bold rounded-lg transition-colors flex items-center gap-2 bg-primary-600 text-white shadow-sm";
                historyBtn.className = "px-4 py-2 text-sm font-bold rounded-lg transition-colors flex items-center gap-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300";
            } else {
                pendingContent.classList.add('hidden');
                historyContent.classList.remove('hidden');

                historyBtn.className = "px-4 py-2 text-sm font-bold rounded-lg transition-colors flex items-center gap-2 bg-primary-600 text-white shadow-sm";
                pendingBtn.className = "px-4 py-2 text-sm font-bold rounded-lg transition-colors flex items-center gap-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300";
            }
        }
    </script>
</x-filament::page>
