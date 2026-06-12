@extends('admin.master', ['menu' => 'coin_namagement', 'sub_menu' => 'coin_network'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    @php
        use App\Enums\NetworkBase;
    @endphp

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
                        {{Form::open(['route' => 'adminSaveCoinSetting', 'files' => true])}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Coin Type')}}</div>
                                        <p class="form-control">{{$item->coin_type}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Coin API')}}</div>
                                        <p class="form-control">{{NetworkBase::tryFrom($network->base_type)->getText()}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(NetworkBase::isBitcoin($network->base_type))
                            @include('admin.coin_management.coin_network.include.bitcoin')
                        @elseif(NetworkBase::isBitgo($network->base_type))
                            @include('admin.coin_management.coin_network.include.bitgo')
                        @endif
                        <div class="row">
                            <div class="col-md-2">
                                @if(isset($item))
                                    <input type="hidden" name="coin_id" value="{{encrypt($item->id)}}">
                                    <input type="hidden" name="coin_network_id" value="{{encrypt($coin_network->network_id)}}">
                                @endif
                                <button type="submit" class="btn theme-btn">{{$button_title}}</button>
                            </div>
                        </div>
                        {{Form::close()}}
                        @if(NetworkBase::isBitgo($network->base_type))
                            <hr>
                            <div class="custom-breadcrumb">
                                <div class="row">
                                    <div class="col-9">
                                        <ul>
                                            <li class="active-item">{{ __("Add Bitgo Webhook") }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            {{Form::open(['route' => 'webhookSave', 'files' => true])}}
                            @include('admin.coin_management.coin_network.include.webhook')
                            <div class="row">
                                <div class="col-md-2">
                                    @if(isset($item))
                                        <input type="hidden" name="coin_id" value="{{encrypt($item->id)}}">
                                        <input type="hidden" name="coin_network_id" value="{{encrypt($coin_network->id)}}">
                                    @endif
                                    <button type="submit" class="btn theme-btn">{{__("Update Webhook")}}</button>
                                </div>
                            </div>
                            {{Form::close()}}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->
@endsection