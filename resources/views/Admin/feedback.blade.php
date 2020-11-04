@extends('layout')

@section('title')
    Feedbacks
@stop

@section('pageTitle')
    Feedbacks
@stop

@section('content')
<div class="card mb-4">

    <div class="card-header">
        <i class="fas fa-table mr-1"></i>
        Feedbacks Data
    </div>
    <div>
        <!-- <button class="btn btn-sm btn-primary float-right m-3" data-toggle="modal" data-target="#AddSeller">+Add Agent</button> -->
    </div>
    <div class="card-body">
        @if(count($feedbacks)==0)
            No Feedback found
        @else

        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Company name</th>
                        <th>Feedback</th>
                        <th>Date Time</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Company name</th>
                        <th>Feedback</th>
                        <th>Date Time</th>
                    </tr>
                </tfoot>
                <tbody>
                @php $i=1; @endphp
                @foreach($feedbacks as $feedback)
                    <tr>
                        <td>{{$i++}}</td>
                        <td>{{$feedback->name}}</td>
                        <td>{{$feedback->cname}}</td>
                        <td>{{$feedback->msg}}</td>
                        <td>{{date('d-m-Y H:i:s',strtotime($feedback->date_time))}}</td>
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

<script src="{{asset('Assets/demo/datatables-demo.js')}}"></script>
@stop
