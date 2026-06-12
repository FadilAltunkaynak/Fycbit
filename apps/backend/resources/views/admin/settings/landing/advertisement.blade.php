<div class="page-title">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-inner">
                <div class="table-title mb-4">
                    <h3>{{__('Advertisement Section')}}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-area plr-65 profile-info-form">
    <form enctype="multipart/form-data" method="POST"
          action="{{route('adminLandingSettingSave')}}">
        @csrf
        <input type="hidden" name="tab" value="advertisement">
        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label for="#">{{__('Advertisement image')}}</label>
                    <div id="file-upload" class="section-width">
                        <input type="file" placeholder="0.00" name="landing_advertisement_image" value="" id="file" ref="file"
                                class="dropify" @if(isset($adm_setting['landing_advertisement_image'])) data-default-file="{{asset(path_image().$adm_setting['landing_advertisement_image'])}}"@endif />
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label for="#">{{__('Advertisement URL')}}</label>
                    <input type="text" placeholder="https://" name="landing_advertisement_url" class="form-control"
                            @if(isset($adm_setting['landing_advertisement_url'])) value="{{ $adm_setting['landing_advertisement_url'] }}"@endif />
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="landing_advertisement_section_status">{{ __('Section Status') }}</label>
                    <div class="cp-select-area">
                        <select id="landing_advertisement_section_status" name="landing_advertisement_section_status" class="form-control">
                            <option @if(isset($adm_setting['landing_advertisement_section_status']) && $adm_setting['landing_advertisement_section_status'] == ENABLE) selected @endif value="{{ ENABLE }}">{{ __('Enable') }}</option>
                            <option @if(isset($adm_setting['landing_advertisement_section_status']) && $adm_setting['landing_advertisement_section_status'] == DISABLE) selected @endif value="{{ DISABLE }}">{{ __('Disable') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <button class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
