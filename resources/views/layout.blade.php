<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>@yield('title')</title>
        <link href="/Tapndeal/public/Assets/css/styles.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/js/all.min.js" crossorigin="anonymous"></script>
        <style>
        a {
            text-decoration: none !important;
            color: inherit; 
        }
        </style>
    </head>
    <body>
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <a class="navbar-brand" href="{{url('/admin')}}">Tap And Deal</a>
            <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <div class="d-none d-md-inline-block text-primary form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
              
            </div>
            <!-- Navbar-->
            <ul class="navbar-nav ml-auto ml-md-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="userDropdown" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user fa-fw"></i>Admin</a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="{{url('/logout')}}">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading"></div>
                            <a class="nav-link" href="{{url('/admin')}}">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link" href="{{url('admin/users')}}">
                                <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                                All Users
                            </a>
                            <a class="nav-link" href="{{url('admin/owner')}}">
                                <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                                Sellers
                            </a>
                            <a class="nav-link" href="{{url('admin/customer')}}">
                                <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                                Customer
                            </a>
                            <a class="nav-link" href="{{url('admin/agents')}}">
                                <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                                Agents
                            </a>
                            <a class="nav-link" href="{{url('admin/orders')}}">
                                <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                                Orders
                            </a>
                            <a class="nav-link" href="{{url('admin/reference')}}">
                                <div class="sb-nav-link-icon"><i class="fas fa-network-wired"></i></div>
                                Reference
                            </a>
                            <a class="nav-link" href="{{url('admin/payments')}}">
                                <div class="sb-nav-link-icon"><i class="far fa-money-bill-alt"></i></div>
                                Payments
                            </a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        Admin
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid">
                        <h1 class="mt-4">@yield('pageTitle')</h1>
                        
                            @yield('path')
                            @yield('content')
                        
                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; TapAndDeal</div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="/Tapndeal/public/Assets/js/scripts.js"></script>
        <script src="/Tapndeal/public/Assets/demo/datatables-demo.js"></script>
        <script>
        $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
        });
        </script>
        @yield('scripts')
    </body>
    
</html>
