<div class="space-y-4 text-sm">
    <div class="p-3 bg-gray-50 rounded-lg space-y-2">
        <div class="flex justify-between items-center">
            <span class="text-xs text-gray-500 uppercase font-semibold">Action:</span>
            <span class="font-bold text-primary-600">{{ ucwords(str_replace('_', ' ', $record->action_type)) }}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-xs text-gray-500 uppercase font-semibold">Initiated By:</span>
            <span class="font-semibold text-gray-800">{{ $record->actor_name }} ({{ strtoupper($record->actor_role) }})</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-xs text-gray-500 uppercase font-semibold">Timestamp:</span>
            <span class="text-gray-600">{{ $record->created_at->format('d M Y, h:i:s A') }}</span>
        </div>
    </div>

    @if($record->description)
        <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg text-blue-900">
            <strong class="block text-xs uppercase font-bold text-blue-700 mb-1">Details:</strong>
            {{ $record->description }}
        </div>
    @endif

    <div class="grid grid-cols-2 gap-4">
        <div class="p-3 bg-red-50 border border-red-100 rounded-lg">
            <h6 class="text-xs font-bold uppercase text-red-700 mb-2">Previous State / Old Values</h6>
            <pre class="bg-white p-2 rounded border border-red-200 text-xs text-gray-800 overflow-x-auto max-h-60">{{ !empty($record->old_values) ? json_encode($record->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 'None / Not Applicable' }}</pre>
        </div>

        <div class="p-3 bg-green-50 border border-green-100 rounded-lg">
            <h6 class="text-xs font-bold uppercase text-green-700 mb-2">Updated State / New Values</h6>
            <pre class="bg-white p-2 rounded border border-green-200 text-xs text-gray-800 overflow-x-auto max-h-60">{{ !empty($record->new_values) ? json_encode($record->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 'None / Not Applicable' }}</pre>
        </div>
    </div>
</div>
