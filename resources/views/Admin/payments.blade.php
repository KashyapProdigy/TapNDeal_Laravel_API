@extends('layout')

@section('title')
    Payment
@stop

@section('pageTitle')
    Payment
@stop

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table mr-1"></i>
        Payment
    </div>
    <div class="alert"></div>
    <div class="card-body">
        @if(count($pay)==0)
            No Payment found
        @else
        
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>User name</th>
                        <th>Mobile</th>
                        <th>User Type</th>
                        <th>Transaction Id</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Payment status</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>User name</th>
                        <th>Mobile</th>
                        <th>User Type</th>
                        <th>Transaction Id</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Payment status</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </tfoot>
                <tbody>
                @foreach($pay as $p)
                    <tr>
                        <td>{{$p->name}}</td>
                        <td>{{$p->mobile}}</td>
                        <td>{{$p->user_type}}</td>
                        <td>{{$p->transaction_id}}</td>
                        <td>{{$p->amount}}</td>
                        <td>{{$p->method}}</td>
                        <td>{{$p->payment_status}}</td>
                        <td>{{$p->description}}</td>
                        <td>{{date('d-m-Y H:i:s',strtotime($p->date_time))}}</td>
                    </tr>
                @endforeach             
                </tbody>
            </table>
        </div>
        
        @endif
    </div>
</div>            
@stop
@section('scripts')
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script src="{{asset('Assets/demo/datatables-demo.js')}}"></script>
@stop