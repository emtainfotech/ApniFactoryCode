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
            </ul>
        </li>
        
        <!-- Dropdown: Comprehensive Order Tracking Actions -->
        <li>
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon i-orders"><i class="fa-solid fa-cart-shopping"></i></div>
                <div class="menu-title">Orders</div>
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
							
						
							<li class="nav-item dropdown dropdown-large">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span class="alert-count">7</span>
									<i class="fa-solid fa-bell"></i>
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<a href="javascript:;">
										<div class="msg-header">
											<p class="msg-header-title">Notifications</p>
											<p class="msg-header-clear ms-auto">Marks all as read</p>
										</div>
									</a>
									<div class="header-notifications-list">
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="notify bg-light-primary text-primary"><i class="bx bx-group"></i>
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">New Customers<span class="msg-time float-end">14 Sec
												ago</span></h6>
													<p class="msg-info">5 new user registered</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="notify bg-light-danger text-danger"><i class="bx bx-cart-alt"></i>
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">New Orders <span class="msg-time float-end">2 min
												ago</span></h6>
													<p class="msg-info">You have recived new orders</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="notify bg-light-success text-success"><i class="bx bx-file"></i>
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">24 PDF File<span class="msg-time float-end">19 min
												ago</span></h6>
													<p class="msg-info">The pdf files generated</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="notify bg-light-warning text-warning"><i class="bx bx-send"></i>
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">Time Response <span class="msg-time float-end">28 min
												ago</span></h6>
													<p class="msg-info">5.1 min avarage time response</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="notify bg-light-info text-info"><i class="bx bx-home-circle"></i>
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">New Product Approved <span
												class="msg-time float-end">2 hrs ago</span></h6>
													<p class="msg-info">Your new product has approved</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="notify bg-light-danger text-danger"><i class="bx bx-message-detail"></i>
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">New Comments <span class="msg-time float-end">4 hrs
												ago</span></h6>
													<p class="msg-info">New customer comments recived</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="notify bg-light-success text-success"><i class='bx bx-check-square'></i>
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">Your item is shipped <span class="msg-time float-end">5 hrs
												ago</span></h6>
													<p class="msg-info">Successfully shipped your item</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="notify bg-light-primary text-primary"><i class='bx bx-user-pin'></i>
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">New 24 authors<span class="msg-time float-end">1 day
												ago</span></h6>
													<p class="msg-info">24 new authors joined last week</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="notify bg-light-warning text-warning"><i class='bx bx-door-open'></i>
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">Defense Alerts <span class="msg-time float-end">2 weeks
												ago</span></h6>
													<p class="msg-info">45% less alerts last 4 weeks</p>
												</div>
											</div>
										</a>
									</div>
									<a href="javascript:;">
										<div class="text-center msg-footer">View All Notifications</div>
									</a>
								</div>
							</li>
							<li class="nav-item dropdown dropdown-large drop-down-cmt">
								<a class="nav-link "  href="javascript:;" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span class="alert-count">8</span>
									<i class='bx bx-comment'></i>
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<a href="javascript:;">
										<div class="msg-header">
											<p class="msg-header-title">Messages</p>
											<p class="msg-header-clear ms-auto">Marks all as read</p>
										</div>
									</a>
									<div class="header-message-list">
										<a class="dropdown-item" href="javascript:;">
											
										</a>
										<a class="dropdown-item" href="javascript:;">
											
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="user-online">
													<img src="images/avatars/avatar-3.png" class="msg-avatar" alt="user avatar">
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">Oscar Garner <span class="msg-time float-end">8 min
												ago</span></h6>
													<p class="msg-info">Various versions have evolved over</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="user-online">
													<img src="images/avatars/avatar-4.png" class="msg-avatar" alt="user avatar">
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">Katherine Pechon <span class="msg-time float-end">15
												min ago</span></h6>
													<p class="msg-info">Making this the first true generator</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="user-online">
													<img src="images/avatars/avatar-5.png" class="msg-avatar" alt="user avatar">
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">Amelia Doe <span class="msg-time float-end">22 min
												ago</span></h6>
													<p class="msg-info">Duis aute irure dolor in reprehenderit</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="user-online">
													<img src="images/avatars/avatar-6.png" class="msg-avatar" alt="user avatar">
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">Cristina Jhons <span class="msg-time float-end">2 hrs
												ago</span></h6>
													<p class="msg-info">The passage is attributed to an unknown</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="user-online">
													<img src="images/avatars/avatar-7.png" class="msg-avatar" alt="user avatar">
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">James Caviness <span class="msg-time float-end">4 hrs
												ago</span></h6>
													<p class="msg-info">The point of using Lorem</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="user-online">
													<img src="images/avatars/avatar-8.png" class="msg-avatar" alt="user avatar">
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">Peter Costanzo <span class="msg-time float-end">6 hrs
												ago</span></h6>
													<p class="msg-info">It was popularised in the 1960s</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="user-online">
													<img src="images/avatars/avatar-9.png" class="msg-avatar" alt="user avatar">
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">David Buckley <span class="msg-time float-end">2 hrs
												ago</span></h6>
													<p class="msg-info">Various versions have evolved over</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="user-online">
													<img src="images/avatars/avatar-10.png" class="msg-avatar" alt="user avatar">
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">Thomas Wheeler <span class="msg-time float-end">2 days
												ago</span></h6>
													<p class="msg-info">If you are going to use a passage</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="user-online">
													<img src="images/avatars/avatar-11.png" class="msg-avatar" alt="user avatar">
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">Johnny Seitz <span class="msg-time float-end">5 days
												ago</span></h6>
													<p class="msg-info">All the Lorem Ipsum generators</p>
												</div>
											</div>
										</a>
									</div>
									<a href="javascript:;">
										<div class="text-center msg-footer">View All Messages</div>
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