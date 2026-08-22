@php
    $currentRecord = $getRecord();
    $allBrandsByCompany = [];
    $allCategories = [];

       if ($currentRecord && isset($currentRecord->company_id)) {
        // Fetch records, apply search, and sort in ascending order by name
        $allBrandsByCompany = \App\Models\Brand::with('category')
            ->where('company_id', $currentRecord->company_id)
            ->when(filled($this->brandSearch), function ($query) {
                $query->where('name', 'like', '%' . $this->brandSearch . '%');
            })
            ->orderBy('name', 'asc') // Sorts alphabetically (A-Z)
            ->get();

        $allCategories = \App\Models\Category::orderBy('name')->get();
    }
@endphp

<div class="space-y-4 p-4 bg-gray-50 border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
    
    <!-- Header Block -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center space-x-2">
            <span>All Brands Matching Company:</span>
            <span class="text-primary-600 dark:text-primary-400 font-extrabold">
                {{ $currentRecord?->company?->name ?? 'N/A' }}
            </span>
            <span class="text-xs text-gray-400 font-normal">
                (ID: {{ $currentRecord?->company_id ?? 'N/A' }})
            </span>
        </h3>
        </br>
        <!-- 3. Dynamic Filter Search Bar -->
           <div class="w-full max-w-xs">
        <div class="relative">
            <input type="text" 
                   wire:model.debounce.300ms="brandSearch" 
                   placeholder="Filter by brand name..." 
                   class="w-full rounded-lg border-gray-300 py-1.5 pl-3 pr-8 text-sm bg-white text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
            @if(filled($this->brandSearch))
                <button type="button" 
                        wire:click="$set('brandSearch', '')" 
                        class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>
    </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm text-left">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold">
                <tr>
                    <th class="px-4 py-3">Brand Name</th>
                    <th class="px-4 py-3">Trademark No</th>
                    <th class="px-4 py-3">Category Name</th>
                    <th class="px-4 py-3">Type Status</th>
                    <th class="px-4 py-3">Admin Msg</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                    <th class="px-4 py-3">Brand Image</th>
                </tr>
            </thead>
            
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                @foreach($allBrandsByCompany as $brand)
                    <tr class="{{ $brand->id === $currentRecord->id ? 'bg-amber-50/50 dark:bg-amber-900/20' : '' }}">
                        <td class="px-4 py-3 font-medium">
                            <input type="text" 
                                   wire:model.defer="allBrandsData.{{ $brand->id }}.name" 
                                   class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-primary-500 focus:ring-0 p-1 text-sm">
                        </td>

                        <!-- Trademark No Column -->
                        <td class="px-4 py-3">
                            <input type="text" 
                                   wire:model.defer="allBrandsData.{{ $brand->id }}.trademarkno" 
                                   placeholder="TM Number"
                                   class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-primary-500 focus:ring-0 p-1 text-sm">
                        </td>

                        <td class="px-4 py-3 text-gray-500">
                            {{ $brand->category?->name ?? 'No Category' }}
                        </td>
                        <td class="px-4 py-3">
                            <select wire:model.defer="allBrandsData.{{ $brand->id }}.type" 
                                    class="rounded-lg border-gray-300 py-1 px-2 text-xs bg-white text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                <option value="Processing">Processing</option>
                                <option value="Registered">Registered</option>
                                <option value="Unregistred">Unregistered</option>
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <input type="text" 
                                   wire:model.defer="allBrandsData.{{ $brand->id }}.adminresponse" 
                                   placeholder="Enter admin response..."
                                   class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-primary-500 focus:ring-0 p-1 text-sm">
                        </td>
                        <td class="px-4 py-3 text-right">
                          <!-- Modern Inline Update Icon Button Component -->
<button type="button"
        title="Save & Update Brand Entry"
        wire:click="updateBrandInline({{ $brand->id }})"
        class="inline-flex items-center justify-center p-1.5 text-white bg-success-600 rounded-lg hover:bg-success-500 focus:outline-none focus:ring-2 focus:ring-success-500 transition-colors shadow-sm"
        wire:loading.attr="disabled"
        wire:target="updateBrandInline({{ $brand->id }})">
    
    <!-- Heroicons Outline/Check-Circle Icon SVG -->
    <svg class="w-4 h-4" xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    
</button>
                          <!-- Dynamic Delete Icon Button Component -->
        @if($brand->id !== $currentRecord->id)
            <button type="button"
                    title="Delete Brand Entry"
                    onclick="confirm('Are you absolutely sure you want to permanently delete this brand entry?') || event.stopImmediatePropagation()"
                    wire:click="deleteBrandInline({{ $brand->id }})"
                    class="inline-flex items-center justify-center p-1.5 text-white bg-danger-600 rounded-lg hover:bg-danger-500 focus:outline-none focus:ring-2 focus:ring-danger-500 transition-colors shadow-sm"
                    wire:loading.attr="disabled">
                
                <!-- Modern Trash Icon SVG (Heroicons Outline/trash) -->
                <svg class="w-4 h-4" xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
                
            </button>
        @else
            <!-- Display subtle badge tag if it matches the current open master entry -->
            <span class="text-xs text-amber-600 font-medium dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-1 rounded">
                Editing
            </span>
        @endif
                        </td>
                        
                          <!-- Brand Image Column -->
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-2">
                                @if($brand->image)
                                    <img src="{{ asset('storage/app/public/' . $brand->image) }}" class="w-8 h-8 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                                @endif
                              
                            </div>
                            <div wire:loading wire:target="uploadedLogos.{{ $brand->id }}" class="text-xs text-primary-500 mt-1">
                                Uploading...
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>

            <!-- NEW FOOTER ROW: Create section styled clearly to contrast existing data -->
         <form wire:submit.prevent="addNewBrandInline" enctype="multipart/form-data">  
<tfoot class="bg-blue-50/40 dark:bg-blue-950/20 border-t-2 border-gray-300 dark:border-gray-600">
    <tr>
        <!-- New Brand Name Field -->
        <td class="px-4 py-4">
            <input type="text" 
                   wire:model="newBrandRowData.name"
                   placeholder="Type new brand name..."
                   class="w-full rounded-lg border-gray-300 py-1.5 px-2.5 text-sm bg-white text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-white placeholder-gray-400">
        </td>

        <!-- Dynamic Category Dropdown Selector -->
        <td class="px-4 py-4">
            <select wire:model="newBrandRowData.category_id"
                    class="w-full rounded-lg border-gray-300 py-1.5 px-2 text-sm bg-white text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                <option value="">Select Category</option>
                @foreach($allCategories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </td>

        <!-- New Type Status Selector Dropdown -->
        <td class="px-4 py-4">
            <select wire:model="newBrandRowData.type"
                    class="w-full rounded-lg border-gray-300 py-1.5 px-2 text-sm bg-white text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                <option value="Processing">Processing</option>
                <option value="Registered">Registered</option>
                <option value="Unregistred">Unregistered</option>
            </select>
        </td>

        <!-- New Admin Message Field -->
        <td class="px-4 py-4">
            <input type="text" 
                   wire:model="newBrandRowData.adminresponse"
                   placeholder="Admin notes..."
                   class="w-full rounded-lg border-gray-300 py-1.5 px-2.5 text-sm bg-white text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-white placeholder-gray-400">
        </td>

        <!-- Row Action Create Button -->
        <td class="px-4 py-4 text-right">
          <input type="file" name="imagetoupload"
                                       wire:model="uploadedLogos.image" 
                                       class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300" style="width:60px">
                </td>
                <td>
                              <button type="button"
        wire:click="addNewBrandInline"
        class="inline-flex items-center justify-center px-4 py-1.5 text-xs font-bold text-white bg-success-600 hover:bg-success-500 focus:ring-success-500 rounded-lg focus:outline-none focus:ring-2 transition-colors shadow-sm">
    Add Brand
</button>
        </td>
    </tr>
</tfoot>
</form>
        </table>
    </div>
</div>