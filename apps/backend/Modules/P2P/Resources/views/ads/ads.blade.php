@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'ads'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-6">
                <ul>
                    <li class="active-item">{{__('Ads List')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
   <div class="user-management pt-4">
        <div class="row no-gutters">
            <div class="col-12 col-lg-3 col-xl-2">
                <ul class="nav user-management-nav mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='buy') active @endif nav-link " id="pills-user-tab"
                            data-toggle="pill" data-controls="capcha" href="#buy" role="tab"
                            aria-controls="pills-user" aria-selected="true">
                            <span>{{ __('Buy List') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="@if(isset($tab) && $tab=='sell') active @endif nav-link " id="pills-user-tab"
                            data-toggle="pill" data-controls="capcha" href="#sell" role="tab"
                            aria-controls="pills-user" aria-selected="true">
                            <span>{{ __('Sell List') }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane show @if(isset($tab) && $tab=='buy')  active @endif" id="buy"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('p2p::ads.buy')
                    </div>
                    <div class="tab-pane show @if(isset($tab) && $tab=='sell')  active @endif" id="sell"
                         role="tabpanel" aria-labelledby="pills-user-tab">
                        @include('p2p::ads.sell')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection
@section('script')
    @section('script')
    <script>
        (function($) {
            "use strict";

            $('.buy_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                bLengthChange: true,
                responsive: true,
                language: {
                    paginate: {
                        next: 'Next &#8250;',
                        previous: '&#8249; Previous'
                    }
                },
                ajax: '{{route('adsBuyList')}}',
                order: [2, 'desc'],
                autoWidth: false,
                columns: [
                    {"data": "user", searchable: false},
                    {"data": "coin", searchable: false},
                    {"data": "amount", searchable: false},
                    {"data": "coin_rate", searchable: false},
                    {"data": "available", searchable: false},
                    {"data": "created_at", searchable: false},
                ]
            });


            $('.sell_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                bLengthChange: true,
                responsive: true,
                language: {
                    paginate: {
                        next: 'Next &#8250;',
                        previous: '&#8249; Previous'
                    }
                },
                ajax: '{{route('adsSellList')}}',
                order: [2, 'desc'],
                autoWidth: false,
                columns: [
                    {"data": "user", searchable: false},
                    {"data": "coin", searchable: false},
                    {"data": "amount", searchable: false},
                    {"data": "coin_rate", searchable: false},
                    {"data": "available", searchable: false},
                    {"data": "created_at", searchable: false},
                ]
            });
        })(jQuery);
    </script>
@endsection
@endsection
