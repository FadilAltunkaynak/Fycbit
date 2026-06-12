@extends('admin.master',['menu'=>'landing_setting','sub_menu'=>'landing'])
@section('title', 'Landing Setting')
@section('style')
@endsection
@section('content')
    <!-- coin-area start -->
    <div class="landing-page-area user-management">
        <div class="page-wraper section-padding">
            <div class="row no-gutters">
                <div class="col-12 col-lg-3 col-xl-2">
                    <ul class="nav nav-pills nav-pill-three landing-tab user-management-nav" id="tab" role="tablist">
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='hero') active @endif" data-toggle="tab"
                            href="#hero">{{__('Hero Section')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='announcement') active @endif" data-toggle="tab"
                            href="#announcement">{{__('Announcement')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='banner') active @endif" data-toggle="tab"
                            href="#banner">{{__('Banner')}}</a>
                        </li>
                        {{-- <li>
                            <a class="nav-link @if(isset($tab) && $tab=='advertisement') active @endif" data-toggle="tab"
                            href="#advertisement">{{__('Advertisement')}}</a>
                        </li> --}}
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='market_trends') active @endif" data-toggle="tab"
                            href="#market_trends">{{__('Market Trends')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='contact') active @endif" data-toggle="tab"
                                href="#contact">{{__('Know More Section')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='advertisement') active @endif" data-toggle="tab"
                            href="#advertisement">{{__('Advertisement')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='links') active @endif" data-toggle="tab"
                                href="#links">{{__('Mobile App Links')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='feature') active @endif" data-toggle="tab"
                                href="#feature">{{__('Features Section')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='blog_section') active @endif" data-toggle="tab"
                                href="#blog_section">{{__('Blog Section')}}</a>
                        </li>
                        <li>
                            <a class="nav-link @if(isset($tab) && $tab=='start_trade') active @endif" data-toggle="tab"
                                href="#start_trade">{{__('Start Trade Section')}}</a>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-lg-9 col-xl-10">
                    <div class="single-tab section-height">
                        <div class="section-body ">
                            <div class="tab-content">
                                <!-- genarel-setting start-->
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='hero')show active @endif " id="hero" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.header')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='announcement')show active @endif " id="announcement" role="tabpanel" aria-labelledby="announcement-setting-tab">
                                    @include('admin.settings.landing.announcement')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='banner')show active @endif " id="banner" role="tabpanel" aria-labelledby="banner-setting-tab">
                                    @include('admin.settings.landing.banner')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='contact')show active @endif "
                                        id="contact" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.customization')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='advertisement')show active @endif "
                                        id="advertisement" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.advertisement')
                                </div>
                                {{-- <div class="tab-pane fade  @if(isset($tab) && $tab=='advertisement')show active @endif "
                                        id="advertisement" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.new_advertisement')
                                </div> --}}
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='market_trends')show active @endif "
                                        id="market_trends" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.trade')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='links')show active @endif "
                                        id="links" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.links')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='feature')show active @endif "
                                        id="feature" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.feature')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='blog_section')show active @endif "
                                        id="blog_section" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.blog_section')
                                </div>
                                <div class="tab-pane fade  @if(isset($tab) && $tab=='start_trade')show active @endif "
                                        id="start_trade" role="tabpanel" aria-labelledby="header-setting-tab">
                                    @include('admin.settings.landing.start_trade')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@endsection
@section('script')
<script>
    $(document).ready(function(){
        var type = "{{isset($adm_setting['download_link_display_type'])?$adm_setting['download_link_display_type']:1}}";
        if(type ==='{{SHOW_LINK}}'){
            $('#display_text').hide();
            $('#display_link').dhow();
        }else
        {
            $('#display_text').show();
            $('#display_link').hide();
        }
    });
    $('#download_link_display_type').change(function(){
        var type = $('#download_link_display_type').val();
        if(type ==='{{SHOW_LINK}}'){
            $('#display_text').hide();
            $('#display_link').show();
        }else if(type ==='{{SHOW_TEXT}}')
        {
            $('#display_text').show();
            $('#display_link').hide();
        }
    });

    var drEvent = $('.dropify').dropify();

    drEvent.on('dropify.beforeClear', function(event, element){
        return confirm("{{ __('Do you really want to delete this image?') }}");
    });
    drEvent.on('dropify.afterClear', function(event, element){
        let name = element.element.name;
        if(name){
            $.get(
                '{{ route("removeAdminImageSettings") }}?slug='+name,
                (response) => {
                    if(response.success || false)
                    {
                        VanillaToasts.create({
                            text: response.message,
                            backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                            type: 'success',
                            timeout: 40000
                        });
                    }else{
                        VanillaToasts.create({
                            text: response.message,
                            type: 'warning',
                            timeout: 40000
                        });
                    }
                }
            )
        }
    });

    $('#announcement-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        stateSave: true,
        retrieve: true,
        bLengthChange: true,
        responsive: false,
        ajax: '{{ route('adminAnnouncementList') }}',
        order: [2, 'desc'],
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,
        headerCallback: function(thead, data, start, end, display) {
            if (data?.length == 0) {
                $(thead).parent().parent().parent().addClass("width-full")
                $(thead).parent().parent().addClass("width-full")
            }
        },
        language: {
            paginate: {
                next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
            }
        },
        columns: [
            {"data": "title", "orderable": false},
            {"data": "status", "orderable": false},
            {"data": "created_at", "orderable": false},
            {"data": "actions", "orderable": false}
        ],
    });

    $('#banner-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        stateSave: true,
        retrieve: true,
        bLengthChange: true,
        responsive: false,
        ajax: '{{ route('adminBannerList') }}',
        order: [2, 'desc'],
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,
        headerCallback: function(thead, data, start, end, display) {
            if (data?.length == 0) {
                $(thead).parent().parent().parent().addClass("width-full")
                $(thead).parent().parent().addClass("width-full")
            }
        },
        language: {
            paginate: {
                next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
            }
        },
        columns: [
            {"data": "title", "orderable": false},
            {"data": "status", "orderable": false},
            {"data": "created_at", "orderable": false},
            {"data": "actions", "orderable": false}
        ],
    });

    $('#feature-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        stateSave: true,
        retrieve: true,
        bLengthChange: true,
        responsive: false,
        ajax: '{{ route('adminFeatureList') }}',
        order: [3, 'desc'],
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,
        headerCallback: function(thead, data, start, end, display) {
            if (data?.length == 0) {
                $(thead).parent().parent().parent().addClass("width-full")
                $(thead).parent().parent().addClass("width-full")
            }
        },
        language: {
            paginate: {
                next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
            }
        },
        columns: [
            {"data": "feature_title", "orderable": false},
            {"data": "status", "orderable": false},
            {"data": "feature_url", "orderable": false},
            {"data": "created_at", "orderable": true},
            {"data": "actions", "orderable": false}
        ],
    });
</script>
@endsection
