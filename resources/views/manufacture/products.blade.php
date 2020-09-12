@extends('manuLayout')

@section('title')
    Products
@stop

@section('pageTitle')
    Products
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
        {{session()->get('danger')}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    <div class="card-header">
        <i class="fas fa-table mr-1"></i>
        Products Data
    </div>
    <div>
        <!-- <button class="btn btn-sm btn-primary float-right m-3" data-toggle="modal" data-target="#AddSeller"></button> -->
    </div>
    <div class="card-body">
        @if(count($products)==0)
            No Product found
        @else
        
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Tags</th>
                        <th>Stock</th>
                        <th>Colors</th>
                        <th>Agent</th>
                        <th> Enable/Disable</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tfoot>
                <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Tags</th>
                        <th>Stock</th>
                        <th>Colors</th>
                        <th>Agent</th>
                        <th> Enable/Disable</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
                <tbody>
                @foreach($products as $prdct)
                   <tr>
                        <td>{{$prdct->name}}</td>
                        <td>{{$prdct->price}}</td>
                        <td>{{$prdct->description}}</td>
                        <td>{{$prdct->category}}</td>
                        <td>{{$prdct->tags}}</td>
                        <td>{{$prdct->stock}}</td>
                        <td>{{$prdct->colors}}</td>
                        <td>{{$prdct->aname}}</td>
                        <td class="@if($prdct->isDisabled==1) text-muted @else text-info @endif">@if($prdct->isDisabled==1)
                                Disable
                            @else   
                                Enable
                            @endif
                            </td>
                        <td>
                        <a class="btn btn-sm btn-success text-white" data-toggle="tooltip" title="Accounts" href="{{url('/seller/accounts')}}/{{$prdct->id}}"><i class="fas fa-eye"></i></a>
                            <span data-toggle="tooltip" title="Update"><a class="btn btn-sm btn-primary text-white" data-toggle="modal" data-target="#updateSeller{{$prdct->id}}"><i class="fas fa-pen"></i></a></span>
                            <a data-toggle="tooltip" title="Delete" class="btn btn-sm btn-danger text-white" onclick="return delcon()" href="{{url('product/delete')}}/{{$prdct->id}}"><i class="far fa-trash-alt"></i></a>
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