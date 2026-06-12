@extends('admin.master', ['menu' => 'coin_namagement', 'sub_menu' => 'supported_network'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{ __('Network') }}</li>
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
                        {{ Form::open(['route' => isset($item)? 'editSupportedNetwork' : 'creteSupportedNetwork', 'method' => 'POST']) }}
                        @if(isset($item) && isset($item->id))
                            <input type="hidden" name="slug" value="{{ $item->slug }}">
                        @endif`
                        <div class="row">
                            <div class="col-md-6 evm_base">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Name') }}</div>
                                        <input type="text" class="form-control" name="name"
                                        @if (isset($item)) value="{{ $item->name }}" @else value="{{ old('name') }}" @endif>
                                        <pre class="text-danger">{{ $errors->first('name') }}</pre>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Network Type')}}</div>
                                        <select class="form-control" name="type" id="type">
                                            @foreach (getBaseNetworkType() as $key => $value)
                                                <option @if(isset($item->type) && $item->type == $key) selected @endif value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 evm_base">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Environment')}}</div>
                                        <select class="form-control" name="environment" id="environment">
                                            @foreach (getNetworkEnvironment() as $key => $value)
                                                <option @if(isset($item->network_type) && $item->network_type == $key) selected @endif value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Chain ID') }}</div>
                                        <input type="number" class="form-control" name="chain_id"
                                            @if (isset($item)) value="{{ $item->chain_id }}" @else value="{{ old('chain_id') }}" @endif>
                                        <pre class="text-danger">{{ $errors->first('chain_id') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 evm_base">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Native Currency')}}</div>
                                        <input type="text" class="form-control" name="native_currency"
                                            @if (isset($item)) value="{{ $item->native_currency }}" @else value="{{ old('native_currency') }}" @endif>
                                        <pre class="text-danger">{{ $errors->first('native_currency') }}</pre>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Explorer Url') }}</div>
                                        <input type="text" class="form-control" name="base_url"
                                            @if (isset($item)) value="{{ $item->base_url }}" @else value="{{ old('base_url') }}" @endif>
                                        <pre class="text-danger">{{ $errors->first('base_url') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 evm_base">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Token Endpoint')}}</div>
                                        <input type="text" class="form-control" name="token_endpoint"
                                            @if (isset($item)) value="{{ $item->token_endpoint }}" @else value="{{ old('token_endpoint') }}" @endif>
                                        <pre class="text-danger">{{ $errors->first('token_endpoint') }}</pre>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Address Endpoint') }}</div>
                                        <input type="text" class="form-control" name="address_endpoint"
                                            @if (isset($item)) value="{{ $item->address_endpoint }}" @else value="{{ old('address_endpoint') }}" @endif>
                                        <pre class="text-danger">{{ $errors->first('address_endpoint') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 evm_base">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Transaction Endpoint')}}</div>
                                        <input type="text" class="form-control" name="tx_endpoint"
                                            @if (isset($item)) value="{{ $item->tx_endpoint }}" @else value="{{ old('tx_endpoint') }}" @endif>
                                        <pre class="text-danger">{{ $errors->first('tx_endpoint') }}</pre>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Gas Fees') }}</div>
                                        <input type="number" class="form-control" name="gas_limit"
                                            @if (isset($item)) value="{{ $item->gas_limit }}" @else value="{{ old('gas_limit') }}" @endif>
                                        <pre class="text-danger">{{ $errors->first('gas_limit') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Gas Price') }}</div>
                                        <input type="number" class="form-control" name="gas_price"
                                            @if (isset($item)) value="{{ $item->gas_price }}" @else value="{{ old('gas_price') }}" @endif>
                                        <pre class="text-danger">{{ $errors->first('gas_price') }}</pre>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Status') }}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="status"
                                                @if (isset($item) && $item->status == 1) checked @endif>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn theme-btn">{{ __('Update') }}</button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->

@endsection

@section('script')
@endsection
