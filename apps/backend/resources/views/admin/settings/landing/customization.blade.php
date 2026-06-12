<div class="page-title">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-inner">
                <div class="table-title mb-4">
                    <h3>{{__('Know More Section')}}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-area plr-65 profile-info-form">
    <form enctype="multipart/form-data" method="POST"
          action="{{route('adminLandingSettingSave')}}">
        @csrf
        <input type="hidden" name="tab" value="contact">
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="#">{{__('Heading')}}</label>
                            <input type="text" class="form-control" name="secure_trade_title"
                                   @if(isset($adm_setting['secure_trade_title']))value="{{$adm_setting['secure_trade_title']}}" @endif>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Title')}}</label>
                            <input type="text" class="form-control" name="customization_title" @if(isset($adm_setting['customization_title'])) value="{{$adm_setting['customization_title']}}" @endif>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Details')}}</label>
                            <textarea class="form-control" rows="5" name="customization_details">@if(isset($adm_setting['customization_details'])){{$adm_setting['customization_details']}} @endif</textarea>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Button Title')}}</label>
                            <input type="text" class="form-control" name="know_more_button_title"
                                    @if(isset($adm_setting['know_more_button_title']))value="{{$adm_setting['know_more_button_title']}}" @endif>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Button Link')}}</label>
                            <input type="text" class="form-control" name="know_more_link"
                                    @if(isset($adm_setting['know_more_link']))value="{{$adm_setting['know_more_link']}}" @endif>
                        </div>
                        <div class="form-group">
                            <label for="#">{{__('Image')}}</label>
                            <div id="file-upload" class="section-width">
                                <input type="file" placeholder="0.00" name="secure_trade_left_img" value="" id="file" ref="file"
                                        class="dropify" @if(isset($adm_setting['secure_trade_left_img'])) data-default-file="{{asset(path_image().$adm_setting['secure_trade_left_img'])}}"@endif />
                            </div>
                        </div>

                        <button class="button-primary theme-btn">{{__('Update')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
