@php($notification_list = getNotificationList())
<!-- head start  -->
    <div class="head_bg fixed-top">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-4 d-flex align-items-center">
                    <a href="{{route('knowledgebase_user_index')}}">
                        <img class="logo" src="{{asset(FILE_KNOWLEDGE_BASE_VIEW_PATH).'/'.$allsettings['logo']}}" alt="" />
                    </a>
                </div>
                <div class="col-8">
                    <ul class="d-flex align-items-center justify-content-end gap-md-4 gap-3 head_item my-0">
                        <li class="d-none d-sm-block">
                            <a class="active" href="{{route('knowledgebase_user_index')}}">{{$allsettings['knowledgebase']}}</a>
                        </li>
                        <li class="d-none d-sm-block">
                            <a href="{{route('support_dashboard')}}">{{$allsettings['support']}}</a>
                        </li>
                        <li>
                            <a class="position-relative" href="#" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-bell-o fs_20" aria-hidden="true"></i>
                                <small class="notify_count" id="notification_count">{{isset($notification_list)?$notification_list->count():0}}</small>
                            </a>
                                <div class="dropdown-menu notification mt-4 p-2" aria-labelledby="dropdownMenuButton2" id="notification_list">
                                    @if (isset($notification_list) && $notification_list->count() > 0)
                                        @foreach ($notification_list as $notification_item)
                                            <div>
                                                <a class="dropdown-item text-dark" href="{{route('support_notification_details', $notification_item->unique_code)}}">{{$notification_item->title}}
                                                    <br />
                                                    <small class="p_color">{!! Str::limit($notification_item->body, 30) !!}</small>
                                                </a>
                                            </div>
                                            <hr />
                                        @endforeach
                                    @else
                                        <div>
                                            <a class="dropdown-item text-dark" href="#">{{__('No Notification found')}}
                                                <br />
                                            </a>
                                        </div>
                                    @endif
                                </div>
                        </li>
                        @if (isset($user))
                            <a href="#" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                @if (isset($user->photo))
                                    <img class="user_img" src="{{show_image($user->id,'user')}}" alt="" />
                                @else
                                    <img class="user_img" src="{{asset('assets/modules/knowledgebase/image/user.jpeg')}}" alt="" />
                                @endif
                            </a>
                            <ul class="dropdown-menu mt-2 p-2" aria-labelledby="dropdownMenuButton1">
                                <li>
                                    <a class="dropdown-item text-dark" href="{{route('support_logout')}}">{{__('Log Out')}}</a>
                                </li>
                            </ul>
                        @else
                            <li>
                                <a class="btn btn-light fw-bolder text-white"
                                href="{{route('support_login')}}">
                                <small class="text-black">{{__('Login')}}</small>
                                </a>
                            </li>
                        @endif
                        <button class="navbar-toggler d-sm-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="h2 text-black"><i class="fa fa-bars"></i></span>
                        </button>
                    </ul>
                </div>


            </div>
        </div>
    </div>
    <div class="collapse navbar-collapse mx-auto position-absolute mt-1 pt-4 w-100 mx-auto" id="navbarSupportedContent">
        <div class="bg-light rounded mx-2 p-3">
            <ul>
                <li>
                    <a class="fw_600 p_color active" href="{{route('knowledgebase_user_index')}}">{{$allsettings['knowledgebase']}}</a>
                </li>
                <hr />
                <li>
                    <a class="fw_600 p_color" href="{{route('support_dashboard')}}">{{$allsettings['support']}}</a>
                </li>
            </ul>
        </div>
    </div>
    <!-- header end -->
