@extends('admin.master', ['menu' => 'future_trade', 'sub_menu' => 'settings'])
@section('title', isset($title) ? $title : '')
@section('sidebar')
@include('futureTrade::layouts.sidebar',['menu'=>'settings'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{__('Future Trade')}}</li>
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
                    <div class="card-body">
                        <form action="{{route('future.settings.save')}}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{__('Default Coin Pair')}}</label>
                                        <select name="future_trade_default_coin_pair" class="form-control">
                                            <option value="">{{__('Select Coin Pair')}}</option>
                                            @foreach($coin_pairs as $pair)
                                                <option value="{{$pair->id}}"
                                                    @if(isset($settings['future_trade_default_coin_pair']) && $settings['future_trade_default_coin_pair'] == $pair->id) selected @endif>
                                                    {{$pair->code}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button class="btn theme-btn w-25" type="submit">{{__('Save Settings')}}</button>
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