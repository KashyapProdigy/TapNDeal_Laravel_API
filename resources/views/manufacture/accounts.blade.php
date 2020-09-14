@extends('manuLayout')

@section('title')
    Employees
@stop

@section('pageTitle')
    Employees
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
        Employees Data
    </div>
    <div class="row d-flex justify-content-right col-12">
        <div class="col-md-10"></div>
        <div class="col-md-2">
            <button class="btn btn-sm btn-primary float-right m-3" data-toggle="modal" data-target="#AddSeller">+Add Employee</button>
        </div>
    </div>
    <div class="card-body">
        @if(count($owners)==0)
            No Seller found
        @else
        
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>email</th>
                        <th>mobile</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Employee Type</th>
                        <th>Varified</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Name</th>   
                        <th>email</th>
                        <th>mobile</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Employee Type</th>
                        <th>Varified</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
                <tbody>
                @foreach($owners as $owner)
                    <tr>
                        <td>{{$owner->name}}</td>
                        <td>{{$owner->email}}</td>
                        <td>{{$owner->mobile}}</td>
                        <td>{{$owner->city_name}}</td>
                        <td>{{$owner->state_name}}</td>
                        <td>{{$owner->user_type}}</td>
                        <td>@if($owner->isVerified==1)<span class="text-success">Varified</span>@else<span class="text-danger">Unvarified</span>@endif</td>
                        <td>
                        <span data-toggle="tooltip" title="Update"><a class="btn btn-sm btn-primary text-white" data-toggle="modal" data-target="#updateEmployee{{$owner->uid}}"><i class="fas fa-pen"></i></a></span>
                            <a class="btn btn-sm btn-danger text-white" data-toggle="tooltip" title="Delete" onclick="return delcon()" href="{{url('manufacture/emp/delete')}}/{{$owner->uid}}"><i class="far fa-trash-alt"></i></a>
                        </td>
                    </tr>
                @endforeach             
                </tbody>
            </table>
        </div>
        
        @endif
    </div>
</div>            
<form action="{{url('manufacture/emp/add')}}" method="post">
<div class="modal fade bd-example-modal-lg" tabindex="-1" id="AddSeller" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
@csrf
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Employee</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <input type="hidden" name="seller" value="{{$seller->id}}">
      <div class="modal-body">

            <div class="form-group">
            <label for="inputEmail4">Name</label>
            <input type="text" class="form-control" name="name"  placeholder="Name">
            </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
            <label for="inputPassword4">Email</label>
            <input type="text" class="form-control" name="email" id="inputPassword4" placeholder="Email">
            </div>
            <div class="form-group col-md-6">
            <label for="inputPassword4">Mobile</label>
            <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Mobile Number">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
            <label for="inputCity">City</label>
            <select id="inputState" name="city" class="form-control">
                <option value="">Choose...</option>
                @foreach($citys as $city)
                    <option value="{{$city->id}}">{{$city->city_name}}</option>
                @endforeach
            </select>
            </div>
            <div class="form-group col-md-6">
            <label for="inputState">Employee Type</label>
            <select id="inputState" name="type" class="form-control">
                <option value="">Choose...</option>
                @foreach($e_type as $e)
                    <option value="{{$e->id}}">{{$e->user_type}}</option>
                @endforeach
            </select>
            </div>
        </div>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">+Add</button>
      </div>
      
    </div>
  </div>
</div>
</form>
@foreach($owners as $owner)
<form action="{{url('/manufacture/emp/edit')}}" method="post">
<div class="modal fade bd-example-modal-lg" tabindex="-1" id="updateEmployee{{$owner->uid}}" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
@csrf
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    <input type="hidden" name="uid" value="{{$owner->uid}}">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Update seller</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      
            <div class="form-group">
            <label for="inputEmail4">Name</label>
            <input type="text" class="form-control" name="name" value="{{$owner->name}}" placeholder="Name">
            </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
            <label for="inputPassword4">Email</label>
            <input type="text" class="form-control" value="{{$owner->email}}" name="email" id="inputPassword4" placeholder="Email">
            </div>
            <div class="form-group col-md-6">
            <label for="inputPassword4">Mobile</label>
            <input type="text" class="form-control" value="{{$owner->mobile}}" name="mobile" id="mobile" placeholder="Mobile Number">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
            <label for="inputCity">City</label>
            <select id="inputState" name="city" class="form-control">
                <option value="">Choose...</option>
                @foreach($citys as $city)
                    <option @if($owner->city_id==$city->id) selected @endif value="{{$city->id}}">{{$city->city_name}}</option>
                @endforeach
            </select>
            </div>
            <div class="form-group col-md-6">
            <label for="inputState">Employee Type</label>
            <select id="inputState" name="etype" class="form-control">
                <option value="">Choose...</option>
                @foreach($e_type as $e)
                    <option @if($owner->type_id == $e->id) selected @endif value="{{$e->id}}">{{$e->user_type}}</option>
                @endforeach
            </select>
            </div>
        </div>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
      
    </div>
  </div>
</div>
</form>
@endforeach
@stop
@section('scripts')
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script src="{{asset('Assets/demo/datatables-demo.js')}}"></script>
<script>
function delcon()
{
    if(confirm('Do you really wan\'t delete this user...!!')==true)
    {
        return true;
    }
    return false;

}
</script>
@stop