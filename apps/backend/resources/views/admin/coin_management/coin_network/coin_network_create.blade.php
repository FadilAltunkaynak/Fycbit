@extends('admin.master', ['menu' => 'coin_namagement', 'sub_menu' => 'coin_network'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    @php
        use App\Enums\CoinType;
    @endphp
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{__('Network')}}</li>
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
                        {{Form::open(['route' => 'createCoinNetworkProcess', 'files' => true, 'method' => 'POST'])}}
                        @if(isset($item->id))
                            <input type="hidden" name="uid" value="{{ $item->uid }}" />
                        @endif

                        <input type="hidden" id="base_type" name="base_type" />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Currency')}}</div>
                                        <select name="currency_id" id="" class="form-control">
                                            <option>{{ __("Select a currency") }}</option>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}" {{ $currency->id == old('currency_id') ? "selected" : "" }}>
                                                    {{ $currency->coin_type }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Network')}}</div>
                                        <select name="network_id" id="network_id" class="form-control">
                                            <option>{{ __("Select a network") }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Type')}}</div>
                                        <select name="type" id="coin_type" class="form-control">
                                            <option>{{ __("Select a type") }}</option>
                                            @foreach($coinTypes as $key => $val)
                                                <option value="{{ $key }}" {{ $key == old('type', CoinType::TOKEN_COIN->value) ? "selected" : "" }}>
                                                    {{ $val }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 contract_address">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Contact Address')}}</div>
                                        <input type="text" class="form-control" name="contract_address"
                                            value="{{ old('contract_address') }}" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6" id='coin_decimal'>
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Currency Decimal')}}</div>
                                        <input type="number" class="form-control" name="coin_decimal"
                                            value="{{ old('coin_decimal') }}" />
                                        <pre class="text-danger">{{$errors->first('decimal')}}</pre>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Withdrawal Fees Type')}}</div>
                                        <select name="withdrawal_fees_type" id="" class="form-control">
                                            @foreach(discount_type() as $key => $val)
                                                <option value="{{ $key }}" {{ $key == old('withdrawal_fees_type') ? "selected" : "" }}>{{ $val }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Withdrawal Fees')}}</div>
                                        <input type="text" class="form-control" name="withdrawal_fees"
                                            value="{{ old('withdrawal_fees', '0.00000010') }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Status')}}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="status">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn theme-btn">{{ __("Create") }}</button>
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

        $(document).ready(function () {
            hideNode({{ old('type', 1) }});

            $('select[name=currency_id]').change(function () {
                const currency_id = $(this).val();
                getSupportedNetworks(currency_id);
            });

            $("#coin_type").change((e) => {
                var value = $(e.target).val();
                hideNode(value);
            });
        });

        function hideNode(value) {
            if (value == 1) {
                $(".contract_address").show();
            } else {
                $(".contract_address").hide();
            }
        }

        function getSupportedNetworks(currency_id) {
            if (currency_id) {
                let url = "{{ route('networksByCoinProvider', 'currency_id') }}"
                url = url.replace('currency_id', currency_id)

                const networkDiv = $('select[name=network_id]')
                networkDiv.html(`<option value="">Loading...</option>`)

                $.get(url, function (res) {
                    if (res.success) {
                        let options = '<option value="">{{ __('Select a network') }}</option>';

                        res.data.forEach((item) => {
                            options += `<option value="${item.id}" data-provider="${item.provider_type}" data-base_type="${item.base_type}">${item.name}</option>`;
                        })
                        networkDiv.html(options);
                    } else {
                        VanillaToasts.create({
                            text: res?.message,
                            type: 'warning',
                            timeout: 40000
                        });
                    }
                });
            }
        }

        $(document).on('change', '#network_id', function () {
            $("#coin_type").parents('.col-md-6').show();
            let selectedOption = $(this).find(':selected');
            let networkId = selectedOption.val();
            let networkProvider = selectedOption.data('provider');
            let networkBase = selectedOption.data('base_type');

            $("#base_type").val(networkBase);

            if (networkProvider == 3) { // Custom RPC Node
                $("#coin_decimal").show();
            } else {
                $("#coin_decimal").hide();

                $("#coin_type").val("2").trigger('change'); // Set as native coin
                setTimeout(() => {
                    $("#coin_type").parents('.col-md-6').hide();
                }, 10);
            }
        });
    </script>
@endsection