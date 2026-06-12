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
                        {{Form::open(['route' => 'updateCoinNetworkProcess', 'files' => true, 'method' => 'POST'])}}

                        <input type="hidden" name="uid" value="{{ $item->uid }}" />

                        <input type="hidden" id="base_type" name="base_type" value="{{ $item->network->base_type }}" />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Currency')}}</div>
                                        <select name="currency_id" id="" class="form-control">
                                            @if(!isset($item))<option value="">{{ __("Select a currency") }}</option>@endif
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}" {{ $currency->id == $item->currency_id ? "selected" : "disabled" }}>
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
                                            @if(!isset($item))<option value="">{{ __("Select a network") }}</option>@endif
                                            @foreach($networks as $network)
                                                <option value="{{ $network->id }}" {{ $network->id == $item->network_id ? "selected" : "disabled" }}>
                                                    {{ $network->name }}
                                                </option>
                                            @endforeach
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
                                                <option value="{{ $key }}" {{ $key == old('type', $item->type) ? "selected" : "" }}>{{ $val }}</option>
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
                                            value="{{ $item->contract_address }}" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6" id='coin_decimal'>
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Currency Decimal')}}</div>
                                        <input type="number" class="form-control" name="coin_decimal"
                                            value="{{$item->coin_decimal}}" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Withdrawal Fees Type')}}</div>
                                        <select name="withdrawal_fees_type" id="" class="form-control">
                                            @foreach(discount_type() as $key => $val)
                                                <option value="{{ $key }}" {{ $key == $item->withdrawal_fees_type ? "selected" : "" }}>{{ $val }}</option>
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
                                            value="{{ old('withdrawal_fees', $item->withdrawal_fees ?? 0.00000010) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Status')}}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="status" {{ $item->status == STATUS_ACTIVE ? "checked" : "" }} />
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn theme-btn">{{ __("Update") }}</button>
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

            hideNode({{ old('type', $item->type) }});

            let providerType = {{ $item->network->provider_type }};

            shouldShowDecimalField(providerType);

            $("#coin_type").change((e) => {
                var value = $(e.target).val();
                hideNode(value);
            });
        });

        function shouldShowDecimalField(networkProvider) {
            if (networkProvider == 3) { // Custom RPC Node
                $("#coin_decimal").show();
            } else {
                $("#coin_decimal").hide();
            }
        }

        function hideNode(value) {
            if (value == 1) {
                $(".contract_address").show();
            } else {
                $(".contract_address").hide();
            }
        }
    </script>
@endsection