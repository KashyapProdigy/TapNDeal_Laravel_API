<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Tap And Deal</title>
        <link href="{{asset('Assets/css/styles.css')}}" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                          
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">Registration</h3></div>
                                    @if(Session::has('error'))
                                        <div class="alert alert-danger">
                                        {{Session::get('error')}}
                                        </div>
                                    @endif
                                    @if(Session::has('success'))
                                        <div class="alert alert-success">
                                        {{Session::get('success')}}
                                        </div>
                                    @endif
                                    @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible">
                                        {!! implode('', $errors->all('<div>:message</div>')) !!}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    @endif
                                    <div class="card-body">
                                        <form method="post">
                                        @csrf
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
                                <a href="{{url('/login')}}">Already Have an account?</a>
                                    <button type="submit" class="btn btn-primary">Register</button>
                                </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; TapAndDeal</div>
                         
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
