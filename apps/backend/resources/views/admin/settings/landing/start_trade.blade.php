<div class="page-title">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-inner">
                <div class="table-title mb-4">
                    <h3>{{__('Start Trade Section')}}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-area plr-65 profile-info-form">
    <form enctype="multipart/form-data" method="POST"
          action="{{route('adminLandingSettingSave')}}">
        @csrf
        <input type="hidden" name="tab" value="start_trade">
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-md-0">
                            <label for="landing_seventh_section_status">{{ __('Section Status') }}</label>
                            <div class="cp-select-area">
                                <select id="landing_seventh_section_status" name="landing_seventh_section_status" class="form-control">
                                    <option @if(isset($adm_setting['landing_seventh_section_status']) && $adm_setting['landing_seventh_section_status'] == ENABLE) selected @endif value="{{ ENABLE }}">{{ __('Enable') }}</option>
                                    <option @if(isset($adm_setting['landing_seventh_section_status']) && $adm_setting['landing_seventh_section_status'] == DISABLE) selected @endif value="{{ DISABLE }}">{{ __('Disable') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <button class="button-primary theme-btn">{{__('Update')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
