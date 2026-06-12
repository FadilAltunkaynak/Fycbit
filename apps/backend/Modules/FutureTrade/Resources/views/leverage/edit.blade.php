@extends('admin.master')
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('sidebar')
@include('futureTrade::layouts.sidebar',['menu'=>'coin-pairs'])
@endsection
@section('content')

    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-12">
                <ul>
                    <li class="active-item">{{__('Leverage Settings For :code', ['code'=>$coin_pair->code])}}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <form action="{{ route('future.leverage.store', $coin_pair->uid) }}" method="post">
                        @csrf
                        <input type="hidden" name="coin_pair_uid" value="{{ $coin_pair->uid }}" />
                        @if(isset($item))
                            <input type="hidden" name="leverage_setting_id" value="{{ $item->uid }}" />
                        @endif
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Min Position Amount') }} <span class="text-danger">*</span></div>
                                        <input type="number" class="form-control" name="min_position_amount" step="any"
                                            value="{{ old('min_position_amount', $item?->min_position_amount ? trim_num($item?->min_position_amount) : null) }}" />
                                        <pre class="text-danger">{{ $errors->first('min_position_amount') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Max Position Amount') }} <span class="text-danger">*</span></div>
                                        <input type="number" class="form-control" name="max_position_amount" step="any"
                                            value="{{ old('max_position_amount', $item?->max_position_amount ? trim_num($item?->max_position_amount) : null) }}" />
                                        <pre class="text-danger">{{ $errors->first('max_position_amount') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Maintenance Margin Rate') }} <span class="text-danger">*</span></div>
                                        <input type="number" class="form-control" name="maintenance_margin_rate" step="any"
                                            value="{{ old('maintenance_margin_rate', $item?->maintenance_margin_rate ? trim_num($item?->maintenance_margin_rate) : null) }}" />
                                        <pre class="text-danger">{{ $errors->first('maintenance_margin_rate') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Maintenance Amount (USDT)') }} <span class="text-danger">*</span></div>
                                        <input type="number" class="form-control" name="maintenance_amount" step="any"
                                            value="{{ old('maintenance_amount', $item?->maintenance_amount ? trim_num($item?->maintenance_amount) : null) }}" />
                                        <pre class="text-danger">{{ $errors->first('maintenance_amount') }}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Max Leverage') }} <span class="text-danger">*</span></div>
                                        <input type="number" class="form-control" name="max_leverage" step="any"
                                            value="{{ old('max_leverage', $item?->max_leverage ? trim_num($item?->max_leverage) : null) }}" />
                                        <pre class="text-danger">{{ $errors->first('max_leverage') }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2">
                                <button type="submit" class="btn theme-btn">{{ __("Save") }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>

    "use strict";

</script>
@endsection