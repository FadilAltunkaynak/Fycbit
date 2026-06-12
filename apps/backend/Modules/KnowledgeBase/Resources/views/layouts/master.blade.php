<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{__('Knowledgebase')}}</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,100;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet" />

    <!-- bootstrap.min.css -->
    <link href="{{ asset('assets/modules/knowledgebase/css/bootstrap.min.css')}}" rel="stylesheet" />
	<!-- font-awesome -->
    <link rel="stylesheet" href="{{asset('assets/common/css/font-awesome.min.css')}}">
    <!-- custom.css -->
    <link rel="stylesheet" href="{{ asset('assets/modules/knowledgebase/css/style.css') }}">
    <!-- toaster -->
    <link href="{{asset('assets/common/toast/vanillatoasts.css')}}" rel="stylesheet" >

    @yield('style')
</head>
<body>

{{--@include('knowledgebase::layouts.user_header') --}}
<div class="body-main">
<div>
@php($user = auth()->user())
@php($allsettings = knowledgebaseSupportSettings())
@php($cover_image = $allsettings['cover_image'] ? url('/').'/'.FILE_KNOWLEDGE_BASE_VIEW_PATH.$allsettings['cover_image'] :  asset('assets/modules/knowledgebase/image/home_bg.jpg'))

<section class="bg_image py-5" style="">
    @include('knowledgebase::layouts.user_header')
</section>

@yield('content')
</div>

<!-- footer section start  -->
@include('knowledgebase::layouts.user_footer')
<!-- footer section end  -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="{{asset('assets/common/js/jquery.min.js')}}"></script>
<script src="{{ asset('assets/modules/knowledgebase/js/scripts.js')}}"></script>
<script src="{{ asset('assets/modules/knowledgebase/js/popper.min.js')}}"></script>
<script src="{{ asset('assets/modules/knowledgebase/js/bootstrap.min.js')}}"></script>

<script src="{{asset('assets/common/toast/vanillatoasts.js')}}"></script>

<script>

    (function($) {
        "use strict";
        @if(session()->has('success'))
            window.onload = function () {
            VanillaToasts.create({
                text: '{{session('success')}}',
                backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                type: 'success',
                timeout: 40000
            });
        };
        @elseif(session()->has('dismiss'))
            window.onload = function () {
            VanillaToasts.create({
                text: '{{session('dismiss')}}',
                type: 'warning',
                timeout: 40000
            });
        };
        @elseif($errors->any())
            @foreach($errors->getMessages() as $error)
            window.onload = function () {
            VanillaToasts.create({
                text: '{{ $error[0] }}',
                type: 'warning',
                timeout: 40000
                });
             };
             @break
             @endforeach
        @endif

        /* Add here all your JS customizations */
        $('.number-only').keypress(function (e) {
            alert(11);
            var regex = /^[+0-9+.\b]+$/;
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (regex.test(str)) {
                return true;
            }
            e.preventDefault();
            return false;
        });
        $('.no-regx').keypress(function (e) {
            var regex = /^[a-zA-Z+0-9+\b]+$/;
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (regex.test(str)) {
                return true;
            }
            e.preventDefault();
            return false;
        });

    })(jQuery)

</script>
<script>
    (function($) {
        "use strict";
        $('#search-input-value').keyup(function(e){
            $("#append-search-result").removeClass('display-none');
            var search = $(this).val();
            console.log(search)
            $.ajax({
                type: "POST",
                url: "{{ route('articleSearchSuggestion') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'search': search
                },
                success: function (data) {
                    console.log(data);
                    if(data.success == true){
                        $('#append-search-result').empty().append(data.view);
                    }else{
                        $('#append-search-result').empty().append(data.view);
                    }

                }
            });
        })
    })(jQuery);
</script>
{{-- for web sockets--}}
<script src="https://js.pusher.com/3.0/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.8.1/echo.iife.min.js"></script>
<script>
    let my_env_socket_port = "{{ env('BROADCAST_PORT')}}";
        Pusher.logToConsole = true;
        window.Echo = new Echo({
            broadcaster: 'pusher',
            wsHost: window.location.hostname,
            wsPort: 6006,
            wssPort: 443,
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: 'mt1',
            encrypted: false,
            disableStats: true
        });

</script>
<script>
    jQuery(document).ready(function () {

    Pusher.logToConsole = true;
    let user_id = '{{Auth::id()}}';

    Echo.channel('New-Ticket-Notification-Send-To-User-'+user_id)
        .listen('.Notification', (data) => {
            // console.log(data);
            if(data.success == true)
            {
                var notificationDetails = data.data;
                $('#notification_count').empty().text(notificationDetails.total_notification);
                $('#notification_list').empty().append(notificationDetails.html_view);
            }
        })
});
</script>
@yield('script')
</body>
</html>
