@extends('admin.master',['menu'=>'coin', 'sub_menu'=>'coin_pair'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div>
                        <form action="{{route('coinPairSettingUpdate')}}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{encrypt($coin_pair_details->id)}}">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Current Market Price')}} : {{ $coin_pair_details->price }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Trade Coin')}}</div>
                                            <input type="text" class="form-control"
                                                value="{{isset($coin_pair_details->child_coin) ? check_default_coin_type($coin_pair_details->child_coin->coin_type) : ''}}"
                                                readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Base Coin')}}</div>
                                            <input type="text" class="form-control"
                                                value="{{isset($coin_pair_details->parent_coin) ? check_default_coin_type($coin_pair_details->parent_coin->coin_type) : ''}}"
                                                readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3 mt-3">
                                    <h4 class="text-white">{{__('Trading Bot Setting')}}</h4>
                                </div>
                            </div>
                            <div class="row">
                                @if($coin_pair_details?->is_token ?? true)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Bot Operation')}}</div>
                                            <select class="form-control" name="bot_operation" id="">
                                                @foreach (bot_operation() as $key => $val)
                                                    <option @if (isset($coin_pair_details) && $coin_pair_details->bot_operation == $key) selected @endif
                                                        value="{{ $key }}">{{ $val }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Bot Price Range Percentage')}} ( %)</div>
                                            <input type="text" class="form-control" name="bot_percentage"
                                                value="{{$coin_pair_details->bot_percentage}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Lower Threshold')}}</div>
                                            <input type="text" class="form-control" name="lower_threshold"
                                                value="{{$coin_pair_details->lower_threshold}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Upper Threshold')}}</div>
                                            <input type="text" class="form-control" name="upper_threshold"
                                                value="{{$coin_pair_details->upper_threshold}}">
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Minimum Amount')}}</div>
                                            <input type="text" class="form-control" name="bot_min_amount"
                                                value="{{$coin_pair_details->bot_min_amount}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Bot Interval (Sec)')}}</div>
                                            <input type="number" min="1" step="1" class="form-control" name="bot_interval"
                                                value="{{$coin_pair_details->bot_interval}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Maximum Amount')}}</div>
                                            <input type="text" class="form-control" name="bot_max_amount"
                                                value="{{$coin_pair_details->bot_max_amount}}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-2">
                                    <button type="submit" class="btn theme-btn">{{ __('Submit')}}</button>
                                </div>
                            </div>
                        </form>



                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
@endsection
