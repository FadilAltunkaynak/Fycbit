@extends('admin.master')
@section('title', isset($title) ? $title : __('ICO Dashboard'))
@section('style')
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'ico_dashboard'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-9">
                <ul>
                    <li class="active-item">{{$title}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <div class="user-management">

<div class="row">
    @if(isset($recent_tokens) && !empty($recent_tokens))
        @foreach($recent_tokens as $token)
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-read">
                    <div class="card-body py-0">
                        <div class="status-card-inner">
                            <div class="content">
                                <p>{{ getNetworkNameByType($token->network ?? '') }}</p>
                                <p>
                                
                                    {{ __('Token Name') }}: {{ $token->token_name }} <br/>
                                    {{ __('Token Type') }}: {{ $token->coin_type }} <br/>
                                    {{ __('Base Coin') }}: {{ $token->base_coin }} <br/>
                                    {{ __('OwnBy') }}: {{ $token->user->first_name.' '.$token->user->last_name }} <br/>

                                </p>
                            {{--    <a href="{{ route('adminUsers') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a> --}}
                            </div>
                            <div class="icon">
                                <img src="{{asset('assets/admin/images/status-icons/money.svg')}}" class="img-fluid" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    @if(false)
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-average">
            <div class="card-body py-0">
                <div class="status-card-inner">
                    <div class="content">
                        <p>{{__('Launchpad Request')}}</p>
                        <h3>{{ $launchpad_request }}</h3>
                        <a href="{{ route('adminUserCoinList') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                    </div>
                    <div class="icon">
                        <img src="{{asset('assets/admin/images/status-icons/team.svg')}}" class="img-fluid" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 col-12 mb-4">
        <div class="card status-card status-card-bg-average">
            <div class="card-body py-0">
                <div class="status-card-inner">
                    <div class="content">
                        <p>{{__('Total Earning')}}</p>
                        <h3>{{ $total_earn }}</h3>
                        <a href="{{ route('adminEarningReport') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                    </div>
                    <div class="icon">
                        <img src="{{asset('assets/admin/images/status-icons/funds.svg')}}" class="img-fluid" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

    <!-- user chart -->
    <div class="user-chart mt-0">
        <div class="row">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="card">
                    <div class="card-body">
                        <div class="card-top">
                            <h4>{{__('Total Sale')}}</h4>
                        </div>
                        <p class="subtitle">{{__('Current Year')}}</p>
                        <canvas id="depositChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
@endsection
@section('script')
    <script src="{{asset('assets/common/chart/chart.min.js')}}"></script>
    <script>
        (function($) {
            "use strict";
            var ctx = document.getElementById('depositChart').getContext("2d")
            console.log(ctx);
            var depositChart = new Chart(ctx, {
                type: 'line',
                yaxisname: "Monthly Deposit",

                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: [{
                        label: "Monthly Deposit",
                        borderColor: "#1cf676",
                        pointBorderColor: "#1cf676",
                        pointBackgroundColor: "#1cf676",
                        pointHoverBackgroundColor: "#1cf676",
                        pointHoverBorderColor: "#D1D1D1",
                        pointBorderWidth: 4,
                        pointHoverRadius: 2,
                        pointHoverBorderWidth: 1,
                        pointRadius: 3,
                        fill: false,
                        borderWidth: 3,
                        data:  {!! json_encode($monthly_sale ?? []) !!}
                    }]
                },
                options: {
                    legend: {
                        position: "bottom",
                        display: true,
                        labels: {
                            fontColor: '#928F8F'
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                fontColor: "#928F8F",
                                fontStyle: "bold",
                                beginAtZero: true,
                                // maxTicksLimit: 5,
                                padding: 20
                            },
                            gridLines: {
                                drawTicks: false,
                                display: false
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                zeroLineColor: "transparent",
                                drawTicks: false,
                                display: false
                            },
                            ticks: {
                                padding: 20,
                                fontColor: "#928F8F",
                                fontStyle: "bold"
                            }
                        }]
                    }
                }
            });

            

        })(jQuery)
    </script>
@endsection
