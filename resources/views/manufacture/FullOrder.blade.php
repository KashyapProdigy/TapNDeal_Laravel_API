@extends('manuLayout')

@section('title')
    Orders Details
@stop

@section('pageTitle')
    Orders Details
@stop

@section('content')
<style>
.invoice-title h2, .invoice-title h3 {
    display: inline-block;
}

.table > tbody > tr > .no-line {
    border-top: none;
}

.table > thead > tr > .no-line {
    border-bottom: none;
}

.table > tbody > tr > .thick-line {
    border-top: 2px solid;
}
</style>
<hr>
<div class="container">
    <div class="row d-flex ">
        <div class="col-6">
    				<address>
    				<h3><strong>Customer Information</strong></h3><hr><br>
    					<strong> Name:</strong>{{$ord->name}}<br>
                        <strong> Mobile:</strong>{{$ord->mobile}}<br>
                        <strong> Email:</strong>{{$ord->email}}<br>
    				</address>
    		
    	</div>
        @php $a=\DB::table('users')->where('ref_code',$ord->agent_reference)->first();@endphp
		@if($a)
        <div class="col-6 text-right">
    				<address>
    				<h3><strong>Agent Information</strong></h3><hr><br>
                    <strong> Name:</strong>{{$a->name}}<br>
                        <strong> Mobile:</strong>{{$a->mobile}}<br>
                        <strong> Email:</strong>{{$a->email}}<br>
    				</address>
    		
    	</div>
		@else
		@endif
    </div>
    
    <div class="row">
    	<div class="col-md-12">
    		<div class="panel panel-default">
    			<div class="panel-heading">
    				<h3 class="panel-title"><strong>Order summary</strong></h3>
    			</div>
    			<div class="panel-body">
    				<div class="table-responsive">
    					<table class="table table-condensed">
    						<thead>
                                <tr>
        							<td><strong>Item</strong></td>
        							<td class="text-center"><strong>Product name</strong></td>
									<td class="text-center"><strong>Price</strong></td>
									<td class="text-center"><strong>Category</strong></td>
        							<td class="text-center"><strong>Quantity</strong></td>
        							<td class="text-right"><strong>Totals</strong></td>
                                </tr>
    						</thead>
    						<tbody>
    							@php $order=json_decode($ord->products);$i=1;@endphp
    							@foreach($order as $o)
                                <tr>
    								<td>{{$i++}}</td>
    								<td class="text-center">{{$o->product_name}}</td>
									<td class="text-center">{{$o->product_price}}</td>
									<td class="text-center">{{$o->category}}</td>
    								<td class="text-center">{{$o->qty}}</td>
    								<td class="text-right">&#x20b9;{{$o->total_price}}</td>
    							</tr>
                                @endforeach
    							<tr>
    								<td class="thick-line"></td>
    								<td class="thick-line"></td>
									<td class="thick-line"></td>
									<td class="thick-line"></td>
    								<td class="thick-line text-center"><strong>Total</strong></td>
    								<td class="thick-line text-right">&#x20b9;{{$ord->total_price}}</td>
    							</tr>
    						</tbody>
    					</table>
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
</div>
@stop
@section('scripts')

@stop