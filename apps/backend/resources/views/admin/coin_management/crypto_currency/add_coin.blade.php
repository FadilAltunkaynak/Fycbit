@extends('admin.master', ['menu' => 'coin_namagement', 'sub_menu' => 'crypto_currency'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection

@php
    use App\Enums\YesNoStatus;
@endphp

@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{__('Crypto')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div>
                        {{Form::open(['route' => 'adminSaveCoin', 'files' => true])}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Type')}}</div>
                                        <select class="form-control" name="currency_type" id="currency_type">
                                            @foreach (getTradeCurrencyType() as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Coin Full Name')}}</div>
                                        <input type="text" class="form-control" name="name" value="{{old('name')}}">
                                        <pre class="text-danger">{{$errors->first('name')}}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Coin Code/Type')}}</div>
                                        <div class="d-none" id="coin_type_input">
                                            <input id="coin_type_input_" type="text" class="form-control"
                                                value="{{ old('coin_type') }}">
                                        </div>
                                        <div class="" id="coin_type_select">
                                            <select id="coin_type_select_" class="form-control">
                                                <option value="">{{ __('Select') }}</option>
                                                @foreach ($currency as $currency)
                                                    <option value="{{ $currency->code }}" data-price="{{ $currency->rate }}">
                                                        {{ $currency->code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <small>{{__('Please make sure your coin code (or type) is right. Never input wrong coin code (or type)')}}</small>
                                        <pre class="text-danger">{{$errors->first('coin_type')}}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Coin Decimal')}}</div>
                                        <input type="number" class="form-control" name="decimal" value="{{old('decimal')}}">
                                        <pre class="text-danger">{{$errors->first('decimal')}}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="active_provider" class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-label">{{ __('Select active coin provider') }}</div>
                                    <div class="cp-select-area">
                                        <select name="active_provider" id="" class="form-control">
                                            <option value="">{{ __('Select provider') }}</option>
                                            @foreach ($providers as $key => $item)
                                                <option value="{{ $key }}" {{ $key == old('active_provider') ? 'selected' : '' }}>
                                                    {{ $item }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('active_provider')
                                            <pre class="text-danger">{{ $message }}</pre>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="coin_rate_api" class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{__('Get coin rate from api ?')}}</label>
                                    <div class="cp-select-area">
                                        <select name="get_price_api" id="" class="form-control">
                                            @foreach (YesNoStatus::getList() as $kStatus => $vStatus)
                                                <option value="{{$kStatus}}" {{ $kStatus == old('get_price_api', YesNoStatus::NO->value) ? 'selected' : ''}}>
                                                    {{ $vStatus }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <small class="text-warning">{{__('If no , please input the coin price')}}</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Coin Price (in USD)')}}</div>
                                        <div class="input-group w-85 ">
                                            <input type="text" class="form-control" name="coin_price"
                                                value="{{ old('coin_price') }}">
                                            <div class="input-group-append">
                                                <span class="input-group-text px-4"><span
                                                        class="currency text-warning">USD</span></span>
                                            </div>
                                        </div>
                                        <small>{{__('Coin price in USD. it will update by currency api regularly')}}</small>
                                        <pre class="text-danger">{{$errors->first('coin_price')}}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <button type="submit" class="btn theme-btn">{{$button_title}}</button>
                            </div>
                        </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->
@endsection
@section('script')
    <script>
        "use strict";
        let currency = 1;

        function add_coin_ui_change() { // name="coin_type"
            let coin_type_input = document.getElementById("coin_type_input");
            let coin_type_select = document.getElementById("coin_type_select");


            if (currency == 1) {
                $("#coin_type_input_").attr("name", "coin_type");
                $("#coin_type_select_").attr("name", "");

                coin_type_select.classList.add("d-none");
                coin_type_input.classList.remove("d-none");
                $("#coin_api").show()
                $("#coin_rate_api").show()
            }

            if (currency == 2) {
                $("#coin_type_input_").attr("name", "")
                $("#coin_type_select_").attr("name", "coin_type")

                coin_type_select.classList.remove("d-none");
                coin_type_input.classList.add("d-none");

                $("#coin_api").hide();
                $("#coin_rate_api").hide();
            }
        }

        function currency_change(event) {
            currency = event.target.value;
            add_coin_ui_change();
        }
        add_coin_ui_change();
        $("#currency_type").on("change", currency_change);
        $("#coin_type_select_").on("change", (e) => {
            let rate = $("#coin_type_select_").find(':selected').data("price")
            $('input[name="coin_price"]').val((1 / rate).toFixed(8));
        });
    </script>
@endsection