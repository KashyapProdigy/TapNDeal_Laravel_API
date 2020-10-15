@extends('layout')

@section('title')
    All Users
@stop

@section('pageTitle')
    All Users
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
        All Users Data
    </div>
    <div class="alert"></div>
    <div class="card-body">
        @if(count($owners)==0)
            No User found
        @else
        
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Company name</th>
                        <th>User type</th>
                        <th>email</th>
                        <th>mobile</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>State</th>
                        <th>PAN No</th>
                        <th>GST No</th>
                        <th>Varified</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                    <th>Name</th>
                        <th>Company name</th>
                        <th>User type</th>
                        <th>email</th>
                        <th>mobile</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>State</th>
                        <th>PAN No</th>
                        <th>GST No</th>
                        <th>Varified</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
                <tbody>
                @foreach($owners as $owner)
                    <tr>
                        <td>{{$owner->name}}</td>
                        <td>{{$owner->cname}}</td>
                        <td>
                            <select onchange="changeType({{$owner->uid}})" id="utype" class="form-controller">
                                @foreach($type as $utype)
                                <option value="{{$utype->id}}" @if($utype->user_type==$owner->user_type)selected @endif>{{$utype->user_type}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>{{$owner->email}}</td>
                        <td>{{$owner->mobile}}</td>
                        <td>{{$owner->address}}</td>
                        <td>{{$owner->city_name}}</td>
                        <td>{{$owner->state_name}}</td>
                        <td>{{$owner->pan}}</td>
                        <td>{{$owner->gst}}</td>
                        <td>@if($owner->isVerified==1)<span class="text-success">Varified</span>@else<span class="text-danger">Unvarified</span>@endif</td>
                        <td>
                            <!-- <a class="btn btn-sm btn-success text-white" data-toggle="tooltip" title="Accounts" href="{{url('/seller/accounts')}}/{{$owner->uid}}"><i class="fas fa-eye"></i></a> -->
                            <!-- <span data-toggle="tooltip" title="Update"><a class="btn btn-sm btn-primary text-white" data-toggle="modal" data-target="#updateSeller{{$owner->uid}}"><i class="fas fa-pen"></i></a></span> -->
                            <a data-toggle="tooltip" title="Delete" class="btn btn-sm btn-danger text-white" onclick="return delcon()" href="{{url('seller/delete')}}/{{$owner->uid}}"><i class="far fa-trash-alt"></i></a>
                        </td>
                    </tr>
                @endforeach             
                </tbody>
            </table>
        </div>
        
        @endif
    </div>
</div>            
@foreach($owners as $owner)
<form action="{{url('/update/seller')}}" method="post">
<div class="modal fade bd-example-modal-lg" tabindex="-1" id="updateSeller{{$owner->uid}}" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
           
        </div>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
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
function changeType(uid)
{
    if(confirm('Do you really wan\'t change this user designation...!!')==true)
    {
        $.ajax({
        url: "{{ url('/changeType') }}",
        method: 'get',
        data: {
            uid: uid,
            utype: $('#utype').val(),
        },
        success: function(result){
            if(result.status)
            {
                $('.alert').html('<div class="alert alert-success alert-dismissible">User Desingnation changed successfully..<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            }
            
        }});
    }
    return false;
}
function delcon()
{
    if(confirm('Do you really wan\'t Delete this user...!!')==true)
    {
        return true;
    }
    return false;
}
</script>
@stop