@extends('layout')

@section('title')
    DashBoard
@stop

@section('content')
        <div class="row">

            <!-- Earnings (Monthly) Card Example -->
            
            <div class="col-xl-4 col-md-6 mb-4">
              <div class="card border-left-primary shadow h-100 py-2">
              <a class="" href="{{url('admin/owner')}}">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Sellers</div>
                      <div class="h5 mb-0 font-weight-bold text--800">{{$ts}}</div>
                    </div>
                    <div class="col-auto">
                    <i class="fas fa-user fa-2x text-primary"></i>
                    </div>
                  </div>
                </div>
                </a>
              </div>
            </div>
            
            <!-- Earnings (Annual) Card Example -->
            <div class="col-xl-4 col-md-6 mb-4">
              <div class="card border-left-success shadow h-100 py-2">
              <a class="" href="{{url('admin/customer')}}">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Customers</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"> {{$tc}} </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-user fa-2x text-success"></i>
                    </div>
                  </div>
                </div>
                </a>
              </div>
            </div>

            <!-- Tasks Card Example -->
            <div class="col-xl-4 col-md-6 mb-4">
              <div class="card border-left-info shadow h-100 py-2">
              <a class="" href="{{url('admin/agents')}}">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Agents</div>
                      <div class="row no-gutters align-items-center">
                        <div class="col-auto">
                          <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{$ta}} </div>
                        </div>
                        <div class="col">
                        </div>
                      </div>
                    </div>
                    <div class="col-auto">
                    <i class="fas fa-user fa-2x text-info"></i>
                    </div>
                  </div>
                </div>
                </a>
              </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-4 col-md-6 mb-4">
              <div class="card border-left-primary shadow h-100 py-2">
              <a class="" href="{{url('admin/orders')}}">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Orders</div>
                      <div class="h5 mb-0 font-weight-bold text--800">{{$to}}</div>
                    </div>
                    <div class="col-auto">
                    <i class="fas fa-list fa-2x text-danger"></i>
                    </div>
                  </div>
                </div>
                </a>
              </div>
            </div>

            <!-- Earnings (Annual) Card Example -->
            <div class="col-xl-4 col-md-6 mb-4">
              <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">Total selling</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800">&#x20b9; {{$sel}}</div>
                    </div>
                    <div class="col-auto">
                    <i class="h1 text-gray-800">&#x20b9;</i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tasks Card Example -->
            <div class="col-xl-4 col-md-6 mb-4">
              <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total products</div>
                      <div class="row no-gutters align-items-center">
                        <div class="col-auto">
                          <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{$tp}}</div>
                        </div>
                        <div class="col">
                        </div>
                      </div>
                    </div>
                    <div class="col-auto">
                    <i class="fas fa-box-open fa-2x text-info"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
@stop