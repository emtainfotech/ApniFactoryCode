<x-filament::page>
    <div class="space-y-6">
        <!-- Banner Info -->
        <div class="p-4 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-xl flex items-start gap-3">
            <svg class="w-6 h-6 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div class="text-sm text-amber-900 dark:text-amber-200">
                <strong>GST Display Price Review Bucket:</strong> When sellers submit products with <em>"GST Included"</em>, they land here for admin verification. You can review factory prices, fine-tune customer display prices, and approve the product live to the marketplace.
            </div>
        </div>

        @forelse($products as $prod)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                <div class="flex flex-wrap justify-between items-center gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex items-center justify-center shrink-0 border border-gray-200">
                            <img src="{{ Helper::imageUrl($prod->image) }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='{{ asset('img/Apni-Factory-3.png') }}';">
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $prod->name }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                    GST Included
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 flex gap-4 mt-0.5">
                                <span>Seller: <strong>{{ $prod->user?->company?->name ?? 'User #' . $prod->user_id }}</strong></span>
                                <span>Category: <strong>{{ $prod->category?->name ?? 'N/A' }}</strong></span>
                                <span>Brand: <strong>{{ $prod->brand?->name ?? 'N/A' }}</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('admin.catalogue.reviews.approve', $prod->id) }}" onsubmit="return confirm('Approve and publish this product live to marketplace?');">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-green-600 hover:bg-green-500 text-white shadow-sm flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Approve & Publish Live
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Product Attributes / SKUs Table with editable Pricing -->
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
                                @foreach($prod->product_attributes as $sku)
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
                                            <select name="attributes[{{ $sku->id }}][is_gst_included]" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-xs py-1 px-2">
                                                <option value="1" {{ $sku->is_gst_included ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ !$sku->is_gst_included ? 'selected' : '' }}>No</option>
                                            </select>
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
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded bg-primary-600 hover:bg-primary-500 text-white">
                            Save Price Changes
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-xl p-8 text-center border border-gray-200 dark:border-gray-700">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h4 class="text-gray-700 dark:text-gray-300 font-bold">No Products Pending GST Review</h4>
                <p class="text-xs text-gray-500 mt-1">All GST-inclusive products have been reviewed and published.</p>
            </div>
        @endforelse
    </div>
</x-filament::page>

