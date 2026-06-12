@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
    @include('futureTrade::layouts.sidebar', ['menu' => 'dashboard'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{ __('Future Trade Dashboard') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- Risk Monitor & Top Traders -->
    <div class="row mt-4">
        <div class="col-xl-8">
            <div class="user-management user-chart card">
                <div class="card-body">
                    <div class="custom-breadcrumb">
                        <ul>
                            <li class="active-item">{{ __('Risk Monitor (High Exposure)') }}</li>
                        </ul>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless custom-table">
                            <thead>
                                <tr>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Pair') }}</th>
                                    <th>{{ __('Side') }}</th>
                                    <th>{{ __('Unrealized PnL') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($risk_monitor as $position)
                                    <tr>
                                        <td>{{ $position->user->first_name }} {{ $position->user->last_name }}</td>
                                        <td>{{ $position->coinPair->trade_coin_code }}/{{ $position->coinPair->base_coin_code }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge {{ $position->order_type == \Modules\FutureTrade\Emum\OrderType::BUY ? 'badge-success' : 'badge-danger' }}">
                                                {{ $position->order_type->label(true) }}
                                            </span>
                                        </td>
                                        <td class="{{ $position->pnl >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $position->pnl >= 0 ? '+' : '' }}{{ number_format($position->pnl, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="user-management user-chart card">
                <div class="card-body">
                    <div class="custom-breadcrumb">
                        <ul>
                            <li class="active-item">{{ __('Top Traders (24h)') }}</li>
                        </ul>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless custom-table">
                            <thead>
                                <tr>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Volume') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($top_traders as $trader)
                                    <tr>
                                        <td>{{ $trader->buyer->email ?? 'Unknown' }}</td>
                                        <td class="font-weight-bold">${{ number_format($trader->volume, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="user-chart mt-4">
        <div class="row">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="card">
                    <div class="card-body">
                        <div class="card-top">
                            <h4>{{ __('Buy Orders') }}</h4>
                        </div>
                        <p class="subtitle">{{ __('Current Year') }}</p>
                        <div id="buyChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="card-top">
                            <h4>{{ __('Sell Orders') }}</h4>
                        </div>
                        <p class="subtitle">{{ __('Current Year') }}</p>
                        <div id="sellChart"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="card">
                    <div class="card-body">
                        <div class="card-top">
                            <h4>{{ __('Trade Volume') }}</h4>
                        </div>
                        <p class="subtitle">{{ __('Current Year') }}</p>
                        <div id="tradeChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="card-top">
                            <h4>{{ __('Position Count') }}</h4>
                        </div>
                        <p class="subtitle">{{ __('Current Year') }}</p>
                        <div id="positionChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="custom-breadcrumb">
                <ul>
                    <li class="active-item">{{ __('Recent Orders') }}</li>
                </ul>
            </div>
            <div class="user-management user-chart card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless custom-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Time') }}</th>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Side') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_orders as $order)
                                    <tr>
                                        <td class="text-muted">{{ $order->created_at->diffForHumans() }}</td>
                                        <td>{{ $order->user->first_name ?? '' }} {{ $order->user->last_name ?? '' }}</td>
                                        <td>
                                            {!! $order->order_type->bothLabelWithColor() !!}
                                        </td>
                                        <td>{{ trim_num($order->amount, $order->coinPair->trade_decimal) }}
                                            {{ $order->coinPair->base_coin_code }}</td>
                                        <td>{{ trim_num($order->price, $order->coinPair->trade_decimal) }}
                                            {{ $order->coinPair->trade_coin_code }}</td>
                                        <td>
                                            {!! $order->status->bothLabelWithColor() !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function ($) {
            "use strict";
        })(jQuery)

        let chartOptionsHandler = (Color, chartData, name) => ({
            chart: {
                height: 270,
                type: "area",
                fontFamily: 'Nunito, sans-serif',
                zoom: {
                    enabled: false,
                },
                toolbar: {
                    show: false,
                },
            },
            dataLabels: {
                enabled: false
            },
            colors: [Color],
            series: [{
                name: name,
                data: chartData
            }],
            stroke: {
                show: true,
                curve: 'smooth',
                width: 2,
                lineCap: 'square',
            },
            fill: {
                colors: [Color],
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.9,
                    stops: [0, 90, 100],
                    shade: 'dark'
                }
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                axisTicks: {
                    show: true,
                    borderType: 'solid',
                    color: Color,
                    height: 6,
                    offsetX: 0,
                    offsetY: 0
                },
                axisBorder: {
                    show: true,
                    color: Color,
                    height: 1,
                    width: '100%',
                    offsetX: 0,
                    offsetY: 0
                },
                labels: {
                    show: true,
                    style: {
                        colors: '#cbcfd7',
                        fontSize: '12px',
                        fontFamily: 'Helvetica, Arial, sans-serif',
                        fontWeight: 400,
                        cssClass: 'apexcharts-xaxis-label',
                    }
                },
            },
            yaxis: {
                show: true,
                labels: {
                    show: true,
                    align: 'right',
                    minWidth: 0,
                    tickAmount: 10,
                    style: {
                        colors: '#cbcfd7',
                        fontSize: '12px',
                        fontFamily: 'Helvetica, Arial, sans-serif',
                        fontWeight: 400,
                        cssClass: 'apexcharts-yaxis-label',
                    },
                    offsetX: 0,
                    offsetY: 0,
                    rotate: 0,
                    formatter: (value) => {
                        return value
                    },
                },
                opposite: false,
            },
            grid: {
                borderColor: '#191e3a',
                strokeDashArray: 5,
                xaxis: {
                    lines: {
                        show: true,
                    },
                },
                yaxis: {
                    lines: {
                        show: false,
                    },
                },
            },
            tooltip: {
                theme: 'dark'
            }
        });

        let buyData = {!! json_encode($monthly_buy) !!};
        let sellData = {!! json_encode($monthly_sell) !!};

        var buyOptions = chartOptionsHandler("#718a71", buyData, "Buy Orders");
        var sellOptions = chartOptionsHandler("#ea98a0", sellData, "Sell Orders");

        var buyChart = new ApexCharts(document.querySelector("#buyChart"), buyOptions);
        var sellChart = new ApexCharts(document.querySelector("#sellChart"), sellOptions);

        buyChart.render();
        sellChart.render();

        // Trade Volume Chart (Bar)
        let tradeData = {!! json_encode($monthly_trade_volume) !!};
        var tradeOptions = chartOptionsHandler("#aecddf", tradeData, "Trade Volume");
        tradeOptions.chart.type = "bar";
        tradeOptions.fill.type = "solid";
        var tradeChart = new ApexCharts(document.querySelector("#tradeChart"), tradeOptions);
        tradeChart.render();

        // Position Count Chart (Line)
        let positionData = {!! json_encode($monthly_position_count) !!};
        var positionOptions = chartOptionsHandler("#ffc107", positionData, "Position Count");
        positionOptions.chart.type = "line";
        positionOptions.fill.type = "solid";
        positionOptions.stroke.curve = "straight";
        var positionChart = new ApexCharts(document.querySelector("#positionChart"), positionOptions);
        positionChart.render();
    </script>
@endsection
