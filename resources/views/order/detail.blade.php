@extends('layout')
@section('title', 'Order Detail')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

<style>
  :root {
    --brand-primary:   #4f46e5;
    --brand-secondary: #7c3aed;
    --brand-accent:    #06b6d4;
    --surface:         #ffffff;
    --bg:              #f1f5f9;
    --text-main:       #0f172a;
    --text-muted:      #64748b;
    --border:          #e2e8f0;
    --success:         #10b981;
    --danger:          #ef4444;
    --warning:         #f59e0b;
    --info:            #3b82f6;
  }

  body { background: var(--bg); font-family: 'Inter', sans-serif; color: var(--text-main); }

  /* ── Page Wrapper ── 
     Uses padding instead of auto-centering so it works inside any sidebar layout.
     The .page-wrapper/.page-content wrapping from the layout already handles the
     sidebar offset — we just add comfortable inner padding here.
  */
  .od-wrapper { max-width: 1080px; margin: 32px auto; padding: 0 24px 60px; }

  /* ── Hero Header ── */
  .od-hero {
    background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 60%, var(--brand-accent) 100%);
    border-radius: 20px;
    padding: 36px 40px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 24px;
    margin-bottom: 32px;
    box-shadow: 0 8px 32px rgba(79,70,229,.25);
  }
  .od-hero .logo-wrap img { height: 64px; border-radius: 12px; background: #fff; padding: 6px; }
  .od-hero-left h1 { font-family: 'Space Grotesk', sans-serif; font-size: 2rem; margin: 0 0 4px; }
  .od-hero-left .meta { opacity: .85; font-size: .9rem; display: flex; gap: 16px; flex-wrap: wrap; }
  .od-hero-left .meta span { display: flex; align-items: center; gap: 6px; }
  .od-company { text-align: right; }
  .od-company ul { list-style: none; padding: 0; margin: 0; font-size: .88rem; line-height: 1.8; }
  .od-company ul li:first-child { font-size: 1.05rem; font-weight: 700; }

  /* ── Section Card ── */
  .od-card {
    background: var(--surface);
    border-radius: 16px;
    border: 1px solid var(--border);
    box-shadow: 0 2px 12px rgba(15,23,42,.06);
    margin-bottom: 28px;
    overflow: hidden;
  }
  .od-card-header {
    padding: 16px 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 700;
    font-size: 1rem;
    border-bottom: 1px solid var(--border);
    background: #fafafa;
  }
  .od-card-header .icon-badge {
    width: 34px; height: 34px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
  }
  .od-card-body { padding: 24px; }

  /* ── Party Cards ── */
  .party-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
  @media(max-width:640px){ .party-grid { grid-template-columns: 1fr; } }

  .party-card {
    border-radius: 14px;
    border: 1px solid var(--border);
    padding: 20px 24px;
  }
  .party-card .party-label {
    font-size: .72rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
    padding: 4px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px;
    margin-bottom: 14px;
  }
  .seller-label { background: #ede9fe; color: var(--brand-primary); }
  .buyer-label  { background: #ccfbf1; color: #047857; }

  .party-name { font-size: 1.15rem; font-weight: 700; margin-bottom: 12px; }
  .party-row { display: flex; gap: 8px; align-items: flex-start; font-size: .88rem; margin-bottom: 8px; color: var(--text-muted); }
  .party-row i { color: var(--brand-accent); margin-top: 2px; flex-shrink: 0; }
  .party-row span { color: var(--text-main); }

  /* ── Products Table ── */
  .od-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
  .od-table thead tr { background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary)); color: #fff; }
  .od-table thead th { padding: 14px 16px; font-weight: 600; white-space: nowrap; border: none; }
  .od-table tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
  .od-table tbody tr:hover { background: #f8f7ff; }
  .od-table tbody td { padding: 13px 16px; vertical-align: middle; }
  .product-name { font-weight: 600; }
  .product-cat  { font-size: .78rem; color: var(--text-muted); }
  .color-dot { display: inline-block; width: 13px; height: 13px; border-radius: 50%; border: 2px solid #c4c4c4; margin-right: 6px; vertical-align: middle; background: #e5e7eb; flex-shrink: 0; }
  .color-cell { display: flex; align-items: center; gap: 4px; }
  .badge-gst { background: #fef3c7; color: #92400e; border-radius: 20px; padding: 3px 10px; font-size: .78rem; font-weight: 600; }

  /* ── Summary Table ── */
  .summary-table { width: 100%; }
  .summary-table tr td { padding: 10px 0; font-size: .9rem; border: none; }
  .summary-table tr td:first-child { color: var(--text-muted); display: flex; align-items: center; gap: 8px; }
  .summary-table tr td:last-child { text-align: right; font-weight: 600; }
  .summary-divider td { border-top: 1px dashed var(--border) !important; padding-top: 14px !important; }
  .grand-row td { font-size: 1.15rem; font-weight: 800; color: var(--brand-primary); }
  .credit-row td { color: var(--success) !important; }

  /* ── Tracking Section ── */
  .tracking-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
  @media(max-width:640px){ .tracking-grid { grid-template-columns: 1fr; } }
  .od-input { border-radius: 10px; border: 1px solid var(--border); padding: 10px 14px; width: 100%; font-size: .9rem; transition: border-color .2s; }
  .od-input:focus { outline: none; border-color: var(--brand-primary); box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
  .od-label { font-size: .82rem; font-weight: 600; margin-bottom: 6px; display: block; color: var(--text-muted); letter-spacing: .04em; text-transform: uppercase; }

  /* ── Action Buttons ── */
  .btn-accept { background: linear-gradient(135deg, #10b981, #059669); border: none; color: #fff; border-radius: 10px; padding: 10px 22px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: opacity .2s; }
  .btn-reject { background: linear-gradient(135deg, #ef4444, #dc2626); border: none; color: #fff; border-radius: 10px; padding: 10px 22px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; transition: opacity .2s; }
  .btn-accept:hover, .btn-reject:hover { opacity: .85; }
  .btn-submit { background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary)); border: none; color: #fff; border-radius: 10px; padding: 12px 32px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: .95rem; }
  .btn-submit:hover { opacity: .9; }

  .file-link { display: inline-flex; align-items: center; gap: 6px; font-size: .82rem; color: var(--brand-primary); text-decoration: none; font-weight: 600; }
  .file-link:hover { text-decoration: underline; }

  /* ── Order Status Timeline ── */
  .status-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
  .status-table thead tr { background: #f1f5f9; }
  .status-table thead th { padding: 12px 16px; font-weight: 600; color: var(--text-muted); font-size: .78rem; text-transform: uppercase; letter-spacing: .07em; border-bottom: 1px solid var(--border); }
  .status-table tbody tr { border-bottom: 1px solid var(--border); }
  .status-table tbody td { padding: 13px 16px; vertical-align: middle; }
  .status-pill { border-radius: 20px; padding: 4px 14px; font-size: .8rem; font-weight: 600; display: inline-block; }

  /* ── Add Status Row ── */
  .add-status-row { background: #f8f7ff; }
  .add-status-row td { padding: 16px; }
  .od-select { border-radius: 10px; border: 1px solid var(--border); padding: 10px 14px; font-size: .9rem; background: #fff; width: 100%; }
  .btn-add-status { background: var(--info); border: none; color: #fff; border-radius: 10px; padding: 10px 20px; font-weight: 600; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px; }

  @media(max-width:768px) {
    .od-hero { padding: 24px 20px; }
    .od-hero-left h1 { font-size: 1.5rem; }
    .od-company { text-align: left; }
  }

  /* Ensure table scrolls on small screens inside sidebar layout */
  .od-table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }

  .hidden { display: none; }
  .alert-danger { background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; color: #991b1b; padding: 12px 16px; margin-bottom: 12px; font-size: .9rem; }
</style>
<div class="page-wrapper">
			<div class="page-content">
<div class="od-wrapper">

  {{-- ── HERO HEADER ── --}}
  <div class="od-hero">
    <div class="od-hero-left">
      <h1><i class="bi bi-receipt-cutoff me-2"></i>Order Detail</h1>
      <div class="meta">
        <span><i class="bi bi-hash"></i> {{ $order->orderno }}</span>
        <span><i class="bi bi-calendar3"></i> {{ date("d M Y") }}</span>
      </div>
    </div>
    <div class="d-flex align-items-center gap-3">
      <div class="logo-wrap">
        <img src="{{ asset('public/img/Apni-Factory-3.png') }}" alt="Logo">
      </div>
      <div class="od-company">
        <ul>
          @foreach($profile as $lt)
            <li @if($lt->attribute=='Name') style="font-weight:700" @endif>
              @if($lt->attribute=='Name') <i class="bi bi-building me-1"></i> @endif
              {{ $lt->value }}
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>

  @php
    $latestStatus = Helper::getstatusoforder($order->orderno);
    $stLower = strtolower($latestStatus ?? '');
    $isPending = in_array($stLower, ['pending', 'wait for confirmation', 'order received', '']);
    $orderCreated = \Carbon\Carbon::parse($order->created_at);
    $deadline = $orderCreated->copy()->addHours(72);
    $diffSecs = max(0, now()->diffInSeconds($deadline, false));
    $isOverdue = now()->greaterThan($deadline);
  @endphp

  @if($isPending)
  {{-- ── 3-DAY SELLER RESPONSE COUNTDOWN BANNER ── --}}
  <div class="card mb-4 border-{{ $isOverdue ? 'danger' : 'warning' }} shadow-sm" style="border-left: 6px solid {{ $isOverdue ? '#dc3545' : '#f59e0b' }}; background: #fffdf5;">
    <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <div style="font-size: 2.2rem; color: {{ $isOverdue ? '#dc3545' : '#f59e0b' }};">
          <i class="bi bi-alarm-fill"></i>
        </div>
        <div>
          <h5 class="mb-1 font-weight-bold text-dark">Seller Response Required (3-Day Limit)</h5>
          <p class="mb-0 text-muted small">
            You must <strong>Accept</strong> or <strong>Reject</strong> this order within 72 hours of placement. Unanswered orders will be automatically cancelled by the system.
          </p>
        </div>
      </div>
      <div class="text-end">
        @if($isOverdue)
          <span class="badge bg-danger fs-6 px-3 py-2">⚠️ Response Window Expired</span>
        @else
          <div class="text-muted small mb-1">Time Remaining:</div>
          <span id="detail-timer" class="badge bg-warning text-dark fs-5 font-monospace px-3 py-2 border border-warning shadow-sm" data-seconds="{{ $diffSecs }}">
            ⏱️ Calculating...
          </span>
        @endif
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const timerEl = document.getElementById('detail-timer');
      if (timerEl) {
        let secs = parseInt(timerEl.getAttribute('data-seconds'), 10);
        function tick() {
          if (secs <= 0) {
            timerEl.className = 'badge bg-danger text-white fs-6 px-3 py-2';
            timerEl.textContent = '⏱️ Expired';
            return;
          }
          secs -= 1;
          const h = Math.floor(secs / 3600);
          const m = Math.floor((secs % 3600) / 60);
          const s = secs % 60;
          timerEl.textContent = `⏱️ ${h}h ${m}m ${s}s`;
        }
        tick();
        setInterval(tick, 1000);
      }
    });
  </script>
  @endif

  {{-- ── SELLER & BUYER ── --}}
  <div class="od-card">
    <div class="od-card-header">
      <span class="icon-badge" style="background:#ede9fe;color:var(--brand-primary)"><i class="bi bi-people-fill"></i></span>
      Party Details
    </div>
    <div class="od-card-body">
      @php $address = json_decode($order->address); @endphp
      <div class="party-grid">

        {{-- Seller --}}
        <div class="party-card">
          <div class="party-label seller-label"><i class="bi bi-shop"></i> Seller</div>
          <div class="party-name">{{ $seller->name }}</div>
          <div class="party-row"><i class="bi bi-briefcase"></i><span>{{ $seller->gst }}</span></div>
          <div class="party-row"><i class="bi bi-envelope"></i><span>{{ $seller->email }}</span></div>
          <div class="party-row"><i class="bi bi-geo-alt"></i><span>{{ $seller->city }}, {{ $seller->state }}</span></div>
          <div class="party-row"><i class="bi bi-mailbox"></i><span>Pincode: {{ $seller->pincode }}</span></div>
        </div>

        {{-- Buyer --}}
        <div class="party-card">
          <div class="party-label buyer-label"><i class="bi bi-person-circle"></i> Buyer</div>
          <div class="party-name">{{ $buyer->name }}</div>
          <div class="party-row"><i class="bi bi-house-door"></i><span>{{ $address->name }}, {{ $address->landmark1 }}, {{ $address->landmark2 }}</span></div>
          <div class="party-row"><i class="bi bi-geo-alt"></i><span>{{ $address->city }}, {{ $address->state }}, {{ $address->country }}</span></div>
          <div class="party-row"><i class="bi bi-mailbox"></i><span>Pincode: {{ $address->pincode }}</span></div>
        </div>

      </div>
    </div>
  </div>

  {{-- ── PRODUCTS TABLE ── --}}
  @php $totl = 0; @endphp
  <div class="od-card">
    <div class="od-card-header">
      <span class="icon-badge" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-box-seam-fill"></i></span>
      Products / Services
    </div>
    <div class="od-table-scroll">
      <table class="od-table">
        <thead>
          <tr>
            <th><i class="bi bi-tag me-1"></i>Item</th>
            <th>HSN</th>
            <th>Color</th>
            <th>Qty (Box+Pcs)</th>
            <th>Rate</th>
            <th>Discount</th>
            <th>After Disc.</th>
            <th>Box Price</th>
            <th>GST</th>
            <th>Sub Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach($orderdetail as $od)
            @php $att = json_decode($od->attribute); @endphp
            @foreach($att as $attri)
            <tr>
              <td>
                <div class="product-name">{{ $od->productname }}</div>
                <div class="product-cat">{{ $od->brdcmpcat }}</div>
              </td>
              <td><strong>{{ $od->hsn }}</strong></td>
              <td>
                <div class="color-cell">
                  <span class="color-dot"></span>
                  <span>{{ $attri->color }}</span>
                </div>
              </td>
              <td>{{ $attri->qty }} <small class="text-muted">({{ $attri->boxpacking }})</small></td>
              <td>₹{{ $attri->prprice }}</td>
              <td>₹{{ $attri->coupon }}</td>
              <td>₹{{ $attri->amntaftrcoupn }}</td>
              <td>₹{{ $attri->unitprice }}</td>
              <td><span class="badge-gst">{{ $attri->tax }}%</span></td>
              <td><strong>₹{{ $attri->totalprice }}</strong></td>
              @php $totl += $attri->totalprice; @endphp
            </tr>
            @endforeach
          @endforeach
        </tbody>
      </table>
    </div>{{-- end od-table-scroll --}}
  </div>{{-- end od-card (products) --}}
  @php $taxdetail = json_decode($order->taxdetail, true); @endphp
  <div class="od-card">
    <div class="od-card-header">
      <span class="icon-badge" style="background:#fef9c3;color:#854d0e"><i class="bi bi-calculator-fill"></i></span>
      Order Summary
    </div>
    <div class="od-card-body">
      <div style="max-width:480px;margin-left:auto">
        <table class="summary-table">
          <tr>
            <td><i class="bi bi-list-ul"></i> Total Amount</td>
            <td>₹{{ $totl }}</td>
          </tr>
          <tr>
            <td><i class="bi bi-tag"></i> Seller Discount</td>
            <td style="color:var(--success)">− ₹{{ $order->sellercouponamount }}</td>
          </tr>
          <tr>
            <td><i class="bi bi-percent"></i> ApniFactory Discount</td>
            <td style="color:var(--success)">− ₹{{ $order->admincouponamount }}</td>
          </tr>
          <tr class="summary-divider">
            <td><i class="bi bi-receipt"></i> Net After Discount</td>
            <td>₹{{ $order->netamount }}</td>
          </tr>
          @foreach($taxdetail as $taxdt)
          <tr>
            <td><i class="bi bi-plus-circle"></i> {{ $taxdt['name'] }}</td>
            <td>₹{{ $taxdt['value'] }}</td>
          </tr>
          @endforeach
          <tr class="summary-divider grand-row">
            <td><i class="bi bi-currency-rupee"></i> Grand Total Payable</td>
            <td>₹{{ $order->grandtotal }}</td>
          </tr>
          <tr class="credit-row">
            <td><i class="bi bi-arrow-return-left"></i> Credit Rate</td>
            <td>{{ $seller->comission }}%</td>
          </tr>
          @php $credit = ($order->grandtotal * (100 - $seller->comission)) / 100; @endphp
          <tr class="credit-row">
            <td><i class="bi bi-wallet2"></i> Credit Amount</td>
            <td>₹{{ $credit }}</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  {{-- ── TRACKING DETAILS ── --}}
   {{-- ── TRACKING DETAILS ── --}}
  @php
    $transname = $text = $transcontact = $lrno = '';
    $ewaybillno = $billty = $invoice = '';
    if (!empty($track)) {
      $transname    = $track->transname;
      $text         = $track->text;
      $transcontact = $track->transcontact;
      $lrno         = $track->lrno;
      $ewaybillno   = $track->invoiceno;
      $billty       = $track->billty;
      $invoice      = $track->invoice;
    }
    // Determine default status based on data
    $st = (!empty($track) && $track->status == 1) ? 'Accept' : 'Reject';
  @endphp

  <div class="od-card">
    <div class="od-card-header">
      <span class="icon-badge" style="background:#dcfce7;color:#166534"><i class="bi bi-truck"></i></span>
      Tracking Details
    </div>
    <div class="od-card-body">

      @foreach($errors->all() as $error)
        <div class="alert-danger mb-3" style="color: #dc3545; font-weight: bold;"><i class="bi bi-exclamation-triangle me-2"></i>{{ $error }}</div>
      @endforeach

      {{-- Combined Tracking Form --}}
      <form id="orderForm" method="post" enctype="multipart/form-data" action="{{ route('order.update', $order->id) }}" onsubmit="return validateOrderForm()">
        <input type="hidden" value="{{ $credit }}" name="credit">
        <input type="hidden" value="trackstatus" name="action">
        @csrf

        {{-- Radio Buttons for Status Selection --}}
        @php
          // Determine if the form is already submitted / locked
          $isLocked = (!empty($track) && $track->invoice != '');
        @endphp

        <div class="mb-4 p-3 bg-light rounded border">
          <label class="od-label d-block mb-2 font-weight-bold">Select Order Action:</label>
          <div class="d-flex gap-4">
            <label class="d-flex align-items-center cursor-pointer" style="gap: 6px;">
              <!-- Added conditional disabled attribute if locked -->
              <input type="radio" name="status" value="Accept" id="radioAccept" 
                     onchange="handleStatusChange('Accept')" 
                     @if($st == 'Accept') checked @endif
                     @if($isLocked) disabled @endif>
              <span class="text-success font-weight-bold"><i class="bi bi-check-circle-fill me-1"></i>Accept Order</span>
            </label>
            
            <label class="d-flex align-items-center cursor-pointer" style="gap: 6px;">
              <!-- Added conditional disabled attribute if locked -->
              <input type="radio" name="status" value="Reject" id="radioReject" 
                     onchange="handleStatusChange('Reject')" 
                     @if($st == 'Reject') checked @endif
                     @if($isLocked) disabled @endif>
              <span class="text-danger font-weight-bold"><i class="bi bi-x-circle-fill me-1"></i>Reject Order</span>
            </label>
          </div>
        </div>

        {{-- Accept Fields Container --}}
        <div id="acceptFieldsContainer" class="mb-3">
          <div class="tracking-grid mb-3">
            <div>
              <label class="od-label"><i class="bi bi-truck me-1"></i>Transport Name <span class="text-danger accept-asterisk">*</span></label>
              <input type="text" class="accept-input od-input" name="transname" value="{{ $transname }}" placeholder="Enter transport name">
            </div>
            <div>
              <label class="od-label"><i class="bi bi-telephone me-1"></i>Transport Contact <span class="text-danger accept-asterisk">*</span></label>
              <input type="number" class="accept-input od-input" name="transcontact" value="{{ $transcontact }}" placeholder="Contact number">
            </div>
            <div>
              <label class="od-label"><i class="bi bi-receipt me-1"></i>L.R. No. <span class="text-danger accept-asterisk">*</span></label>
              <input type="text" class="accept-input od-input" name="lrno" value="{{ $lrno }}" placeholder="Enter L.R. No.">
            </div>
            <div>
              <label class="od-label"><i class="bi bi-file-earmark-text me-1"></i>Invoice No. <span class="text-danger accept-asterisk">*</span></label>
              <input type="text" class="accept-input od-input" name="invoiceno" value="{{ $ewaybillno }}" placeholder="Enter invoice number">
            </div>
            <div>
              <label class="od-label"><i class="bi bi-paperclip me-1"></i>Billty File</label>
              <input type="file" name="builty_file" accept=".pdf,.jpg,.png" class="accept-input od-input">
              @if($st=='Accept' && $billty != '')
                <a href="{{ asset('storage/app/public/'.$billty) }}" target="_blank" class="file-link mt-2 d-inline-flex"><i class="bi bi-download"></i> Download Billty</a>
              @endif
            </div>
            <div>
              <label class="od-label"><i class="bi bi-file-pdf me-1"></i>Invoice File</label>
              <input type="file" name="invoice_file" accept=".pdf,.jpg,.png" class="accept-input od-input">
              @if($st=='Accept' && $invoice != '')
                <a href="{{ asset('storage/app/public/'.$invoice) }}" target="_blank" class="file-link mt-2 d-inline-flex"><i class="bi bi-download"></i> Download Invoice</a>
              @endif
            </div>
          </div>
        </div>

        {{-- Note Field Container (Acts as note for accept, reason note for reject) --}}
        <div class="mb-4">
          <label class="od-label" id="noteLabel"><i class="bi bi-pencil-square me-1"></i>Note to Buyer</label>
          <input type="text" class="od-input" name="anote" id="buyer_note" value="{{ $text }}" placeholder="Any message to the buyer..." @if($isLocked) readonly @endif>
          <div id="note_error_msg" style="color: #dc3545; font-size: 13px; margin-top: 4px; font-weight: bold; display: none;">
            <i class="bi bi-exclamation-circle-fill me-1"></i>Please provide a reason for rejection in this field.
          </div>
        </div>

        @if(empty($track) || $track->invoice == '')
        <button type="submit" class="btn-submit">
          <i class="bi bi-send-fill"></i> Submit Tracking Details
        </button>
        @endif
      </form>
    </div>
  </div>



  {{-- ── ORDER STATUS ── --}}
  <div class="od-card">
    <div class="od-card-header">
      <span class="icon-badge" style="background:#fce7f3;color:#9d174d"><i class="bi bi-activity"></i></span>
      Order Status
    </div>
    <div class="table-responsive">
      <table class="status-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Status</th>
            <th>Message</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          @foreach($status as $key => $lt)
          @php
            $pillColor = match($lt->status) {
              'Delivered'         => 'background:#dcfce7;color:#166534',
              'Order Received'    => 'background:#dbeafe;color:#1d4ed8',
              'Order Processed'   => 'background:#ede9fe;color:#5b21b6',
              'In Transit'        => 'background:#fef3c7;color:#92400e',
              'Out for Delivery'  => 'background:#fce7f3;color:#9d174d',
              'Returned'          => 'background:#fee2e2;color:#991b1b',
              default             => 'background:#f1f5f9;color:#475569',
            };
          @endphp
          <tr>
            <td>{{ $key + 1 }}</td>
            <td><span class="status-pill" style="{{ $pillColor }}">{{ $lt->status }}</span></td>
            <td>{{ $lt->msg }}</td>
            <td><i class="bi bi-calendar3 me-1 text-muted"></i>{{ date("d M Y", strtotime($lt->created_at)) }}</td>
          </tr>
          @endforeach

          {{-- Add Status Row --}}
          <tr class="add-status-row">
            <td colspan="4">
              <form method="post" enctype="multipart/form-data" action="{{ route('order.update', $order->id) }}">
                <input type="hidden" value="{{ $order->id }}" name="orderid">
                <input type="hidden" value="{{ $order->orderno }}" name="orderno">
                <input type="hidden" value="{{ $order->user_id }}" name="userid">
                <input type="hidden" value="{{ $buyer->mobile }}" name="buyermobile">
                <input type="hidden" value="stausupdate" name="action">
                @csrf
                <div class="d-flex gap-3 align-items-end flex-wrap">
                  <div style="min-width:200px">
                    <label class="od-label"><i class="bi bi-list-stars me-1"></i>Status</label>
                    <select class="od-select" name="status">
                      <option value="">— Select Status —</option>
                      @foreach(['Order Received','Order Processed','In Transit','Out for Delivery','Delivered','Pending','Returned','Rejected','Completed'] as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div style="flex:1;min-width:200px">
                    <label class="od-label"><i class="bi bi-chat-text me-1"></i>Message</label>
                    <input type="text" class="od-input" name="msg" placeholder="Enter status message">
                  </div>
                  <div>
                    <button type="submit" class="btn-add-status">
                      <i class="bi bi-plus-lg"></i> Add Status
                    </button>
                  </div>
                </div>
              </form>
            </td>
          </tr>

        </tbody>
      </table>
    </div>
  </div>

</div>{{-- end od-wrapper --}}
</div>
</div>
<script>
    function handleStatusChange(status) {
      const acceptBlock    = document.getElementById('acceptFieldsContainer');
      const noteLabel      = document.getElementById('noteLabel');
      const noteInput      = document.getElementById('buyer_note');
      const acceptInputs   = document.querySelectorAll('.accept-input');
      const errorMsg       = document.getElementById('note_error_msg');
      const asterisks      = document.querySelectorAll('.accept-asterisk');
      
      // Reset errors on status toggle
      errorMsg.style.display = 'none';
      noteInput.style.borderColor = '';

      if (status === 'Accept') {
        // Show accept fields box
        acceptBlock.style.display = 'block';
        
        // Dynamic labels and rules configuration
        noteLabel.innerHTML = '<i class="bi bi-pencil-square me-1"></i>Note to Buyer';
        noteInput.placeholder = 'Any message to the buyer...';
        noteInput.required = false;

        // Force other standard shipping text fields to be required
        asterisks.forEach(el => el.style.display = 'inline');
        acceptInputs.forEach(input => {
          if (input.type !== 'file') {
            input.required = true;
          }
        });
      } else if (status === 'Reject') {
        // Hide accept fields box completely
        acceptBlock.style.display = 'none';

        // Swap note to operate strictly as rejection reason text box
        noteLabel.innerHTML = '<i class="bi bi-chat-left-text me-1"></i>Reason for Rejection <span class="text-danger">*</span>';
        noteInput.placeholder = 'Enter reason for rejection...';
        noteInput.required = true;

        // Disable standard accept fields browser tracking checks
        asterisks.forEach(el => el.style.display = 'none');
        acceptInputs.forEach(input => input.required = false);
      }
    }

    function validateOrderForm() {
      const isRejectChecked = document.getElementById('radioReject').checked;
      const noteInput       = document.getElementById('buyer_note');
      const errorMsg        = document.getElementById('note_error_msg');

      // Hard check for the note input value string payload
      if (isRejectChecked) {
        if (!noteInput.value.trim()) {
          noteInput.style.borderColor = '#dc3545';
          errorMsg.style.display = 'block';
          noteInput.focus();
          return false; // Intercepts page submit stream execution
        }
      }
      return true; // Execution passes onto controller route safely
    }

    // Initialize logic mapping structure instantly on runtime document finish
    document.addEventListener("DOMContentLoaded", function() {
      const initialStatus = document.querySelector('input[name="status"]:checked')?.value || 'Accept';
      handleStatusChange(initialStatus);
    });
</script>

@endsection
