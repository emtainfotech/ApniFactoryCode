<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - Apni Factory</title>
    <style>
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        color: #222222;
        margin: 0;
        padding: 0;
        font-size: 11px;
    }
    .invoice-container {
        width: 100%;
        background: #FFFFFF;
    }
    /* Top Header Styling */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        /* REMOVED: Top red accent rule line */
        border-bottom: none; 
        margin-bottom: 15px;
    }
    .logo-text {
        font-size: 26px;
        font-weight: bold;
        color: #222222;
        margin: 0;
    }
    .logo-accent {
        color: #B81D24;
    }
    .logo-subtext {
        font-size: 9px;
        color: #555555;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 2px;
    }
    .invoice-title {
        font-size: 24px;
        color: #B81D24;
        text-transform: uppercase;
        font-weight: bold;
        text-align: right;
    }
    /* Details & Block Tables */
    .section-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    .section-table td {
        vertical-align: top;
    }
    
    /* ADDED: Clean box border enclosing titles and texts */
   /* Optimized exclusively for mPDF table nesting stability */
.card-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #CCCCCC;
    background-color: #FFFFFF;
    margin: 0;
    padding: 0;
}
.card-header-cell {
    background-color: #F9F9F9;
    color: #B81D24;
    font-weight: bold;
    font-size: 11px;
    text-transform: uppercase;
    padding: 6px 10px;
    border-bottom: 1px solid #CCCCCC;
}
.card-content-cell {
    padding: 8px 10px;
    line-height: 1.5;
    font-size: 11px;
    vertical-align: top;
}

    .card-content {
        padding: 8px 10px;
        line-height: 1.4;
        font-size: 11px;
    }
    /* Nested Right Side Metadata Table */
    .meta-table {
        width: 100%;
        border-collapse: collapse;
    }
    .meta-table td {
        padding: 3px 0;
        font-size: 11px;
    }
    .meta-label {
        color: #555555;
        font-weight: bold;
    }
    .meta-value {
        text-align: right;
        font-weight: normal;
    }
    /* Core Items Table Layout */
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    .items-table th {
        background-color: #B81D24;
        color: #FFFFFF;
        text-transform: uppercase;
        font-weight: bold;
        font-size: 10px;
        padding: 8px 6px;
        border: 1px solid #B81D24;
        text-align: center;
    }
    .items-table td {
        padding: 8px 6px;
        border: 1px solid #DDDDDD;
        vertical-align: top;
        font-size: 11px;
    }
    .item-desc {
        font-weight: bold;
        margin-bottom: 2px;
    }
    .item-subdesc {
        color: #555555;
        font-size: 10px;
    }
    /* Calculations / Tax Blocks */
    .summary-row {
        padding: 5px 8px;
        border-bottom: 1px dotted #DDDDDD;
    }
    .summary-total-box {
        font-weight: bold;
        font-size: 13px;
        color: #B81D24;
        background-color: #FFF0F0;
        padding: 6px 8px;
    }
    .tax-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }
    .tax-table th, .tax-table td {
        padding: 5px;
        border-bottom: 1px solid #DDDDDD;
        text-align: right;
    }
    .tax-table th {
        font-weight: bold;
        color: #555555;
        background-color: #F9F9F9;
    }
    .amount-words {
        padding: 8px;
        font-size: 10px;
        background-color: #F9F9F9;
    }
    /* Logistics Multi-column Table mapping */
    .logistics-table {
        width: 100%;
        border-collapse: collapse;
    }
    .logistics-table td {
        padding: 6px 8px;
        width: 25%;
    }
    .logistics-label {
        font-size: 9px;
        color: #555555;
        font-weight: bold;
        text-transform: uppercase;
        display: block;
        margin-bottom: 2px;
    }
    .logistics-value {
        font-size: 11px;
    }/* Table wrapping the logistics blocks */
.logistics-card-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #CCCCCC;
    background-color: #FFFFFF;
    margin-bottom: 15px;
}

/* Red highlighted header cell style */
.logistics-header-cell {
    background-color: #F9F9F9;
    color: #B81D24;
    font-weight: bold;
    font-size: 11px;
    text-transform: uppercase;
    padding: 8px 10px;
    border-bottom: 1px solid #CCCCCC;
}

/* Inner borders between rows */
.logistics-table-cell {
    padding: 8px;
    width: 25%;
    vertical-align: top;
    border-bottom: 1px solid #DDDDDD;
}

/* Address sub-block cells */
.address-block-cell {
    width: 50%; 
    padding: 10px; 
    vertical-align: top;
    background-color: #FAFAFA;
}

    /* Footer Details */
    .invoice-footer {
        text-align: center;
        /* REMOVED: Bottom rule separator line */
        border-top: none; 
        padding-top: 12px;
        margin-top: 20px;
        font-size: 10px;
        color: #555555;
    }
    /* Master container layout table */
.summary-container-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    margin-bottom: 15px;
}

/* Individual boxed cards */
.summary-card-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #CCCCCC;
    background-color: #FFFFFF;
}

/* Gray highlighted header cell */
.summary-header-cell {
    background-color: #F9F9F9;
    color: #B81D24;
    font-weight: bold;
    font-size: 11px;
    text-transform: uppercase;
    padding: 6px 10px;
    border-bottom: 1px solid #CCCCCC;
}

/* Row padding inside summaries */
.summary-content-cell {
    padding: 0;
    vertical-align: top;
}

/* Clean up spacing inside summary rows */
.summary-row {
    padding: 6px 8px;
    border-bottom: 1px dotted #DDDDDD;
}
</style>

</head>
<body>

<div class="invoice-container">
    
    <table class="header-table">
        <tr>
            <td>
               <img src="https://panel.apnifactory.co.in/public/img/Apni-Factory-3.png" alt="Logo" width="200px">
                    
            </td>
            <td class="invoice-title">Tax Invoice</td>
        </tr>
    </table>

        <table class="section-table" style="table-layout: fixed; width: 100%; border-collapse: collapse;">
    <tr>
        <!-- Column 1: Seller Details Card -->
        <td style="width: 33%; padding-right: 8px; vertical-align: top;">
            <table class="card-table">
                <tr>
                    <td class="card-header-cell">Seller Details</td>
                </tr>
                <tr>
                    <td class="card-content-cell">
                        <strong>{{$seller_name}}</strong><br/>
                        {{$seller_address}}<br/>
                        India<br/><br/>
                        <strong>GSTIN:</strong> {{$seller_gstin}}
                    </td>
                </tr>
            </table>
        </td>
        <!-- Column 2: Buyer Details Card -->
        <td style="width: 33%; padding-right: 8px; vertical-align: top;">
            <table class="card-table">
                <tr>
                    <td class="card-header-cell">Buyer Details</td>
                </tr>
                <tr>
                    <td class="card-content-cell">
                        <strong>{{$buyer_name}}</strong><br/>
                        {{$buyer_address}}<br/>
                         India<br/><br/>
                        <strong>GSTIN:</strong> {{$buyer_gstin}}
                    </td>
                </tr>
            </table>
        </td>

        <!-- Column 3: Invoice Details Card -->
        <td style="width: 34%; vertical-align: top;">
            <table class="card-table">
                <tr>
                    <td class="card-header-cell">Invoice Details</td>
                </tr>
                <tr>
                    <td class="card-content-cell">
                        <table class="meta-table" style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="meta-label" style="font-size: 11px; color: #555555; font-weight: bold; padding: 2px 0;">Invoice No:</td>
                                <td class="meta-value" style="font-size: 11px; text-align: right; padding: 2px 0;">{{$invoice_no}}</td>
                            </tr>
                            <tr>
                                <td class="meta-label" style="font-size: 11px; color: #555555; font-weight: bold; padding: 2px 0;">Invoice Date:</td>
                                <td class="meta-value" style="font-size: 11px; text-align: right; padding: 2px 0;">{{$invoice_date}}</td>
                            </tr>
                            <tr>
                                <td class="meta-label" style="font-size: 11px; color: #555555; font-weight: bold; padding: 2px 0;">Order ID:</td>
                                <td class="meta-value" style="font-size: 11px; text-align: right; padding: 2px 0;">{{$order_id}}</td>
                            </tr>
                            <tr>
                                <td class="meta-label" style="font-size: 11px; color: #555555; font-weight: bold; padding: 2px 0;">Order Date:</td>
                                <td class="meta-value" style="font-size: 11px; text-align: right; padding: 2px 0;">{{$order_date}}</td>
                            </tr>
                            <tr>
                                <td class="meta-label" style="font-size: 11px; color: #555555; font-weight: bold; padding: 2px 0;">Place of Supply:</td>
                                <td class="meta-value" style="font-size: 11px; text-align: right; padding: 2px 0;">{{$placeofsupply}}</td>
                            </tr>
                            <tr>
                                <td class="meta-label" style="font-size: 11px; color: #555555; font-weight: bold; padding: 2px 0;">Place of Delivery:</td>
                                <td class="meta-value" style="font-size: 11px; text-align: right; padding: 2px 0;">{{$placeofdelivery}}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 38%; text-align: left;">Item / Description</th>
                <th style="width: 13%;">HSN Code<br>Color</th>
                <th style="width: 8%;">Qty (Box)</th>
                <th style="width: 10%; text-align: right;">Rate (₹)</th>
                <th style="width: 10%; text-align: right;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            @php $key=1;@endphp
             @foreach($orderdetail as $od)
            @php $att = json_decode($od->attribute);$totl=0; @endphp
            @foreach($att as $attri)
            <tr>
                <td>{{$key}}</td>
              <td>
                <div class="item-desc">{{ $od->productname }}</div>
                <div class="item-subdesc">{{ $od->brdcmpcat }}</div>
              </td>
              <td style="text-align: center;"><strong>{{ $od->hsn }}</strong><br>{{ $attri->color }}</td>
             
              <td style="text-align: center;">{{ $attri->qty }} <small class="text-muted">({{ $attri->boxpacking }})</small></td>
              <td style="text-align: center;">₹{{ $attri->unitprice }}</td>
              <td style="text-align: center;"><strong>₹{{ $attri->totalprice }}</strong></td>
              @php $totl += $attri->totalprice; $key=$key+1;@endphp
            </tr>
            @endforeach
          @endforeach
            <!--<tr>-->
            <!--    <td style="text-align: center;">1</td>-->
            <!--    <td>-->
            <!--        <div class="item-desc">Floor Cleaner</div>-->
            <!--        <div class="item-subdesc">Mr Shine White (1 Ltr)</div>-->
            <!--    </td>-->
            <!--    <td style="text-align: center;">34025099</td>-->
            <!--    <td style="text-align: center;">18%</td>-->
            <!--    <td style="text-align: center;">1</td>-->
            <!--    <td style="text-align: center;">20</td>-->
            <!--    <td style="text-align: right;">1,580.00</td>-->
            <!--    <td style="text-align: right;">1,580.00</td>-->
            <!--</tr>-->
        </tbody>
    </table>
<table class="summary-container-table">
    <tr>
        <!-- Left Side: Price & Discount Summary Box -->
        <td style="width: 49%; vertical-align: top; padding-right: 12px;">
            <table class="summary-card-table">
                <tr>
                    <td class="summary-header-cell" colspan="2">Price & Discount Summary</td>
                </tr>
                <tr>
                    <td class="summary-content-cell" colspan="2">
                        <table style="width: 100%; border-collapse: collapse;">
                               <tr class="summary-row">
                                <td class="">Sub Total (Before Disc.)</td>
                                <td style="text-align: right; padding: 6px 8px; font-weight: bold;">₹ calculatekro</td>
                            </tr>
                            <tr class="summary-row">
                                <td style="padding: 6px 8px; color: #555555;">(-) Seller Discount</td>
                                <td style="text-align: right; padding: 6px 8px; color: #555555;">₹  {{$sellercouponamount}}</td>
                            </tr>
                            <tr class="summary-row">
                                <td style="padding: 6px 8px; color: #555555;">(-) ApniFactory Discount</td>
                                <td style="text-align: right; padding: 6px 8px; color: #555555;">₹ {{$admincouponamount}}</td>
                            </tr>
                            <tr class="summary-row">
                                <td style="padding: 8px; font-weight: bold;">Net Taxable Value</td>
                                <td style="text-align: right; padding: 8px; font-weight: bold;">₹ {{$nettaxableamount}}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>

        <!-- Right Side: Tax Summary Box -->
        <td style="width: 51%; vertical-align: top;">
            <table class="summary-card-table">
                <tr>
                    <td class="summary-header-cell">Tax Summary</td>
                </tr>
                <tr>
                    <td class="summary-content-cell">
                        <!-- Nested structured tax fields -->
                        <table class="tax-table">
                            <thead>
                                <tr>
                                    <th style="text-align: left;">Particulars</th>
                                    <th>Taxable (₹)</th>
                                    @if($taxis=='igst')
                                    <th>IGST 18%</th>
                                    @else
                                    <th>CGST 9%</th>
                                    <th>SGST 9%</th>                      
                                    @endif
                                    <th>Tax (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="text-align: left; font-weight: bold;">Total</td>
                                    <td>{{$nettaxableamount}}</td>
                                    @if($taxis=='igst')
                                    <td>{{$tax}}</td>
                                    @else
                                    <td></td>
                                    <td>{{$igst}}</td>
                                    @endif
                                    <td style="font-weight: bold;">{{$tax}}</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Grand Total Accent Highlight Bar -->
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="summary-total-box" style="padding: 8px;">GRAND TOTAL (₹)</td>
                                <td class="summary-total-box" style="text-align: right; padding: 8px; font-size: 14px;"> {{$grandtotal}}</td>
                            </tr>
                        </table>

                        <!-- Amount in Words block nested neatly within the frame boundary -->
                        <div class="amount-words">
                            <strong>In Words:</strong> <span style="color: #555555;">{{$amount_in_words}}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

  
    
<!-- Outer structural table container -->
<table class="logistics-card-table">
    <!-- Highlighted Section Header Row -->
    <tr>
        <td colspan="3" class="logistics-header-cell">
             Dispatch & Logistics Details
        </td>
    </tr>

    <!-- First row of parameters -->
    <tr>
        <td class="logistics-table-cell">
            <span class="logistics-label">Dispatch Date</span><br>
            <span class="logistics-value">{{$dispatch_date}}</span>
        </td>
        <td class="logistics-table-cell">
            <span class="logistics-label">Transporter Name</span><br>
            <span class="logistics-value">{{$transporter}}</span>
        </td>
        <td class="logistics-table-cell">
            <span class="logistics-label">LR / GR Number</span><br>
            <span class="logistics-value">{{$lr_no}}</span>
        </td>
    </tr>

    <!-- Second row of parameters -->
    <tr>
        <td class="logistics-table-cell">
            <span class="logistics-label">Transport Contact No.</span><br>
            <span class="logistics-value">{{$transportno}}</span>
        </td>
        <td class="logistics-table-cell">
            <span class="logistics-label">Seller Note</span><br>
            <span class="logistics-value">{{$note}}</span>
        </td>
        <!-- Empty padding cells keeping rows to 4 columns -->
        
    </tr>

    <!-- Nested Addresses layout row split evenly across 2 columns -->
    
</table>
<table class="logistics-card-table">
       <tr>
        <td class="address-block-cell" style="border-right: 1px solid #DDDDDD;">
            <span class="logistics-label">Dispatch From</span><br>
            <span style="font-size: 11px; color: #333333; line-height: 1.4;">{{$dispatchfrom}}</span>
        </td>
        <td  class="address-block-cell">
            <span class="logistics-label">Deliver To</span><br>
            <span style="font-size: 11px; color: #333333; line-height: 1.4;">{{$dispatchto}}</span>
        </td>
    </tr>
</table>
<!-- Terms & Conditions and Signature Section Split Layout -->
<table class="section-table" style="table-layout: fixed; width: 100%; margin-top: 15px;">
    <tr>
        <!-- Left Side: Terms and Conditions -->
        <td style="width: 55%; padding-right: 15px; vertical-align: top;">
            <div class="card-box">
                <div class="card-header">Terms &amp; Conditions</div>
                <div class="card-content" style="font-size: 10px; line-height: 1.5; color: #555555;">
                    1. All disputes are subject to local jurisdiction authorities.<br>
                    2. This invoice acts as a system ledger reconciliation entry.<br>
                    3. Standard settlement rules apply based on platform cycle terms.<br>
                    4. For any billing queries, contact platform support channels.
                </div>
            </div>
        </td>

        <!-- Right Side: Authorized Signature Box -->
        <td style="width: 45%; vertical-align: top;">
            <div class="card-box">
                <div class="card-header" style="text-align: right;"></div>
                <div class="card-content">
                    <!-- Blank lines to create exact physical spacing for signature/stamp placement -->
                    <br><br><br><br>
                    <div style="border-top: 1px dashed #DDDDDD; text-align: center; font-weight: bold; padding-top: 5px; color: #333333;">
                        Authorized Signatory
                    </div>
                </div>
            </div>
        </td>
    </tr>
</table>

    <div class="invoice-footer">
        <p style="margin: 0 0 4px 0;">Thank you for your business!</p>
        <p style="margin: 0 0 6px 0; font-size: 9px; color: #888888;">This is a computer generated invoice generated through ApniFactory Marketplace.</p>
        <p style="margin: 0; font-weight: bold; color: #222222;">For any queries, contact support@apnifactory.com</p>
    </div>

</div>

</body>
</html>             