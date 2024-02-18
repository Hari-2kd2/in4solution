<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="{!! asset('icon.png') !!}" type="image/x-icon" />
    <!-- <link rel="shortcut icon" href="{!! asset('admin_assets/img/logo.png') !!}" type="image/x-icon" /> -->
    <title>Pro-People Login</title>
    <!-- Bootstrap Core CSS -->
    <link href="{!! asset('admin_assets/bootstrap/dist/css/bootstrap.min.css') !!}" rel="stylesheet">
    <!-- animation CSS -->
    <link href="{!! asset('admin_assets/css/animate.css') !!}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{!! asset('admin_assets/css/style.css') !!}" rel="stylesheet">
    <!-- color CSS -->
    <link href="{!! asset('admin_assets/css/colors/default.css') !!}" id="theme" rel="stylesheet">

    <style>
        .white-box {
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 1px 1px 8px;
            margin: 50% auto;
            border-radius: 12px;
        }

        button {
            background: #5a2aba;
            border-radius: 8px;
        }

        button.btn:hover {
            color: #ffffff;
        }

        .input {
            height: 32px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="cssload-speeding-wheel"></div>
    </div>
    <section id="wrapper" class="new-login-register" style="background: #   ;">
        <div class="container">
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <div class="white-box" style="margin:20% auto;">
                        <h3 class="box-title m-b-0">LogIn</h3>
                        <div class="login-logo" style="text-align: center">
                            <a href="{{ Url('/') }}"><img src="{!! asset('admin_assets/img/in4_logo.jpg') !!}"
                                    style="background:white; max-height:50px;" /></a>
                        </div>
                        {!! Form::open(['url' => 'login', 'class' => 'form-horizontal new-lg-form', 'id' => 'loginform']) !!}
                        
                        @if (session()->has('success'))
                            <div class="alert alert-success">
                                <p>{!! session()->get('success') !!}</p>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <p>{!! session()->get('error') !!}</p>
                            </div>
                        @endif
                        <div class="form-group m-t-20">
                            <div class="col-xs-12">
                                <label>User Name</label>
                                <input type="text" name="user_name" class="form-control" placeholder="User Name"
                                    value="{!! old('user_name') !!}" autofocus>
                                @if ($errors->has('username'))
                                    <span style="margin-left:8px"
                                        class="text-danger">{{ $errors->first('username') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <label>Password</label>
                                <input type="password" name="user_password" class="form-control"
                                    placeholder="Password" />
                                @if ($errors->has('password'))
                                    <span style="margin-left:8px"
                                        class="text-danger">{{ $errors->first('password') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group text-center text-white">
                            <div class="col-xs-12">
                                <button class="btn btn-lg btn-block text-uppercase waves-effect waves-light"
                                    type="submit">LogIn</button>
                            </div>
                        </div>
                        <a class="btn-block waves-effect waves-light newPassword">Forgot Password</a>
                        {!! Form::close() !!}
                        {!! Form::open(['url' => 'newPassword','class' => 'form-horizontal new-lg-form','id' => 'newpasswordform','method' =>'POST']) !!}
							<div class="form-group m-t-20">
							  <div class="col-xs-12">
								<p class="text-muted"> The new password will be sent to admin email! </p>
							  </div>
							</div>
							<div class="form-group">
							  <div class="col-xs-12">
							  <input class="form-control" type="text" required="" name="user_name" name="User Name" placeholder=" Enter your User Name">
							  </div>
							</div>
							<div class="form-group">
								<div class="col-md-12">
									<a href="javascript:void(0)" id="closeForgetPassword" class="text-dark pull-right"><i class="fa fa-arrow-left m-r-5"></i> Back</a> </div>
							</div>
							
							<div class="form-group text-center">
							  <div class="col-xs-12">
								<button class="btn btn-primary btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">New Password</button>
							  </div>
							</div>
							{!! Form::close() !!}
                            <form class="form-horizontal m-t-20" id="recoverform" action="">
                                <div class="form-group ">
                                  <div class="col-xs-12">
                                    <h3>Recover Password</h3>
                                    <p class="text-muted">Enter your Email and instructions will be sent to you! </p>
                                  </div>
                                </div>
                                <div class="form-group ">
                                  <div class="col-xs-12">
                                    <input class="form-control" type="text" required="" placeholder="User ID">
                                  </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <a href="javascript:;" id="backToLogin" class="text-dark pull-right back-to-login"><i class="fa fa-arrow-left m-r-5"></i> Back</a> </div>
                                </div>
                                
                                <div class="form-group text-center">
                                  <div class="col-xs-12">
                                    <button class="btn btn-primary btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Reset</button>
                                  </div>
                                </div>
                            </form>
                    </div>
                </div>
                <div class="col-md-4"></div>
            </div>
        </div>



    </section>
    <!-- jQuery -->
    <script src="{!! asset('admin_assets/plugins/bower_components/jquery/dist/jquery.min.js') !!}"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="{!! asset('admin_assets/bootstrap/dist/js/bootstrap.min.js') !!}"></script>
    <!-- Menu Plugin JavaScript -->
    <script src="{!! asset('admin_assets/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.js') !!}"></script>

    <!--slimscroll JavaScript -->
    <script src="{!! asset('admin_assets/js/jquery.slimscroll.js') !!}"></script>
    <!--Wave Effects -->
    <script src="{!! asset('admin_assets/js/waves.js') !!}"></script>
    <!-- Custom Theme JavaScript -->
    <script src="{!! asset('admin_assets/js/custom.min.js') !!}"></script>

    <script>
        $(function () {
			$('#newpasswordform').css('display','none');
            $(document).on("focus", "#backToLogin", function () {
                $( "#recoverform" ).fadeOut( "slow", function() {
					$('#newpasswordform').css('display','none');  
                });
				$( "#newPassword").fadeOut( "slow", function() {
				});
            });
			$(document).on("click", ".newPassword", function () {
                $( "#loginform").fadeOut( "slow", function() {
				});
				$( "#recoverform" ).fadeOut( "slow", function() {
					$('#newpasswordform').css('display','block'); 
                });
                     
            });
			$(document).on("click", "#closeForgetPassword", function () { 
				$( "#newPassword" ).fadeOut( "slow", function() {
                });
                $('#newpasswordform').css('display','none');  
                $( "#loginform").fadeIn( "slow", function() {
				});
            });
			

            $(".alert-success").delay(1000).fadeOut("slow");
        });
	</script>
</body>

</html>
