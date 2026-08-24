<div class="space-y-4">
    <div class="grid grid-cols-3 gap-3 p-3 bg-gray-50 rounded-lg text-sm">
        <div>
            <span class="text-gray-500 text-xs block">Adjustment Type</span>
            <span class="font-bold text-gray-800">{{ ucfirst(str_replace('_', ' ', $record->adjustment_type)) }}</span>
        </div>
        <div>
            <span class="text-gray-500 text-xs block">Value Applied</span>
            <span class="font-bold text-primary-600">
                {{ $record->adjustment_type === 'percentage' ? (($record->adjustment_value > 0 ? '+' : '') . $record->adjustment_value . '%') : ('₹' . number_format($record->adjustment_value, 2)) }}
            </span>
        </div>
        <div>
            <span class="text-gray-500 text-xs block">Total Affected SKUs</span>
            <span class="font-bold text-green-600">{{ $record->affected_count }} SKUs</span>
        </div>
    </div>

    <div class="overflow-x-auto max-h-96 border rounded-lg">
        <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-gray-100 uppercase text-gray-600 font-semibold border-b">
                <tr>
                    <th class="p-2">Shade / Color</th>
                    <th class="p-2">Pack Size</th>
                    <th class="p-2 text-right">Old Factory (₹)</th>
                    <th class="p-2 text-right">New Factory (₹)</th>
                    <th class="p-2 text-right">Old Customer (₹)</th>
                    <th class="p-2 text-right">New Customer (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 font-medium">
                            <span class="inline-block w-3 h-3 rounded-full mr-1 align-middle border border-gray-300" style="background-color: {{ $item['hexcode'] ?? '#CCCCCC' }};"></span>
                            {{ $item['shade_name'] ?? ('Shade #' . ($item['shade_id'] ?? '')) }}
                        </td>
                        <td class="p-2 text-gray-600">{{ $item['packing_name'] ?? ('Pack #' . ($item['packing_id'] ?? '')) }}</td>
                        <td class="p-2 text-right text-gray-500 line-through">₹{{ number_format($item['old_seller_price'] ?? 0, 2) }}</td>
                        <td class="p-2 text-right font-bold text-gray-900">₹{{ number_format($item['new_seller_price'] ?? 0, 2) }}</td>
                        <td class="p-2 text-right text-gray-500 line-through">₹{{ number_format($item['old_customer_price'] ?? 0, 2) }}</td>
                        <td class="p-2 text-right font-bold text-primary-600">₹{{ number_format($item['new_customer_price'] ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-gray-400">No SKU items logged in this adjustment snapshot.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
