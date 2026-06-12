<div class="sidebar">
    <!-- logo -->
    <div class="logo">
        <a href="{{route('adminDashboard')}}">
            <img src="{{show_image(Auth::user()->id,'logo')}}" class="img-fluid" alt="">
        </a>
    </div><!-- /logo -->

    <!-- sidebar menu -->
    <div class="sidebar-menu">
        <nav>
            <ul id="metismenu">


{!! mainMenuRenderer('demoCoinList',__('Coin Setting'),$menu ?? '','coins','dashboard.svg') !!}
{!! mainMenuRenderer('demoCoinPairs',__('Coin Pair'),$menu ?? '','coin_pair','dashboard.svg') !!}

{!! subMenuRenderer(__('Trade Reports'),$menu ?? '', 'trade','trade-report.svg',[
    ['route' => 'demoAllOrdersHistoryBuy', 'title' => __('Buy Order History'),'tab' => $sub_menu ?? '', 'tab_compare' => 'buy_order', 'route_param' => NULL ],
    ['route' => 'demoAllOrdersHistorySell', 'title' => __('Sell Order History'),'tab' => $sub_menu ?? '', 'tab_compare' => 'sell_order', 'route_param' => NULL ],
    ['route' => 'demoAllOrdersHistoryStopLimit', 'title' => __('Stop Limit Order History'),'tab' => $sub_menu ?? '', 'tab_compare' => 'stop_limit', 'route_param' => NULL ],
    ['route' => 'demoAllTransactionHistory', 'title' => __('Transaction History'),'tab' => $sub_menu ?? '', 'tab_compare' => 'transaction', 'route_param' => NULL ],
]) !!}

{!! mainMenuRenderer('adminDashboard',__('Admin Dashboard'),$menu ?? '','dashboard','dashboard.svg') !!}

            </ul>
        </nav>
    </div><!-- /sidebar menu -->

</div>
