<div class="sidebar">
    <!-- logo -->
    <div class="logo">
        <a href="{{route('adminDashboard')}}">
            <img src="{{show_image(authId(),'logo')}}" class="img-fluid" alt="">
        </a>
    </div>
    <!-- /logo -->

    <!-- sidebar menu -->
    <div class="sidebar-menu">
        <nav>
            <ul id="metismenu">
                @include('futureTrade::layouts.sidebar-items')
            </ul>
        </nav>
    </div>
    <!-- /sidebar menu -->
</div>
