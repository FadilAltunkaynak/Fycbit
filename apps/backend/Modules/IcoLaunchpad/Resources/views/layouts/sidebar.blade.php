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
                    {!! mainMenuRenderer('adminDashboard',__('Admin Dashboard'),$menu ?? '','dashboard','dashboard.svg') !!}
                    {!! mainMenuRenderer('dashboardICO',__('ICO Dashboard'),$menu ?? '','ico_dashboard','dashboard.svg') !!}
                    {!! mainMenuRenderer('dynamicFormCreateICO',__('Ico Form'),$menu ?? '','dynamic_form_settings','coin.svg') !!}
                    {!! mainMenuRenderer('submitted-form-ico',__('Launchpad Request'),$menu ?? '','submitted_form_list','Notification.svg') !!}
                    {!! mainMenuRenderer('listICO',__('Ico Token List'),$menu ?? '','ico_list','menu.svg') !!}
                    {!! mainMenuRenderer('buyTokenList',__('Token Buy List'),$menu ?? '','ico_token_buy','trade-report.svg') !!}
                    {!! mainMenuRenderer('getICOWithdrawhistoryList',__('Withdraw List'),$menu ?? '','ico_withdraw_history','Transaction-1.svg') !!}
                    {!! mainMenuRenderer('IcoPaymentMethod',__('Payment Method'),$menu ?? '','ico_payment_list','wallet.svg') !!}
                    {!! mainMenuRenderer('setupDynamicForm',__('Form Setting'),$menu ?? '','setup_dynamic_form','settings.svg') !!}
                    {!! mainMenuRenderer('launchpadPageSettings',__('Launchpad Setting'),$menu ?? '','launchapad_page_settings','settings.svg') !!}
                    {!! mainMenuRenderer('launchpadFeatureList',__('Launchpad Feature'),$menu ?? '','launchapad_feature_list','FAQ.svg') !!}
                    {!! mainMenuRenderer('icoSettings',__('Settings'),$menu ?? '','ico_settings','settings.svg') !!}
            </ul>
        </nav>
    </div><!-- /sidebar menu -->

</div>
