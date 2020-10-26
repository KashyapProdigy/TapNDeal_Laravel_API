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
        {{session()->get('error')}}
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
    <div class="row d-flex justify-content-right col-12">
        <div class="col-md-8"></div>
        <div class="col-md-2">
            <button class="btn btn-sm btn-primary float-right my-3" data-toggle="modal" data-target="#AddSeller">+Add Multiple products</button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-sm btn-danger float-right my-3" data-toggle="modal" data-target="#AddImages">+Add Multiple images</button>
        </div>
    </div>
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
                        @php $a=\DB::table('users')->select('name')->where('id',$prdct->agents_id)->first(); @endphp
                        @if($a)
                            <td>{{$a->name}}</td>
                        @else
                            <td>-</td>
                        @endif
                        <td class="@if($prdct->isDisabled==1) text-danger @else text-info @endif">@if($prdct->isDisabled==1)
                                Disable
                            @else   
                                Enable
                            @endif
                            </td>
                        <td>
                        <a class="btn btn-sm btn-primary text-white m-1" data-toggle="tooltip" title="Enable/Disable" @if($prdct->isDisabled==0) href="{{url('manufacture/Products/disable')}}/{{$prdct->id}} @else href="{{url('manufacture/Products/enable')}}/{{$prdct->id}} @endif"><i class="fas @if($prdct->isDisabled==1)fa-eye-slash @else fa-eye @endif"></i></a>
                        <a data-toggle="tooltip" title="Delete" class="btn m-1 btn-sm btn-danger text-white" onclick="return delcon()" href="{{url('manufacture/Products/delete')}}/{{$prdct->id}}"><i class="far fa-trash-alt"></i></a>
                        </td>
                   </tr>
                @endforeach             
                </tbody>
            </table>
        </div>
        
        @endif
    </div>
</div>            
<form action="{{url('manufacture/products/add')}}" method="post" enctype="multipart/form-data">
<div class="modal fade bd-example-modal-lg" tabindex="-1" id="AddSeller" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
@csrf
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Products</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body">
        <div class="form-row">
            <input type="file" name="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
        </div>
      </div>
      <a href="{{asset('tapndeal.xlsx')}}" class="mx-3">Click here for demo file</a>
      <div class="text-danger m-3">*file must be excel or csv file</div>
      
      <div class="modal-footer">
        
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">+Add</button>
      </div>
      
    </div>
  </div>
</div>
</form>
<form action="{{url('manufacture/images/add')}}" method="post" enctype="multipart/form-data">
<div class="modal fade bd-example-modal-lg" tabindex="-1" id="AddImages" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
@csrf
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Images</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body">
        <div class="form-row">
            <input type="file" name="images[]" accept="image/*" multiple>
        </div>
      </div>
      <div class="text-danger m-3">*file must be image</div>
      
      <div class="modal-footer">
        
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">+Add</button>
      </div>
      
    </div>
  </div>
</div>
</form>
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