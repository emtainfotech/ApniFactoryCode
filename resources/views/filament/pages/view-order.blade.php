<x-filament::page>
    <style>
         .inline{display: inline-block;}
    </style> 
    <div class="space-y-6">
        <!-- Invoice Details -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Invoice Details</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Order Number</dt>
                        <dd class="mt-1 text-lg font-bold text-gray-900">{{ $record->orderno }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Order Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $record->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Invoice ID</dt>
                        <dd class="mt-1 text-sm text-gray-900">#INV00001-{{ $record->id }}</dd>
                    </div>
                </div>
            </div>
        </div>
        <!-- Customer Information -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Customer Information</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 inline">Customer Name</dt>:
                        <dd class="mt-1 text-sm text-gray-900 inline">{{ $record->customer->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 inline">Seller Name</dt>:
                        <dd class="mt-1 text-sm text-gray-900 inline">{{ $seller->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 inline">Phone</dt>:
                        <dd class="mt-1 text-sm text-gray-900 inline">{{ $record->customer->phone ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 inline">Seller GST</dt>:
                        <dd class="mt-1 text-sm text-gray-900 inline">{{ $seller->gst ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 inline">Email</dt>:
                        <dd class="mt-1 text-sm text-gray-900 inline">{{ $record->customer->email ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 inline">Seller Mobile</dt>:
                        <dd class="mt-1 text-sm text-gray-900 inline">{{ $seller->mobile ?? 'N/A' }}</dd>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Billing Address</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                        @php  $address = json_decode($record->address);        @endphp
                                        @foreach($address as $key=>$ad)
                    <div>
                        <dt class="text-sm font-medium text-gray-500" style="display: inline-block;">{{$key}}</dt>
                        <dd class="mt-1 text-sm text-gray-900" style="display: inline-block;"> : {{ $ad ?? 'N/A' }}</dd>
                    </div>
                                        @endforeach
                    
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Order Summary</h3>
                
                <!-- Financial Summary -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <dt class="text-sm font-medium text-blue-600">Net Amount</dt>
                        <dd class="mt-1 text-xl font-semibold text-blue-900">₹{{ number_format($record->netamount, 2) }}</dd>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <dt class="text-sm font-medium text-gray-600">Tax Amount</dt>
                        <dd class="mt-1 text-xl font-semibold text-gray-900">₹{{ number_format($record->taxamount, 2) }}</dd>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <dt class="text-sm font-medium text-green-600">Grand Total</dt>
                        <dd class="mt-1 text-2xl font-bold text-green-900">₹{{ number_format($record->grandtotal, 2) }}</dd>
                    </div>
                </div>

                <!-- Coupon Details -->
                @if(($record->sellercouponamount && $record->sellercouponamount > 0) || ($record->admincouponamount && $record->admincouponamount > 0))
                <div class="border-t pt-4">
                    <h4 class="font-medium text-gray-900 mb-3">Discount Details</h4>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @if($record->sellercouponamount && $record->sellercouponamount > 0)
                        <div class="bg-orange-50 p-3 rounded-lg">
                            <dt class="text-sm font-medium text-orange-600">Seller Coupon Discount</dt>
                            <dd class="mt-1 text-lg font-semibold text-orange-900">-₹{{ number_format($record->sellercouponamount, 2) }}</dd>
                        </div>
                        @endif
                        
                        @if($record->admincouponamount && $record->admincouponamount > 0)
                        <div class="bg-orange-50 p-3 rounded-lg">
                            <dt class="text-sm font-medium text-orange-600">Admin Coupon Discount</dt>
                            <dd class="mt-1 text-lg font-semibold text-orange-900">-₹{{ number_format($record->admincouponamount, 2) }}</dd>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
<?php /*
                <!-- Tax Details -->
                @if($record->taxdetail)
                <div class="border-t pt-4 mt-4">
                    <h4 class="font-medium text-gray-900 mb-2">Tax Details</h4>
                    <p class="text-sm text-gray-600">{{ $record->taxdetail }}</p>
                </div>
                @endif

                <!-- Admin Coupon Details -->
                @if($record->admincoupondetail)
                <div class="border-t pt-4 mt-4">
                    <h4 class="font-medium text-gray-900 mb-2">Admin Coupon Details</h4>
                    <p class="text-sm text-gray-600">{{ $record->admincoupondetail }}</p>
                </div>
                @endif
                */?>
            </div>
        </div>
  
        <!-- Product Details -->
        <div class="bg-white overflow-hidden shadow rounded-lg">  <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Product Details</h3>
                    <!-- Fallback product details display -->
                    <div class="bg-gray-50 p-4 rounded-lg">
  
                        <!-- Basic product info if available -->
                        @if($orderdetail && $orderdetail->count() > 0)
                        <div class="mt-4">
                            <h4 class="font-medium text-gray-900 mb-2">Order Items:</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item / Details</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty(box + pcs)</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CMP Dis.</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Finalboxprice</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GST</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sub Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                         @foreach($orderdetail as $od)  
                                        @php  $att = json_decode($od->attribute);        @endphp
                                        @foreach($att as $attri)
                                          <tr>
                                          <td  class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                           {{$od->productname}}(  <b>{{$od->hsn}}</b>)
                                          <br>
                                          <small class="text-muted">{{$od->brdcmpcat}} </small>
                                          </td> 
                                              <td>{{$attri->color}}</td>
                                        <td  class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{$attri->qty}} ( {{$attri->boxpacking}} )
                                        </td>
                                        <td  class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> ₹{{ number_format($attri->prprice ?? 0, 2) }}</td>
                                        <td  class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> ₹{{ number_format($attri->coupon ?? 0, 2) }}</td>
                                        <td  class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> ₹{{ number_format($attri->amntaftrcoupn ?? 0, 2) }}</td>
                                        <td  class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> ₹{{ number_format($attri->unitprice ?? 0, 2) }}</td>
                                        <td  class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> ₹{{ number_format($attri->tax ?? 0, 2) }}%</td>
                                        <td  class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> ₹{{ number_format($attri->totalprice ?? 0, 2) }}</td>
                                        
                                         </tr>
                                         @endforeach
                                         @endforeach
                                       
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
            </div>
         
        </div>
 <!-- Order track -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Order Tracking Details</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                        @if(!empty($track))
                                        @foreach($track as $key=>$tr)
                                        @if($key=='id' or $key=='order_id')
                                        @elseif($key=='billty' or $key=='invoice') 
                                        <div>
                        <dt class="text-sm font-medium text-gray-500" style="display: inline-block;">{{$key}}</dt>
                        <dd class="mt-1 text-sm text-gray-900" style="display: inline-block;"> : <a href="{{asset('storage/app/public/'.$tr)}}" target="_blank" >{{ $tr ?? 'N/A' }}</a></dd>
                    </div>
                                        @else
                    <div>
                        <dt class="text-sm font-medium text-gray-500" style="display: inline-block;">{{$key}}</dt>
                        <dd class="mt-1 text-sm text-gray-900" style="display: inline-block;"> : {{ $tr ?? 'N/A' }}</dd>
                    </div>
                                        @endif
                                        @endforeach
                                        @endif
                    
                </div>
            </div>
        </div>
        
 <!--Order status -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Order Status Details</h3>
                <!-- Basic product info if available -->
                        @if($status && $status->count() > 0)
                        <div class="mt-4">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Msg</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Create</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Update</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($status as $orst)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $orst->status ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $orst->msg ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $orst->created_at ?? 0 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $orst->updated_at ?? 0 }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
            </div>
        </div>
        
 <!-- Order Transectioin -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Order Payment Details</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-1">
                    
                        @if($transections)
                                        @foreach($transections as $key=>$paymt)
                                         @if($key=='id' or $key=='order_id' or $key=='customer_id' or $key=='user_id' or $key=='txnmethod')
                                        @else
                    <div>
                        <dt class="text-sm font-medium text-gray-500" style="display: inline-block;">{{$key}}</dt>
                        <dd class="mt-1 text-sm text-gray-900" style="display: inline-block;"> : {{ $paymt ?? 'N/A' }}</dd>
                    </div>                  @endif
                                        @endforeach
                                           @endif
                    
                </div>
            </div>
        </div>
        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('filament.resources.orders.index') }}" 
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Orders
            </a>
            
            @if(method_exists($record, 'canEdit') ? $record->canEdit() : true)
            <a href="{{ route('filament.resources.orders.edit', $record) }}" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Order
            </a>
            @endif
        </div>
    </div>
</x-filament::page>