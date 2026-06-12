@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('p2p::layouts.sidebar',['menu'=>'gift_card', 'sub_menu'=>'advertisements'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Advertisement History')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <!-- User Management -->
    <div class="user-management wallet-transaction-area">
        <div class="row no-gutters">
            <div class="col-12 col-lg-2">
                <ul class="nav wallet-transaction user-management-nav mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id=all_ads" data-toggle="pill" href="#all_ads_tab"
                            role="tab" aria-controls="all_ads_tab" aria-selected="true">
                            {{__('All Advertisement')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="active_ads" data-toggle="pill" href="#active_ads_tab"
                            role="tab" aria-controls="active_ads_tab" aria-selected="true">
                            {{__('Actived Advertisement')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="deactive_ads" data-toggle="pill"
                            href="#deactive_ads_tab" role="tab" aria-controls="deactive_ads_tab"
                            aria-selected="true">
                            {{__('Deactived Advertisement')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="complete_ads" data-toggle="pill"
                            href="#complete_ads_tab" role="tab" aria-controls="complete_ads_tab"
                            aria-selected="true">
                            {{__('Complete Advertisement')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="ongoing_ads" data-toggle="pill"
                            href="#ongoing_ads_tab" role="tab" aria-controls="ongoing_ads_tab"
                            aria-selected="true">
                            {{__('Ongoing Advertisement')}}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-12 col-lg-10">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="all_ads_tab" role="tabpanel"
                            aria-labelledby=all_ads">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="all_table" class="table table-borderless custom-table display text-left"
                                        width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('User')}}</th>
                                        <th class="all">{{__('Price')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Status')}}</th>
                                        <th class="all">{{__('Created At')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="active_ads_tab" role="tabpanel"
                            aria-labelledby="active_ads">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="active_table"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('User')}}</th>
                                        <th class="all">{{__('Price')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Status')}}</th>
                                        <th class="all">{{__('Created At')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="deactive_ads_tab" role="tabpanel"
                            aria-labelledby="deactive_ads">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="deactive_table"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('User')}}</th>
                                        <th class="all">{{__('Price')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Status')}}</th>
                                        <th class="all">{{__('Created At')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="complete_ads_tab" role="tabpanel"
                            aria-labelledby="complete_ads">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="complete_table"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('User')}}</th>
                                        <th class="all">{{__('Price')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Status')}}</th>
                                        <th class="all">{{__('Created At')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="ongoing_ads_tab" role="tabpanel"
                            aria-labelledby="ongoing_ads">
                        <div class="table-area">
                            <div class="table-responsive">
                                <table id="ongoing_table"
                                        class="table table-borderless custom-table display text-left" width="100%">
                                    <thead>
                                    <tr>
                                        <th class="all">{{__('User')}}</th>
                                        <th class="all">{{__('Price')}}</th>
                                        <th class="all">{{__('Amount')}}</th>
                                        <th class="all">{{__('Status')}}</th>
                                        <th class="all">{{__('Created At')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->
@endsection

@section('script')
    <script>
        (function($) {
            "use strict";

            function renderHistoryTable(url,table){
                $(table).DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 25,
                    responsive: true,
                    ajax: url,
                    // order: [7, 'desc'],
                    autoWidth: false,
                    language: {
                        paginate: {
                            next: 'Next &#8250;',
                            previous: '&#8249; Previous'
                        }
                    },
                    columns: [
                        {"data": "user"},
                        {"data": "price"},
                        {"data": "amount"},
                        {"data": "status"},
                        {"data": "created_at"},
                    ]
                });
            }
            renderHistoryTable('{{route('getGiftCardAdsHistory')}}/?status='+'all','#all_table');
            renderHistoryTable('{{route('getGiftCardAdsHistory')}}/?status='+'1','#active_table');
            renderHistoryTable('{{route('getGiftCardAdsHistory')}}/?status='+'0','#deactive_table');
            renderHistoryTable('{{route('getGiftCardAdsHistory')}}/?status='+'2','#complete_table');
            renderHistoryTable('{{route('getGiftCardAdsHistory')}}/?status='+'4','#ongoing_table');
        })(jQuery);
    </script>
@endsection
