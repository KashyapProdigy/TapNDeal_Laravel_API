@extends('layout')

@section('title')
    Reference
@stop

@section('pageTitle')
    Reference User
@stop

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table mr-1"></i>
        Reference User Data
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
                        <th>User type</th>
                        <th>email</th>
                        <th>mobile</th>
                        
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Name</th>
                        <th>User type</th>
                        <th>email</th>
                        <th>mobile</th>
                    </tr>
                </tfoot>
                <tbody>
                @foreach($user as $owner)
                    <tr>
                        <td>{{$owner->name}}</td>
                        <td>{{$owner->user_type}}</td>
                        <td>{{$owner->email}}</td>
                        <td>{{$owner->mobile}}</td>
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