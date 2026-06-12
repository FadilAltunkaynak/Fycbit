@extends('admin.master')
@section('title', isset($title) ? $title : __('Knowledge Base Dashboard'))
@section('style')
@endsection
@section('sidebar')
@include('knowledgebase::layouts.sidebar',['menu'=>'knowledgebase_dashboard'])
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
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-read">
                    <div class="card-body py-0">
                        <div class="status-card-inner">
                            <div class="content">
                                <p>{{__('Total Article Category')}}</p>
                                <h3>{{$total_category_of_article}}</h3>
                                <a href="{{ route('knowledgebase_categoryList') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                            </div>
                            <div class="icon">
                                <img src="{{asset('assets/modules/knowledgebase/icon/category.svg')}}" class="img-fluid" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-read">
                    <div class="card-body py-0">
                        <div class="status-card-inner">
                            <div class="content">
                                <p>{{__('Total Article Sub Category')}}</p>
                                <h3>{{$total_subcategory_of_article}}</h3>
                                <a href="{{ route('knowledgebase_subCategoryList') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                            </div>
                            <div class="icon">
                                <img src="{{asset('assets/modules/knowledgebase/icon/category.svg')}}" class="img-fluid" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="card status-card status-card-bg-read">
                    <div class="card-body py-0">
                        <div class="status-card-inner">
                            <div class="content">
                                <p>{{__('Total Article')}}</p>
                                <h3>{{$total_article}}</h3>
                                <a href="{{ route('knowledgebase_subCategoryList') }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                            </div>
                            <div class="icon">
                                <img src="{{asset('assets/modules/knowledgebase/icon/article.svg')}}" class="img-fluid" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-4 col-md-6 col-12 mb-4">
            <div class="card status-card status-card-bg-read">
                <div class="card-body py-0">
                    <div class="status-card-inner">
                        <div class="content">
                            <p>{{__('Total Unseen Ticket')}}</p>
                            <h3>{{$total_unseen_ticket}}</h3>
                            <a href="{{ route('support_ticket_list').'?assigned_type='.TICKET_ASSIGN_STATUS_ASSIGNED }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                        </div>
                        <div class="icon">
                            <img src="{{asset('assets/modules/knowledgebase/icon/ticket-warning.svg')}}" class="img-fluid" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 col-12 mb-4">
            <div class="card status-card status-card-bg-read">
                <div class="card-body py-0">
                    <div class="status-card-inner">
                        <div class="content">
                            <p>{{__('Total Seen Ticket')}}</p>
                            <h3>{{$total_seen_ticket}}</h3>
                            <a href="{{ route('support_ticket_list').'?assigned_type='.TICKET_ASSIGN_STATUS_ASSIGNED }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                        </div>
                        <div class="icon">
                            <img src="{{asset('assets/modules/knowledgebase/icon/ticket-success.svg')}}" class="img-fluid" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 col-12 mb-4">
            <div class="card status-card status-card-bg-read">
                <div class="card-body py-0">
                    <div class="status-card-inner">
                        <div class="content">
                            <p>{{__('Total Unassigned Ticket')}}</p>
                            <h3>{{$total_unassigned_ticket}}</h3>
                            <a href="{{ route('support_ticket_list').'?assigned_type='.TICKET_ASSIGN_STATUS_UNASSIGNED }}" class=" mt-3 btn btn-sm btn-warning">{{__("Show More")}}</a>
                        </div>
                        <div class="icon">
                            <img src="{{asset('assets/modules/knowledgebase/icon/ticket-danger.svg')}}" class="img-fluid" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-top">
                        <h4 class="text-white">{{__('Ticket Status')}}</h4>
                    </div>
                    <p class="subtitle">{{__('Current Year')}}</p>
                    <div class="myPieChart">
                        <canvas width="300" height="300" id="myPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="card-top">
                        <h4 class="text-white">{{__('Monthly Ticket')}}</h4>
                    </div>
                    <p class="subtitle">{{__('Current Year')}}</p>
                    <canvas id="myTicketChart"></canvas>
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

        var ctx = document.getElementById('myPieChart').getContext("2d")
            var depositChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [
                    'Pending',
                    'Open',
                    'Close',
                    'Close Forever'
                ],
                datasets: [{
                    label: 'Ticket Status',
                    data: {!! json_encode($ticket_status) !!},
                    backgroundColor: [
                    '#0000FF',
                    '#4C9900',
                    '#FFFF00',
                    '#990000'
                    ],
                    hoverOffset: 4
                }],

                }
            });
            var ctx = document.getElementById('myTicketChart').getContext("2d")
            var depositChart = new Chart(ctx, {
                type: 'bar',
                yaxisname: "Monthly Ticket",

                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: [{
                        label: "Monthly Ticket",
                        borderColor: "#1cf676",
                        pointBorderColor: "#1cf676",
                        pointBackgroundColor: "#1cf676",
                        pointHoverBackgroundColor: "#1cf676",
                        pointHoverBorderColor: "#A0A0A0",
                        pointBorderWidth: 4,
                        pointHoverRadius: 2,
                        pointHoverBorderWidth: 1,
                        pointRadius: 3,
                        fill: false,
                        borderWidth: 3,
                        data: {!! json_encode($monthly_ticket) !!},
                        backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)',
                        'FFCCCC',
                        '66FFB2',
                        '808080',
                        '99CCFF',
                        'FF0000'
                        ],
                        borderColor: [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb(201, 203, 207)',
                        'rgb(255, 99, 132)',
                        'rgb(255, 99, 132)',
                        'rgb(255, 99, 132)',
                        'rgb(255, 99, 132)',
                        'rgb(255, 99, 132)',
                        ],
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
