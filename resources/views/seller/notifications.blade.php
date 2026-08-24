@extends('layout')
@section('title', 'Notifications Center')
@section('content')

<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb Header -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Notifications</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ url('seller/dashboard') }}"><i class="fa-solid fa-house"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Notifications Center</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="markAllNotificationsReadPage()">
                    <i class="fa-solid fa-check-double me-1"></i> Mark All as Read
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-4">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small text-uppercase fw-bold">Total Notifications</p>
                                <h4 class="my-1 text-primary fw-bold">{{ $notifications->total() }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto">
                                <i class="fa-solid fa-bell"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-danger shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary small text-uppercase fw-bold">Unread Alerts</p>
                                <h4 class="my-1 text-danger fw-bold" id="pageUnreadCount">{{ $unreadCount }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('seller.notifications.index') }}" class="row g-2 align-items-center">
                    <div class="col-md-6 col-lg-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Search notification title or body..." value="{{ request('q') }}">
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <select name="filter" class="form-select" onchange="this.form.submit()">
                            <option value="all" {{ request('filter') != 'unread' ? 'selected' : '' }}>All Notifications</option>
                            <option value="unread" {{ request('filter') == 'unread' ? 'selected' : '' }}>Unread Only</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary px-3">Search</button>
                        @if(request('q') || request('filter'))
                            <a href="{{ route('seller.notifications.index') }}" class="btn btn-light ms-1">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Notifications Stream -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                @if($notifications->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($notifications as $notif)
                            @php
                                $isUnread = $notif->msgread == 0;
                                $created = \Carbon\Carbon::parse($notif->created_at);
                                
                                $icon = 'fa-solid fa-bell';
                                $badgeColor = 'bg-primary';
                                $actionUrl = route('order.list');

                                $titleLower = strtolower($notif->title ?? '');
                                $msgLower = strtolower($notif->msg ?? '');

                                if (str_contains($titleLower, 'order') || str_contains($msgLower, 'order')) {
                                    $icon = 'fa-solid fa-cart-shopping';
                                    $badgeColor = 'bg-danger';
                                    preg_match('/#([A-Za-z0-9\-]+)/', ($notif->title . ' ' . $notif->msg), $matches);
                                    if (!empty($matches[1])) {
                                        $actionUrl = route('order.detail', $matches[1]);
                                    }
                                } elseif (str_contains($titleLower, 'expired') || str_contains($titleLower, 'reject')) {
                                    $icon = 'fa-solid fa-clock-rotate-left';
                                    $badgeColor = 'bg-warning text-dark';
                                } elseif (str_contains($titleLower, 'price') || str_contains($titleLower, 'paint')) {
                                    $icon = 'fa-solid fa-paint-roller';
                                    $badgeColor = 'bg-info text-dark';
                                    $actionUrl = route('seller.paint-pricing.index');
                                }
                            @endphp
                            <div class="list-group-item p-3 {{ $isUnread ? 'bg-light bg-opacity-75' : '' }}" style="{{ $isUnread ? 'border-left: 4px solid #0d6efd;' : '' }}" id="notif-row-{{ $notif->id }}">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle {{ $badgeColor }} text-white p-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.1rem; flex-shrink: 0;">
                                        <i class="{{ $icon }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-bold text-dark">
                                                {{ $notif->title }}
                                                @if($isUnread)
                                                    <span class="badge bg-primary ms-2" style="font-size: 0.7rem;">New</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted"><i class="fa-regular fa-clock me-1"></i>{{ $created->format('d M Y, h:i A') }} ({{ $created->diffForHumans() }})</small>
                                        </div>
                                        <p class="mb-2 text-muted">{{ $notif->msg }}</p>
                                        <div class="d-flex gap-2">
                                            @if($actionUrl)
                                                <a href="{{ $actionUrl }}" class="btn btn-sm btn-outline-primary" onclick="markRowRead({{ $notif->id }})">
                                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Details
                                                </a>
                                            @endif
                                            @if($isUnread)
                                                <button type="button" class="btn btn-sm btn-light border" onclick="markRowRead({{ $notif->id }})">
                                                    <i class="fa-solid fa-check me-1"></i> Mark Read
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="p-3">
                        {{ $notifications->withQueryString()->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fa-regular fa-bell-slash fs-1 text-muted d-block mb-3"></i>
                        <h5 class="text-muted fw-bold">No Notifications Found</h5>
                        <p class="text-secondary small">You're all caught up! New order and system alerts will appear here.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<script>
function markRowRead(id) {
    fetch(`/seller/notifications/mark-read/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        const row = document.getElementById(`notif-row-${id}`);
        if (row) {
            row.style.borderLeft = 'none';
            row.classList.remove('bg-light', 'bg-opacity-75');
            const badge = row.querySelector('.badge.bg-primary');
            if (badge) badge.remove();
        }
        if (typeof fetchLiveNotifications === 'function') {
            fetchLiveNotifications();
        }
    });
}

function markAllNotificationsReadPage() {
    fetch("{{ route('seller.notifications.mark-all-read') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        location.reload();
    });
}
</script>

@endsection
