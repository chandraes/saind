@extends('auth.layout')
@section('content')
<div class="page login-page">
    <div>
        <!-- CONTAINER OPEN -->
        <div class="col col-login mx-auto mt-7">
            <div class="text-center text-white">
                <div class="">
                    <img src="{{asset('images/saind.png')}}" class="" alt="" width="200">
                </div>
                <br>
                {{-- <h2>SAIND</h2> --}}
                <p>Login in. To see it in action.</p>
            </div>
        </div>
        <div class="container-login100">
            <div class="wrap-login100 p-0">
                <div class="card-body">
                    <p class="text-danger text-center"><strong>{{session('error')}}</strong></p>
                    <form class="login100-form validate-form" action="{{ route('login') }}" method="POST">
                        @csrf
                        <span class="login100-form-title">
                            Login
                        </span>
                        @error('username')
                            <span class="text-red" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <div class="wrap-input100 validate-input " data-bs-validate = "Valid user is required: ex@abc.xyz">
                            <input class="input100 @error('username') is-invalid state-invalid @enderror" type="text" name="username" placeholder="Username">
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="zmdi zmdi-account" aria-hidden="true"></i>
                            </span>

                        </div>
                        @error('password')
                        <span class="text-red" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <div class="wrap-input100 validate-input" data-bs-validate = "Password is required">
                            <input class="input100 @error('username') is-invalid state-invalid @enderror" type="password" name="password" placeholder="Password">
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="zmdi zmdi-lock" aria-hidden="true"></i>
                            </span>

                        </div>
                        <div class="container-login100-form-btn">
                            <button type="submit" href="index.html" class="login100-form-btn btn-primary">
                                <strong>Login</strong>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
        <!-- CONTAINER CLOSED -->
    </div>
</div>
@endsection
