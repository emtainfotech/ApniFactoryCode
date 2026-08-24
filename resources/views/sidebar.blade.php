<!--sidebar wrapper -->
	<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <img src="{{ asset('public/img/Apni-Factory-3.png')}}" class="logo-icon" alt="logo icon">
        </div>
        <div class="toggle-icon ms-auto">
            <i class="fa-solid fa-angle-right"></i>
        </div>
    </div>
    
    <!-- Navigation Tree Links Hierarchy -->
    <ul class="metismenu" id="menu">
        
        <!-- Core Dashboard Anchor -->
        <li>
            <a href="{{url('seller/dashboard')}}">
                <div class="parent-icon i-dashboard"><i class="fa-solid fa-chart-pie"></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>
        
        <!-- Category Structures -->
        <li>
            <a href="{{url('seller/brand')}}">
                <div class="parent-icon i-brand"><i class="fa-solid fa-copyright"></i></div>
                <div class="menu-title">Brand</div>
            </a>
        </li>
        <li>
            <a href="{{url('seller/category')}}">
                <div class="parent-icon i-category"><i class="fa-solid fa-layer-group"></i></div>
                <div class="menu-title">Category</div>
            </a>
        </li>
        
        <!-- Dropdown: Products Tree Layout -->
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon i-products"><i class="fa-solid fa-box-open"></i></div>
                <div class="menu-title">Products</div>
            </a>
            <ul>
                <li><a href="{{url('seller/product/add')}}"><i class="fa-solid fa-plus-circle me-2"></i>Create New</a></li>
                <li><a href="{{url('seller/product')}}"><i class="fa-solid fa-list-ul me-2"></i>List All</a></li>
                <li><a href="{{route('seller.paint-pricing.index')}}"><i class="fa-solid fa-paint-roller me-2" style="color: #6f42c1;"></i>Paint Pricing Manager</a></li>
            </ul>
        </li>
        
        <!-- Dropdown: Comprehensive Order Tracking Actions -->
        @php
            $pendingOrdersCount = 0;
            if (Auth::check()) {
                $pendingOrdersCount = \DB::table('orders')
                    ->where('orders.user_id', Auth::user()->id)
                    ->join('order_status', 'orders.orderno', '=', 'order_status.order_no')
                    ->whereIn('order_status.status', ['pending', 'Wait For Confirmation', 'Order Received'])
                    ->whereIn('order_status.id', function($q) {
                        $q->selectRaw('MAX(id)')->from('order_status')->groupBy('order_no');
                    })
                    ->count();
            }
        @endphp
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon i-orders"><i class="fa-solid fa-cart-shopping"></i></div>
                <div class="menu-title">Orders @if($pendingOrdersCount > 0)<span class="badge bg-danger rounded-pill ms-1 font-monospace" style="font-size: 0.75rem;">{{ $pendingOrdersCount }}</span>@endif</div>
            </a>
            <ul>
                <li><a href="{{route('order.list')}}"><i class="fa-solid fa-boxes-stacked me-2"></i>All Orders</a></li>
                <li><a href="{{route('order.list','status=Order Received')}}"><i class="fa-solid fa-bell me-2" style="color: #ffc107;"></i>Order Received</a></li>
                <li><a href="{{route('order.list','status=Order Processed')}}"><i class="fa-solid fa-gears me-2" style="color: #0dcaf0;"></i>Order Processed</a></li>
                <li><a href="{{route('order.list','status=In Transit')}}"><i class="fa-solid fa-truck me-2" style="color: #fd7e14;"></i>In Transit</a></li>
                <li><a href="{{route('order.list','status=Out for Delivery')}}"><i class="fa-solid fa-truck-ramp-box me-2" style="color: #6f42c1;"></i>Out for Delivery</a></li>
                <li><a href="{{route('order.list','status=Delivered')}}"><i class="fa-solid fa-circle-check me-2" style="color: #198754;"></i>Delivered</a></li>
                <li><a href="{{route('order.list','status=Pending')}}"><i class="fa-solid fa-clock-rotate-left me-2" style="color: #6c757d;"></i>Pending</a></li>
                <li><a href="{{route('order.list','status=Returned')}}"><i class="fa-solid fa-arrow-rotate-left me-2" style="color: #dc3545;"></i>Returned</a></li>
            </ul>
        </li>
        
        <!-- Marketing Items -->
        <li>
            <a href="{{url('seller/coupon')}}">
                <div class="parent-icon i-coupon"><i class="fa-solid fa-ticket-simple"></i></div>
                <div class="menu-title">Coupon</div>
            </a>
        </li>
        <li>
            <a href="{{url('seller/advertisement')}}">
                <div class="parent-icon i-ad"><i class="fa-solid fa-rectangle-ad"></i></div>
                <div class="menu-title">Advertisement</div>
            </a>
        </li>
        <li>
            <a href="{{url('seller/managecolor')}}">
                <div class="parent-icon i-color"><i class="fa-solid fa-palette"></i></div>
                <div class="menu-title">Manage Color</div>
            </a>
        </li>
        
        <!-- Financial Structures -->
        <li>
            <a href="{{url('seller/creditnotes')}}">
                <div class="parent-icon i-credit"><i class="fa-solid fa-receipt"></i></div>
                <div class="menu-title">Credit Notes</div>
            </a>
        </li>
        <li>
            <a href="{{url('seller/bank-detail')}}">
                <div class="parent-icon i-bank"><i class="fa-solid fa-building-columns"></i></div>
                <div class="menu-title">Bank Detail</div>
            </a>
        </li>
        <li>
            <a href="{{url('seller/transection')}}">
                <div class="parent-icon i-payment"><i class="fa-solid fa-credit-card"></i></div>
                <div class="menu-title">Payment</div>
            </a>
        </li>
        
        <!-- Logistics Restrictions & Help -->
        <li>
            <a href="{{url('seller/citynotallow')}}">
                <div class="parent-icon i-restrict"><i class="fa-solid fa-city"></i></div>
                <div class="menu-title">Restricted City</div>
            </a>
        </li>
        <li>
            <a href="{{url('seller/ticketsupport')}}">
                <div class="parent-icon i-support"><i class="fa-solid fa-headset"></i></div>
                <div class="menu-title">Ticket Support</div>
            </a>
        </li>
        <li>
            <a href="{{url('seller/help')}}">
                <div class="parent-icon i-help"><i class="fa-solid fa-circle-question"></i></div>
                <div class="menu-title">Help</div>
            </a>
        </li>
        
    </ul>
</div>
	<!--end sidebar wrapper -->
		 <header>
			<div class="topbar d-flex align-items-center">
				<nav class="navbar navbar-expand gap-3">
					<div class="mobile-toggle-menu"><i class="fa-solid fa-bars"></i>
					</div>
					<div class="search-bar flex-grow-1">
						<div class="position-relative search-bar-box">
							<form>
							  <input type="text" class="form-control search-control" autofocus placeholder="Type to search..."> <span class="position-absolute top-50 search-show translate-middle-y"><i class="fa-solid fa-magnifying-glass"></i></span>
							   <span class="position-absolute top-50 search-close translate-middle-y"><i class="fa-solid fa-xmark"></i></span>
						    </form>
						</div>
					</div>
					<div class="top-menu ms-auto">
						<ul class="navbar-nav align-items-center gap-1">
							<li class="nav-item mobile-search-icon">
								<a class="nav-link" href="javascript:;"><i class="fa-solid fa-magnifying-glass"></i>
								</a>
							</li>
							
						
							<!-- Real-Time Seller Notifications Dropdown -->
							<li class="nav-item dropdown dropdown-large" id="sellerNotificationDropdownWrap">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificationBellBtn">
									<span class="alert-count bg-danger text-white rounded-pill font-monospace" id="sellerAlertCount" style="display: none; font-size: 0.72rem; min-width: 18px; text-align: center;">0</span>
									<i class="fa-solid fa-bell"></i>
								</a>
								<div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="width: 380px; max-width: 95vw; border-radius: 12px;">
									<div class="msg-header d-flex align-items-center py-2 px-3 border-bottom bg-light">
										<h6 class="msg-header-title mb-0 font-weight-bold text-dark"><i class="fa-solid fa-bell me-1 text-warning"></i> Notifications</h6>
										<a href="javascript:void(0);" class="msg-header-clear ms-auto text-primary small text-decoration-none font-weight-bold" id="btnMarkAllRead" onclick="markAllNotificationsRead(event)">
											<i class="fa-solid fa-check-double me-1"></i>Mark all as read
										</a>
									</div>
									<div class="header-notifications-list py-1" id="sellerNotificationsList" style="max-height: 380px; overflow-y: auto;">
										<div class="text-center py-4 text-muted small" id="notificationsLoadingState">
											<i class="fa-solid fa-spinner fa-spin me-1"></i> Loading notifications...
										</div>
									</div>
									<a href="{{ route('seller.notifications.index') }}" class="text-decoration-none">
										<div class="text-center msg-footer py-2 bg-light border-top font-weight-bold text-primary small">
											View All Notifications <i class="fa-solid fa-arrow-right ms-1"></i>
										</div>
									</a>
								</div>
							</li>

<script>
let lastNotificationCount = null;

function fetchLiveNotifications() {
    fetch("{{ route('seller.notifications.live') }}", {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const countBadge = document.getElementById('sellerAlertCount');
        const listContainer = document.getElementById('sellerNotificationsList');
        
        if (!countBadge || !listContainer) return;

        // Update badge count
        if (data.count > 0) {
            countBadge.textContent = data.count > 99 ? '99+' : data.count;
            countBadge.style.display = 'inline-block';
        } else {
            countBadge.style.display = 'none';
        }

        // Check if new incoming notification arrived to trigger sound/alert
        if (lastNotificationCount !== null && data.count > lastNotificationCount) {
            showNotificationToast("New Order/Notification Received! Check your notifications.");
        }
        lastNotificationCount = data.count;

        // Render notifications list
        if (!data.notifications || data.notifications.length === 0) {
            listContainer.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fa-regular fa-bell-slash fs-3 d-block mb-2 text-secondary"></i>
                    <p class="mb-0 small">No notifications found</p>
                </div>
            `;
            return;
        }

        let html = '';
        data.notifications.forEach(item => {
            const unreadStyle = item.msgread === 0 ? 'background-color: #f0f7ff; border-left: 3px solid #0d6efd;' : '';
            const unreadDot = item.msgread === 0 ? '<span class="badge bg-primary rounded-circle p-1 ms-1" style="width:7px;height:7px;display:inline-block;" title="Unread"></span>' : '';
            
            html += `
                <a class="dropdown-item py-2 px-3 border-bottom" href="javascript:void(0);" onclick="handleNotificationClick(${item.id}, '${item.target_url}')" style="${unreadStyle} transition: background 0.2s;">
                    <div class="d-flex align-items-start gap-2">
                        <div class="notify ${item.bg_class} rounded-circle d-flex align-items-center justify-content-center mt-1" style="width: 36px; height: 36px; font-size: 1.1rem; flex-shrink: 0;">
                            <i class="${item.icon}"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="msg-name mb-0 text-truncate font-weight-bold" style="font-size: 0.85rem;">
                                    ${item.title} ${unreadDot}
                                </h6>
                                <span class="msg-time text-muted ms-2" style="font-size: 0.72rem; white-space: nowrap;">${item.time_ago}</span>
                            </div>
                            <p class="msg-info mb-0 text-muted small text-truncate" style="font-size: 0.78rem;">${item.msg}</p>
                        </div>
                    </div>
                </a>
            `;
        });

        listContainer.innerHTML = html;
    })
    .catch(err => {
        console.error("Live notifications fetch error:", err);
    });
}

function handleNotificationClick(id, targetUrl) {
    // Mark as read via AJAX then navigate
    fetch(`/seller/notifications/mark-read/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .finally(() => {
        window.location.href = targetUrl;
    });
}

function markAllNotificationsRead(event) {
    if (event) event.stopPropagation();
    
    fetch("{{ route('seller.notifications.mark-all-read') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(res => {
        fetchLiveNotifications();
    })
    .catch(err => console.error("Error marking all read:", err));
}

function showNotificationToast(msg) {
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-0 end-0 p-3';
    toast.style.zIndex = '99999';
    toast.innerHTML = `
        <div class="toast show align-items-center text-white bg-primary border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body font-weight-bold">
                    <i class="fa-solid fa-bell me-2"></i> ${msg}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close" onclick="this.closest('.position-fixed').remove()"></button>
            </div>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 6000);
}

// Initial fetch on DOM load and recurring 10-second polling
document.addEventListener('DOMContentLoaded', function() {
    fetchLiveNotifications();
    setInterval(fetchLiveNotifications, 10000);
});
</script>
							<!-- Support & Help Tickets Dropdown -->
							<li class="nav-item dropdown dropdown-large drop-down-cmt">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Support & Help Tickets">
									@php
										$sellerTickets = \Illuminate\Support\Facades\Auth::check() 
											? \Illuminate\Support\Facades\DB::table('tickets')->where('user_id', \Illuminate\Support\Facades\Auth::id())->latest('id')->limit(5)->get()
											: collect();
										$pendingTicketsCount = \Illuminate\Support\Facades\Auth::check()
											? \Illuminate\Support\Facades\DB::table('tickets')->where('user_id', \Illuminate\Support\Facades\Auth::id())->where('status', 'Pending')->count()
											: 0;
									@endphp
									@if($pendingTicketsCount > 0)
										<span class="alert-count bg-warning text-dark font-monospace" style="font-size: 0.72rem; min-width: 18px; text-align: center;">{{ $pendingTicketsCount }}</span>
									@endif
									<i class='fa-solid fa-headset'></i>
								</a>
								<div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="width: 340px; max-width: 95vw; border-radius: 12px;">
									<div class="msg-header d-flex align-items-center py-2 px-3 border-bottom bg-light">
										<h6 class="msg-header-title mb-0 font-weight-bold text-dark"><i class="fa-solid fa-headset me-1 text-primary"></i> Support Tickets</h6>
										<a href="{{ url('seller/ticketsupport') }}" class="msg-header-clear ms-auto text-primary small text-decoration-none font-weight-bold">
											<i class="fa-solid fa-plus me-1"></i>New Ticket
										</a>
									</div>
									<div class="header-message-list py-1" style="max-height: 320px; overflow-y: auto;">
										@forelse($sellerTickets as $ticket)
											<a class="dropdown-item py-2 px-3 border-bottom" href="{{ url('seller/ticketsupport') }}">
												<div class="d-flex align-items-center gap-2">
													<div class="rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 1rem; flex-shrink: 0;">
														<i class="fa-solid fa-ticket"></i>
													</div>
													<div class="flex-grow-1 overflow-hidden">
														<div class="d-flex justify-content-between align-items-center mb-1">
															<h6 class="msg-name mb-0 text-truncate font-weight-bold" style="font-size: 0.84rem;">
																#{{ $ticket->id }} {{ $ticket->topic }}
															</h6>
															<span class="badge {{ $ticket->status == 'Completed' ? 'bg-success' : ($ticket->status == 'Reject' ? 'bg-danger' : 'bg-warning text-dark') }}" style="font-size: 0.65rem;">
																{{ $ticket->status }}
															</span>
														</div>
														<p class="msg-info mb-0 text-muted small text-truncate" style="font-size: 0.78rem;">{{ $ticket->msg }}</p>
													</div>
												</div>
											</a>
										@empty
											<div class="text-center py-4 text-muted">
												<i class="fa-solid fa-circle-check fs-3 d-block mb-2 text-success"></i>
												<p class="mb-0 small">No active support tickets</p>
											</div>
										@endforelse
									</div>
									<a href="{{ url('seller/ticketsupport') }}" class="text-decoration-none">
										<div class="text-center msg-footer py-2 bg-light border-top font-weight-bold text-primary small">
											View All Tickets <i class="fa-solid fa-arrow-right ms-1"></i>
										</div>
									</a>
								</div>
							</li>
						</ul>
					</div>
					
					<div class="user-box dropdown px-3">
					    	<div class="profile-image-upload-container" style="cursor: pointer; position: relative;">
            <img src="{{ auth()->user()->profilephoto ? asset('storage/app/public/' . auth()->user()->profilephoto) : asset('public/img/Apni-Factory-3.png') }}" 
                 class="logo-icon" 
                 id="sidebarProfileImage" 
                 alt="logo icon" 
                 title="Click to change profile picture" style="height:60px;">
            
            <input type="file" id="profileImageInput" accept="image/*" style="display: none;">
        </div>
						<a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<!--<img src="{{asset('public/img/avatar-2.png')}}" class="user-img" alt="user avatar">-->
							<div class="user-info ps-3">
								<p class="user-name mb-0">{{Auth::user()->name}}</p>
								<p class="designattion mb-0">{{Auth::user()->email}}</p>
							</div>
						</a>
						<ul class="dropdown-menu dropdown-menu-end">
							<li><a class="dropdown-item" href="profile"><i class="bx bx-user"></i><span>Profile</span></a>
							</li>
							
							<li>
								<div class="dropdown-divider mb-0"></div>
							</li>
							<li>
							     <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
							</li>
						</ul>
					</div>
				</nav>
			</div>
		</header>
	
		<!--end header -->
		<script>
document.getElementById('sidebarProfileImage').addEventListener('click', function() {
    // 1. Trigger the hidden file browser
    document.getElementById('profileImageInput').click();
});

document.getElementById('profileImageInput').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        let file = this.files[0];
        let formData = new FormData();
        formData.append('profile_picture', file);
        
        // Include CSRF Token for Laravel security
        formData.append('_token', '{{ csrf_token() }}');

        // 2. Send the file via AJAX to Laravel backend
        fetch("{{ route('profile.update.image') }}", {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // 3. Dynamically update the image on the screen on success
                document.getElementById('sidebarProfileImage').src = data.image_url;
                alert('Profile picture updated successfully!');
            } else {
                alert('Upload failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong during the upload.');
        });
    }
});
</script>