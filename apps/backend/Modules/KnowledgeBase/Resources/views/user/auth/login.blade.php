@extends('knowledgebase::layouts.master')

@section('content')
<!-- header start -->
{{-- <section class="bg_image">
    <div class="container">
        <div class="row">
            <div class="col-12 mb-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <a href="{{route('knowledgebase_user_index')}}">
                        <img src="http://127.0.0.1:8000/assets/user/images/logo.svg" alt="" />
                    </a>
                    <ul class="d-flex align-items-center gap-md-4 gap-3 head_item my-0">
                        <li class="d-none d-sm-block">
                            <a href="{{route('knowledgebase_user_index')}}">{{__('Knowledge')}}</a>
                        </li>
                        <li class="d-none d-sm-block">
                            <a class="active" href="{{route('support_dashboard')}}">{{__('Support')}}</a>
                        </li>
                        <li>
                            <a class="position-relative" href="#" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-bell-o fs_20" aria-hidden="true"></i>
                                <small class="notify_count">8</small>
                            </a>
                            <div class="dropdown-menu notification mt-4 p-2" aria-labelledby="dropdownMenuButton2">
                                <div>
                                    <a class="dropdown-item text-dark" href="#">First Notification
                                        <br />
                                        <small class="p_color"
                                        >Lorem ipsum dolor sit amet consectetur...</small
                                        >
                                    </a>
                                </div>
                                <hr />
                                <div>
                                    <a class="dropdown-item text-dark" href="#">Secend Notification
                                        <br />
                                        <small class="p_color"
                                        >Lorem ipsum dolor sit amet consectetur...</small
                                        >
                                    </a>
                                </div>
                                <hr />
                                <div>
                                    <a class="dropdown-item text-dark" href="#">Secend Notification
                                        <br />
                                        <small class="p_color"
                                        >Lorem ipsum dolor sit amet consectetur ...</small
                                        >
                                    </a>
                                </div>
                            </div>
                        </li>

                        @if (auth()->user())
                            <a href="#" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                <img class="user_img" src="../Assets/image/user.jpeg" alt="" />
                            </a>
                            <ul class="dropdown-menu mt-4 p-2" aria-labelledby="dropdownMenuButton1">
                                <li>
                                    <a class="dropdown-item text-dark" href="#">Profile</a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-dark" href="#">Log Out</a>
                                </li>
                            </ul>
                        @else
                            <li>
                                <a class="btn btn-info fw-bolder text-white"
                                href="{{route('support_login')}}">
                                <small>Login</small>
                                </a>
                            </li>
                        @endif
                    </ul>
                    <button class="navbar-toggler d-sm-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="h2 text-white"><i class="fa fa-bars"></i></span>
                    </button>
                </div>
            </div>

            <div class="collapse navbar-collapse mx-auto" id="navbarSupportedContent">
                <div class="bg-light rounded mx-2 p-3">
                    <ul>
                        <li>
                            <a class="fw_600 p_color" href="knowledge.html">Knowledge</a>
                        </li>
                        <hr />
                        <li>
                            <a class="fw_600 p_color active" href="support.html">Support</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section> --}}
<!-- header end -->
<section class="login-bg">
    <div class="container">
        <div class="row">
            <form action="{{route('support_login_process')}}" method="POST">
                @csrf
                <div class="col-12">
                    <div class="height_vh d-flex align-items-center justify-content-center">
                        <div class="p-4 login_bg">
                            <div class="text-center pb-3">
                                <a class="login-logo" href="#">
                                    <img src="http://127.0.0.1:8000/assets/user/images/logo.svg" alt="" />
                                </a>
                            </div> 
                            <div class="text-center my-3">
                                <h2 class="text-dark"><b>Sign In</b></h2>
                                <p>Please sign in to your account </p>
                            </div>
                            
                            <form class="text-white">
                                <label class="pt-4">Email :</label>
                                <input type="email" value="{{old('email')}}" id="exampleInputEmail1" name="email"
                                class="login_input"
                                placeholder="{{__('Your email')}}" />

                                <label class="pt-4">Password :</label>
                                <input type="password" name="password" id="exampleInputPassword1"
                                    class="login_input form-control-password look-pass-a"
                                    placeholder="{{__('Your password')}}" />
                                <button class="login_btn mt-5">Sign In</button>
                            </form>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>


@endsection

@section('script')

@endsection
