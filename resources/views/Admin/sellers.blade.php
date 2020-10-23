@extends('layout')

@section('title')
    Sellers
@stop

@section('pageTitle')
    Sellers
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
        Seller Data
    </div>
    <div>
        <button class="btn btn-sm btn-primary float-right m-3" data-toggle="modal" data-target="#AddSeller">+Add Seller</button>
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
                        <th>Company name</th>
                        <th>email</th>
                        <th>mobile</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>State</th>
                        <th>PAN No</th>
                        <th>GST No</th>
                        <th>verified</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                    <th>Name</th>
                        <th>Company name</th>
                        <th>email</th>
                        <th>mobile</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>State</th>
                        <th>PAN No</th>
                        <th>GST No</th>
                        <th>verified</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
                <tbody>
                @foreach($owners as $owner)
                    <tr>
                        <td>{{$owner->name}}</td>
                        <td>{{$owner->cname}}</td>
                        <td>{{$owner->email}}</td>
                        <td>{{$owner->mobile}}</td>
                        <td>{{$owner->address}}</td>
                        <td>{{$owner->city_name}}</td>
                        <td>{{$owner->state_name}}</td>
                        <td>{{$owner->pan}}</td>
                        <td>{{$owner->gst}}</td>
                        <td>@if($owner->isVerified==1)<span class="text-success">verified</span>@else<span class="text-danger">Unverified</span>@endif</td>
                        <td>
                            <a class="btn btn-sm btn-success text-white" data-toggle="tooltip" title="Accounts" href="{{url('/seller/accounts')}}/{{$owner->uid}}"><i class="fas fa-eye"></i></a>
                            <span data-toggle="tooltip" title="Update"><a class="btn btn-sm btn-primary text-white" data-toggle="modal" data-target="#updateSeller{{$owner->uid}}"><i class="fas fa-pen"></i></a></span>
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
<form action="{{url('/SellerAdd')}}" method="post">
<div class="modal fade bd-example-modal-lg" tabindex="-1" id="AddSeller" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
@csrf
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add seller</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <div class="form-group">
        <label for="inputEmail4">Name</label>
        <input type="text" class="form-control" name="name"  placeholder="Seller Name">
    </div>
    <div class="form-group">
        <label for="inputEmail4">Company Name</label>
        <input type="text" class="form-control" name="cname"  placeholder="Company Name">
    </div>
    <div class="form-group">
        <label for="inputEmail4">Address</label>
        <input type="text" class="form-control" name="address"  placeholder="Address">
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
        <label for="inputCity">GST no:</label>
        <input type="text" class="form-control" name="gst" id="gst" placeholder="GST Number">
    </div>
    <div class="form-group col-md-6">
        <label for="inputCity">PAN no:</label>
        <input type="text" class="form-control" name="pan" id="pan" placeholder="GST Number">
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
            <div class="form-group">
                <label for="inputEmail4">Company Name</label>
                <input type="text" class="form-control" name="cname" value="{{$owner->cname}}"  placeholder="Company Name">
            </div>
            <div class="form-group">
                <label for="inputEmail4">Address</label>
                <input type="text" class="form-control" name="address" value="{{$owner->address}}"  placeholder="Address">
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
                <label for="inputCity">GST no:</label>
                <input type="text" class="form-control" name="gst" id="gst" value="{{$owner->gst}}" placeholder="GST Number">
            </div>
            <div class="form-group col-md-6">
                <label for="inputCity">PAN no:</label>
                <input type="text" class="form-control" name="pan" id="pan" value="{{$owner->pan}}" placeholder="GST Number">
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