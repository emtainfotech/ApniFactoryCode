<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApniFactory - Customer Mobile App Simulator</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #0f172a;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #f8fafc;
            min-height: 100vh;
        }
        .phone-frame {
            max-width: 410px;
            height: 840px;
            background: #ffffff;
            border-radius: 42px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 0 0 12px #1e293b, 0 0 0 14px #334155;
            overflow: hidden;
            margin: 30px auto;
            position: relative;
            display: flex;
            flex-direction: column;
            color: #1e293b;
        }
        .phone-notch {
            width: 140px;
            height: 22px;
            background: #1e293b;
            margin: 0 auto;
            border-bottom-left-radius: 14px;
            border-bottom-right-radius: 14px;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 100;
        }
        .app-header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            padding: 30px 16px 16px 16px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }
        .app-body {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            background: #f8fafc;
        }
        .app-nav-bar {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 8px 0;
            display: flex;
            justify-content: space-around;
        }
        .nav-item-btn {
            text-align: center;
            color: #64748b;
            text-decoration: none;
            font-size: 11px;
            cursor: pointer;
        }
        .nav-item-btn.active {
            color: #2563eb;
            font-weight: bold;
        }
        .product-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 10px;
            margin-bottom: 12px;
            transition: transform 0.2s;
        }
        .alt-seller-card {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="row align-items-center mb-3">
        <div class="col-md-8">
            <h3 class="fw-bold text-white mb-1"><i class="fa-solid fa-mobile-screen text-primary me-2"></i>ApniFactory Mobile App Simulator</h3>
            <p class="text-slate-400 mb-0">Live interactive client demonstration connecting directly to backend REST APIs.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ url('/seller/paint-pricing') }}" class="btn btn-outline-light me-2"><i class="fa-solid fa-paint-roller me-1"></i> Seller Pricing</a>
            <a href="{{ url('/admin') }}" class="btn btn-primary"><i class="fa-solid fa-gauge-high me-1"></i> Admin Panel</a>
        </div>
    </div>

    <div class="row">
        <!-- Left: Simulator Screen -->
        <div class="col-lg-6">
            <div class="phone-frame">
                <div class="phone-notch"></div>

                <!-- Top App Header -->
                <div class="app-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="badge bg-warning text-dark px-2 py-1 small">B2B Manufacturing</span>
                            <h5 class="fw-bold mb-0 mt-1">ApniFactory</h5>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-white text-primary rounded-pill px-3 py-2">
                                <i class="fa-solid fa-wallet me-1"></i> ₹10,000
                            </span>
                        </div>
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" class="form-control border-0" placeholder="Search paints, fabrics, garments...">
                    </div>
                </div>

                <!-- App Screen Body (Tabs) -->
                <div class="app-body">
                    
                    <!-- View Tab: Home & Products -->
                    <div id="tabHome" class="tab-content-view">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">Featured Factory Products</h6>
                            <small class="text-primary fw-bold">View All</small>
                        </div>

                        @foreach($products as $p)
                            <div class="product-card">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 bg-light rounded me-3 text-center" style="width: 60px; height: 60px;">
                                        <i class="fa-solid fa-box text-primary fa-2x mt-1"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-dark">{{ $p->name }}</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted small">Customer Price:</span>
                                                <strong class="text-success d-block">
                                                    ₹{{ number_format($p->attributes->first()->price ?? 350, 2) }}
                                                </strong>
                                            </div>
                                            <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="alert('Item added to cart via API!')">
                                                <i class="fa-solid fa-cart-plus"></i> Buy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- View Tab: Rejected Orders & Alternative Sellers -->
                    <div id="tabAlternatives" class="tab-content-view" style="display: none;">
                        <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            <strong>Order #ORD-9842 Rejected</strong>: Seller out of stock. A 100% refund has been credited.
                        </div>

                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-shield-halved text-success me-1"></i>Top 3 Verified Alternative Sellers:</h6>
                        
                        @forelse($sampleAlternatives as $alt)
                            <div class="alt-seller-card">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div>
                                        <strong class="text-dark">{{ $alt['company_name'] }}</strong>
                                        <span class="badge bg-success ms-1"><i class="fa-solid fa-star text-warning"></i> {{ $alt['rating'] }}</span>
                                        <div class="text-muted small">{{ $alt['city'] }} &bull; MOV: ₹{{ number_format($alt['min_order_value']) }}</div>
                                    </div>
                                    <span class="badge bg-primary px-2 py-1">{{ $alt['matched_product']['starting_price'] }}</span>
                                </div>
                                <div class="mt-2 pt-2 border-top border-success-subtle d-flex justify-content-between align-items-center">
                                    <small class="text-dark fw-bold">{{ $alt['matched_product']['name'] }}</small>
                                    <button class="btn btn-sm btn-success rounded-pill px-3" onclick="alert('Reordered from alternative seller: {{ $alt['company_name'] }}!')">
                                        1-Click Reorder
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small">No rejected orders currently. All active orders are within normal SLA.</p>
                        @endforelse
                    </div>

                    <!-- View Tab: Notifications -->
                    <div id="tabNotifications" class="tab-content-view" style="display: none;">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-bell text-primary me-1"></i>In-App Notifications</h6>
                        @foreach($notifications as $n)
                            <div class="p-3 bg-white rounded-3 border mb-2 shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark small">{{ $n->title }}</strong>
                                    <span class="badge bg-light text-muted" style="font-size: 10px;">{{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}</span>
                                </div>
                                <p class="text-muted small mb-0">{{ $n->msg }}</p>
                            </div>
                        @endforeach
                    </div>

                </div>

                <!-- Bottom Navigation Bar -->
                <div class="app-nav-bar">
                    <div class="nav-item-btn active" onclick="showTab('tabHome', this)">
                        <i class="fa-solid fa-house fa-lg d-block mb-1"></i>
                        Shop
                    </div>
                    <div class="nav-item-btn" onclick="showTab('tabAlternatives', this)">
                        <i class="fa-solid fa-arrows-split-up-and-left fa-lg d-block mb-1"></i>
                        Alternatives
                    </div>
                    <div class="nav-item-btn" onclick="showTab('tabNotifications', this)">
                        <i class="fa-solid fa-bell fa-lg d-block mb-1"></i>
                        Alerts
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Feature Overview & API Integration Guide -->
        <div class="col-lg-6">
            <div class="card bg-dark text-white border-secondary mb-4 p-4 shadow">
                <h5 class="fw-bold text-warning mb-3"><i class="fa-solid fa-code me-2"></i>Mobile Client API Integration Status</h5>
                
                <div class="mb-3 p-3 bg-slate-800 rounded border border-slate-700">
                    <h6 class="text-primary fw-bold mb-1">1. Alternative Sellers Recommendations</h6>
                    <code>GET /api/customer/orders/{id}/alternative-sellers</code>
                    <p class="text-slate-300 small mt-1 mb-0">Returns scored replacement sellers with verified factory pricing, ratings, and instant reorder links.</p>
                </div>

                <div class="mb-3 p-3 bg-slate-800 rounded border border-slate-700">
                    <h6 class="text-success fw-bold mb-1">2. Two-Price Matrix Engine</h6>
                    <code>GET /api/seller/paint-families/{id}/pricing</code>
                    <p class="text-slate-300 small mt-1 mb-0">Returns live matrix of all SKU pack sizes, color shades, Factory Price, and platform customer markup.</p>
                </div>

                <div class="mb-3 p-3 bg-slate-800 rounded border border-slate-700">
                    <h6 class="text-info fw-bold mb-1">3. Automated Refund & Wallet Service</h6>
                    <code>POST /api/payment/webhook</code> &bull; <code>PaymentRefundService::processRefund()</code>
                    <p class="text-slate-300 small mt-1 mb-0">Automatically processes buyer refunds on order rejection or 72-hour SLA expiration.</p>
                </div>

                <div class="p-3 bg-slate-800 rounded border border-slate-700">
                    <h6 class="text-warning fw-bold mb-1">4. Multi-Channel Notification Router</h6>
                    <code>App\Services\NotificationService::send()</code>
                    <p class="text-slate-300 small mt-1 mb-0">Unified dispatch across Database inbox, FCM Mobile Push, WhatsApp Cloud API, and Transactional Email.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showTab(tabId, el) {
        document.querySelectorAll('.tab-content-view').forEach(t => t.style.display = 'none');
        document.getElementById(tabId).style.display = 'block';
        document.querySelectorAll('.nav-item-btn').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
    }
</script>

</body>
</html>
