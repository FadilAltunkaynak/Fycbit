@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('blognews::layouts.Sidebar',['menu'=>'dashboard'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('News Dashboard')}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <div class="row">
                            <div class="col-md-8 col-sm-12">
                                <canvas id="withdrawalChart"></canvas>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card status-card status-card-bg-read">
                                            <div class="card-body py-0">
                                                <div class="status-card-inner">
                                                    <div class="content">
                                                        <p>{{__('Total News')}}</p>
                                                        <h3>{{ $news ?? 0 }}</h3>
                                                        <a href="{{ route('allNewsPage') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                                                    </div>
                                                    <div class="icon">
                                                        <img src="{{asset('assets/admin/images/status-icons/docfile.svg')}}" class="img-fluid" alt="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card status-card status-card-bg-read">
                                            <div class="card-body py-0">
                                                <div class="status-card-inner">
                                                    <div class="content">
                                                        <p>{{__('Total Published News')}}</p>
                                                        <h3>{{ $published ?? 0 }}</h3>
                                                        <a href="{{ route('allNewsPage') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                                                    </div>
                                                    <div class="icon">
                                                        <img src="{{asset('assets/admin/images/status-icons/docfile.svg')}}" class="img-fluid" alt="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card status-card status-card-bg-read">
                                            <div class="card-body py-0">
                                                <div class="status-card-inner">
                                                    <div class="content">
                                                        <p>{{__('Total Active News')}}</p>
                                                        <h3>{{ $active ?? 0 }}</h3>
                                                        <a href="{{ route('allNewsPage') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                                                    </div>
                                                    <div class="icon">
                                                        <img src="{{asset('assets/admin/images/status-icons/docfile.svg')}}" class="img-fluid" alt="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card status-card status-card-bg-read">
                                            <div class="card-body py-0">
                                                <div class="status-card-inner">
                                                    <div class="content">
                                                        <p>{{__('Total Comments')}}</p>
                                                        <h3>{{ $comments ??  0}}</h3>
                                                        <a href="{{ route('NewsComment') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                                                    </div>
                                                    <div class="icon">
                                                        <img src="{{asset('assets/admin/images/status-icons/docfile.svg')}}" class="img-fluid" alt="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
    <script src="{{asset('assets/common/chart/chart.min.js')}}"></script>
    <script>
        var ctx = document.getElementById('withdrawalChart').getContext("2d");
            var withdrawalChart = new Chart(ctx, {
                type: 'line',
                yaxisname: "visitor",

                data: {
                    labels: {!! $chart_title !!},
                    datasets: [{
                        label: "Current Week Visitor",
                        borderColor: "#f691be",
                        pointBorderColor: "#f691be",
                        pointBackgroundColor: "#f691be",
                        pointHoverBackgroundColor: "#f691be",
                        pointHoverBorderColor: "#D1D1D1",
                        pointBorderWidth: 4,
                        pointHoverRadius: 2,
                        pointHoverBorderWidth: 1,
                        pointRadius: 3,
                        fill: false,
                        borderWidth: 3,
                        data: {!! $chart_value !!}
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
                                // padding: 20,
                                // max: 1000
                            },
                            gridLines: {
                                drawTicks: false,
                                display: false
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                zeroLineColor: "transparent",
                                drawTicks: true,
                                display: false
                            },
                            ticks: {
                                // padding: 20,
                                fontColor: "#928F8F",
                                fontStyle: "bold",
                                // max: 10000,
                                autoSkip: false
                            }
                        }]
                    }
                }
            });
    </script>
@endsection
