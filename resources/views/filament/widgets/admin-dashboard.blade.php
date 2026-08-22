<div class="space-y-8">

    <!-- ==================== SECTION 1: SALES & ORDERS ==================== -->
    <div class="space-y-3">
        <div class="flex items-center space-x-2 border-b border-gray-200 pb-2 dark:border-gray-700">
            <span class="text-danger-500 font-bold">📊</span>
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">Sales & Orders</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Blue Scheme -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-blue-500 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Sales Volume</p>
                    <h4 class="text-2xl font-black text-gray-800 dark:text-white mt-1">₹{{ number_format($sales_volume, 2) }}</h4>
                </div>
                <div class="p-3 rounded-lg bg-blue-50 text-blue-500 dark:bg-blue-950/50">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Card 2: Green Scheme -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-emerald-500 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Active Orders</p>
                    <h4 class="text-2xl font-black text-gray-800 dark:text-white mt-1">{{ $active_orders }}</h4>
                </div>
                <div class="p-3 rounded-lg bg-emerald-50 text-emerald-500 dark:bg-emerald-950/50">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>

            <!-- Card 3: Orange Scheme -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-amber-500 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pending Orders</p>
                    <h4 class="text-2xl font-black text-gray-800 dark:text-white mt-1">{{ $pending_orders }}</h4>
                </div>
                <div class="p-3 rounded-lg bg-amber-50 text-amber-500 dark:bg-amber-950/50">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Card 4: Red Scheme -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-rose-500 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Returns</p>
                    <h4 class="text-2xl font-black text-gray-800 dark:text-white mt-1">{{ $returns }}</h4>
                </div>
                <div class="p-3 rounded-lg bg-rose-50 text-rose-500 dark:bg-rose-950/50">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-6a4 4 0 00-4-4H4m0 0l3-3m-3 3l3 3"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== SECTION 2: SELLERS & CATALOG ==================== -->
    <div class="space-y-3">
        <div class="flex items-center space-x-2 border-b border-gray-200 pb-2 dark:border-gray-700">
            <span class="text-primary-500 font-bold">🏬</span>
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">Sellers & Catalog</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Card 5: Teal Scheme -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-teal-500 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Sellers</p>
                    <h4 class="text-2xl font-black text-gray-800 dark:text-white mt-1">{{ $total_sellers }}</h4>
                </div>
                <div class="p-3 rounded-lg bg-teal-50 text-teal-500 dark:bg-teal-950/50">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>

            <!-- Card 6: Purple Scheme -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-purple-500 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Brands</p>
                    <h4 class="text-2xl font-black text-gray-800 dark:text-white mt-1">{{ $total_brands }}</h4>
                </div>
                <div class="p-3 rounded-lg bg-purple-50 text-purple-500 dark:bg-purple-950/50">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>

            <!-- Card 7: Fuchsia Scheme -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-fuchsia-500 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Categories</p>
                    <h4 class="text-2xl font-black text-gray-800 dark:text-white mt-1">{{ $total_category }}</h4>
                </div>
                <div class="p-3 rounded-lg bg-fuchsia-50 text-fuchsia-500 dark:bg-fuchsia-950/50">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </div>
            </div>
        </div>
    </div>
</div>
