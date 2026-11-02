<?php
$file = 'resources/views/sidebar.blade.php';
$content = file_get_contents($file);

// Replace pending orders query with cached version
$oldQuery = '$pendingOrdersCount = 0;
            if (Auth::check()) {
                $pendingOrdersCount = \DB::table(\'orders\')
                    ->where(\'orders.user_id\', Auth::user()->id)
                    ->join(\'order_status\', \'orders.orderno\', \'=\', \'order_status.order_no\')
                    ->whereIn(\'order_status.status\', [\'pending\', \'Wait For Confirmation\', \'Order Received\'])
                    ->whereIn(\'order_status.id\', function($q) {
                        $q->selectRaw(\'MAX(id)\')->from(\'order_status\')->groupBy(\'order_no\');
                    })
                    ->count();
            }';

$newQuery = '$pendingOrdersCount = 0;
            if (Auth::check()) {
                $sellerUserId = Auth::user()->id;
                $pendingOrdersCount = \Illuminate\Support\Facades\Cache::remember("seller_pending_orders_{$sellerUserId}", 30, function() use ($sellerUserId) {
                    return \DB::table(\'orders\')
                        ->where(\'orders.user_id\', $sellerUserId)
                        ->join(\'order_status\', \'orders.orderno\', \'=\', \'order_status.order_no\')
                        ->whereIn(\'order_status.status\', [\'pending\', \'Wait For Confirmation\', \'Order Received\'])
                        ->whereIn(\'order_status.id\', function($q) {
                            $q->selectRaw(\'MAX(id)\')->from(\'order_status\')->groupBy(\'order_no\');
                        })
                        ->count();
                });
            }';

// Also replace the empty topbar search form with a real working search
$oldSearch = '<form>
							  <input type="text" class="form-control search-control" autofocus placeholder="Type to search..."> <span class="position-absolute top-50 search-show translate-middle-y"><i class="fa-solid fa-magnifying-glass"></i></span>
							   <span class="position-absolute top-50 search-close translate-middle-y"><i class="fa-solid fa-xmark"></i></span>
						    </form>';

$newSearch = '<form action="{{ url(\'seller/product\') }}" method="GET">
							  <input type="text" name="product" class="form-control search-control" placeholder="Search products..." value="{{ request(\'product\') }}"> <span class="position-absolute top-50 search-show translate-middle-y"><i class="fa-solid fa-magnifying-glass"></i></span>
							   <span class="position-absolute top-50 search-close translate-middle-y"><i class="fa-solid fa-xmark"></i></span>
						    </form>';

// Normalize newlines for reliable replacement
$normalizedContent = str_replace("\r\n", "\n", $content);
$normalizedOldQuery = str_replace("\r\n", "\n", $oldQuery);
$normalizedOldSearch = str_replace("\r\n", "\n", $oldSearch);

if (strpos($normalizedContent, $normalizedOldQuery) !== false) {
    $normalizedContent = str_replace($normalizedOldQuery, str_replace("\r\n", "\n", $newQuery), $normalizedContent);
    echo "Query replaced successfully!\n";
} else {
    echo "Query target NOT found!\n";
}

if (strpos($normalizedContent, $normalizedOldSearch) !== false) {
    $normalizedContent = str_replace($normalizedOldSearch, str_replace("\r\n", "\n", $newSearch), $normalizedContent);
    echo "Search form replaced successfully!\n";
} else {
    echo "Search target NOT found!\n";
}

file_put_contents($file, $normalizedContent);
echo "File updated.\n";

