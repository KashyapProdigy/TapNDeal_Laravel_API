@extends('layout')

@section('title')
    Orders List
@stop

@section('pageTitle')
    Orders List
@stop

@section('content')
<div class="card mb-4">
    
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible">
        {!! implode('', $errors->all('<div>:message</div>')) !!}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    @if(session()->has('success'))
    <div class="alert alert-success alert-dismissible">
        {{session()->get('success')}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    @if(session()->has('error'))
    <div class="alert alert-danger alert-dismissible">
        {{session()->get('error')}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    <div class="card-header">
        <i class="fas fa-table mr-1"></i>
        Order data
    </div>
   
    <div class="card-body">
        @if(count($list)==0)
            No Seller found
        @else
        
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer name</th>
                        <th>Agent name</th>
                        <th>Seller name</th>
                        <th>Order status</th>
                        <th>Approved</th>
                        <th>Delivered</th>
                        <th>Total Price</th>
                        <th>View Details</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                    <th>#</th>
                    <th>Customer name</th>
                    <th>Agent name</th>
                    <th>Seller name</th>
                    <th>Order status</th>
                    <th>Approved</th>
                    <th>Delivered</th>
                    <th>Total Price</th>
                    <th>View Details</th>
                    </tr>
                </tfoot>
                <tbody>
                @php $i=0; @endphp
                @foreach($list as $li)
                @php $a=\DB::table('users')->select('name')->where('ref_code',$li->agent_reference)->first();@endphp
                @php $s=\DB::table('users')->select('name')->where('id',$li->seller_id)->first();@endphp
                <tr>
                    <td>{{++$i}}</td>
                    <td>{{$li->name}}</td>
                    <td>{{$a->name}}</td>
                    <td>{{$s->name}}</td>
                    <td class= @if($li->status_id==1)text-danger @else text-success @endif>{{$li->status_name}}</td>
                    <td class="@if($li->isApproved==1)text-success @else text-danger @endif">
                        @if($li->isApproved==1)
                            Approved
                        @else
                            Not Approved
                        @endif
                    </td>
                    <td class="@if($li->isDelivered==1)text-success @else text-danger @endif">
                        @if($li->isDelivered==1)
                           Delivered
                        @else
                          Not Delivered
                        @endif
                    </td>
                    <td class="text-right">
                    &#x20b9;{{$li->total_price}}
                    </td>
                    <td>
                        <a class="btn btn-sm btn-success text-white" data-toggle="tooltip" title="View details" href="{{url('/admin/orders/show')}}/{{$li->oid}}"><i class="fas fa-eye"></i></a>
                    </td>
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
<script>
function delcon()
{
    if(confirm('Do you really wan\'t delete this seller...!!')==true)
    {
        return true;
    }
    return false;

}
</script>
@stop