@extends('layout')

@section('title')
    Refered
@stop

@section('pageTitle')
    Refered
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
        Refered Users Data
    </div>
    <div class="alert"></div>
    <div class="card-body">
        @if(count($user)==0)
            No User found
        @else
        
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>email</th>
                        <th>mobile</th>
                        <th>Refered to total users</th>
                        <th>Show users</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Name</th>
                        <th>email</th>
                        <th>mobile</th>
                        <th>Refered to total users</th>
                        <th>Show users</th>
                    </tr>
                </tfoot>
                <tbody>
                @foreach($user as $owner)
                @if($owner->refered > 0)
                    <tr>
                        <td>{{$owner->name}}</td>
                        <td>{{$owner->email}}</td>
                        <td>{{$owner->mobile}}</td>
                        <td>{{$owner->refered}}</td>
                        <td><a class="btn btn-sm btn-success text-white" data-toggle="tooltip" title="Accounts" href="refered/users/{{encrypt($owner->ref_code)}}"><i class="fas fa-eye"></i></a></td>
                    </tr>
                @endif
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