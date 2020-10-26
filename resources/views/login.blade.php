<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <meta name="csrf-token" content="{{ csrf_token() }}" /> 
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
                            <div class="col-lg-5">
                          
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">Login</h3></div>
                                    @if(Session::has('error'))
                                        <div class="alert alert-danger">
                                        {{Session::get('error')}}
                                        </div>
                                    @endif  
                                    <div id="msg"></div>
                                    <div class="card-body">
                                        <form method="post" name="logform">
                                        @csrf
                                            <div class="form-group" id="num">
                                                <label class="small mb-1" for="inputEmailAddress">Mobile No.</label>
                                                <input class="form-control py-4" name="mobile" id="number" type="text"  placeholder="Enter Your mobile number" />
                                            </div>
                                            <div id="recaptcha-container"></div>
                                            <div class="form-group" id="otp">
                                                <label for="inputPassword4">Password</label>
                                                <input class="form-control py-4" name="password" id="verificationCode" type="text"  placeholder="Enter Password" />
                                            </div>
                                            <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                                                
                                                <button type="submit" id="send" class="btn btn-primary">Login</button>
                                                <a id="su" href="{{url('/manufacture')}}">Sign up</a>    
                                                <!-- <button type="button" onclick="codeverify()" id="sub" class="btn btn-success d-none">Submit OTP</button> -->
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
        <!-- <script src="js/scripts.js"></script> -->
        <script>
        // function afterotp()
        // {
        //     $('#recaptcha-container').addClass('d-none');
        //     $('#send').addClass('d-none');
        //     $('#num').addClass('d-none');
        //     $('#sp').addClass('d-none');
        //     $('#sub').removeClass('d-none');
        //     $('#otp').removeClass('d-none');
        //     $('#msg').html('OTP sended successfully to your number..!!');
        //     $('#msg').addClass('alert alert-success');
        // }
        // function checkMob(){
        //        $.ajax({
        //           url: "{{ url('/mobileCheck') }}",
        //           method: 'get',
        //           data: {
        //               mo: $('#number').val(),
        //           },
        //           success: function(result){
        //              if(result.co!=1)
        //              {
        //                 $('#msg').addClass('alert alert-danger');
        //                 $('#msg').html('No Account found on this mobile number..!')
        //              }
        //              else{
        //                 phoneAuth();
        //              }
        //           }});
        // }
        // </script>
        // <script src="https://www.gstatic.com/firebasejs/6.0.2/firebase.js"></script>

        // <!-- TODO: Add SDKs for Firebase products that you want to use
        //     https://firebase.google.com/docs/web/setup#config-web-app -->

        // <script>
        //     // Your web app's Firebase configuration
        //     var firebaseConfig = {
        //         apiKey: "AIzaSyDhyxefh_wPnGxldEDFomXxsf6kWErbkDg",
        //         authDomain: "tap-n-deal-fbc6f.firebaseapp.com",
        //         databaseURL: "https://tap-n-deal-fbc6f.firebaseio.com",
        //         projectId: "tap-n-deal-fbc6f",
        //         storageBucket: "tap-n-deal-fbc6f.appspot.com",
        //         messagingSenderId: "485868255508",
        //         appId: "1:485868255508:web:f03278394335ebb6306a01",
        //         measurementId: "G-MEPWN1V4R8"
        //     };
        //     // Initialize Firebase
        //     firebase.initializeApp(firebaseConfig);
        //     // firebase.analytics();
        // </script>
        // <script>
        // window.onload=function () {
        // render();
        // };
        // function render() {
        //     window.recaptchaVerifier=new firebase.auth.RecaptchaVerifier('recaptcha-container');
        //     recaptchaVerifier.render();
        // }
        // function phoneAuth() {
        //     //get the number
        //     var number="+91"+document.getElementById('number').value;
        //     alert(number);
        //     //phone number authentication function of firebase
        //     //it takes two parameter first one is number,,,second one is recaptcha
        //     firebase.auth().signInWithPhoneNumber(number,window.recaptchaVerifier).then(function (confirmationResult) {
        //         //s is in lowercase
        //         window.confirmationResult=confirmationResult;
        //         coderesult=confirmationResult;
        //         console.log(coderesult);
        //         // alert("Message sent");
        //         afterotp();
        //     }).catch(function (error) {
        //         $('#msg').html(error.message);
        //         $('#msg').addClass('alert alert-danger');
        //     });
        // }
        // function codeverify() {
        //     var code=document.getElementById('verificationCode').value;
        //     coderesult.confirm(code).then(function (result) {
        //         document.forms["logform"].submit();
        //         var user=result.user;
        //         console.log(user);
        //     }).catch(function (error) {
        //         $('#msg').html(error.message);
        //         $('#msg').addClass('alert alert-danger');
        //     });
        // }
        // </script>

    </body>
</html>
