<div class="page-title">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-inner">
                <div class="table-title mb-4">
                    <h3>{{__('Landing Page Trade Related Settings')}}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-area plr-65 profile-info-form">
    <form enctype="multipart/form-data" method="POST"
          action="{{route('adminLandingSettingSave')}}">
        @csrf
        <input type="hidden" name="tab" value="market_trends">
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="#">{{__('Market Trade Heading')}}</label>
                            <input type="text" class="form-control" name="market_trend_title"
                                   @if(isset($adm_setting['market_trend_title']))value="{{$adm_setting['market_trend_title']}}" @endif>
                        </div>
                    </div>
                    <div class="col-md-12 mt-3">
                        <div class="form-group">
                            <label for="#">{{__('Number of Assets')}}</label>
                            <div class="cp-select-area">
                                <select name="pair_assets_list" class="form-control" data-width="100%">
                                    <option @if(isset($adm_setting['pair_assets_list']) && $adm_setting['pair_assets_list'] == '6') selected @endif value="6">6</option>
                                    <option @if(isset($adm_setting['pair_assets_list']) && $adm_setting['pair_assets_list'] == '10') selected @endif value="10">10</option>
                                    <option @if(isset($adm_setting['pair_assets_list']) && $adm_setting['pair_assets_list'] == '15') selected @endif value="15">15</option>
                                    <option @if(isset($adm_setting['pair_assets_list']) && $adm_setting['pair_assets_list'] == '20') selected @endif value="20">20</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="#">{{__('Assets base coin')}}</label>
                            <div class="customSelect">
                                <select name="pair_assets_base_coin" class="selectpicker" data-width="100%" data-live-search="true">
                                    @foreach(getAllCoinList() as $coin)
                                        <option @if(isset($adm_setting['pair_assets_base_coin']) && $adm_setting['pair_assets_base_coin'] == $coin->coin_type) selected @endif value="{{ $coin->coin_type }}">{{ $coin->coin_type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group mb-md-0">
                            <label for="landing_third_section_status">{{ __('Section Status') }}</label>
                            <div class="cp-select-area">
                                <select id="landing_third_section_status" name="landing_third_section_status" class="form-control">
                                    <option @if(isset($adm_setting['landing_third_section_status']) && $adm_setting['landing_third_section_status'] == ENABLE) selected @endif value="{{ ENABLE }}">{{ __('Enable') }}</option>
                                    <option @if(isset($adm_setting['landing_third_section_status']) && $adm_setting['landing_third_section_status'] == DISABLE) selected @endif value="{{ DISABLE }}">{{ __('Disable') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                
                            </div>

                            <div class="col-lg-12">
                                <button class="button-primary theme-btn">{{__('Update')}}</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>
