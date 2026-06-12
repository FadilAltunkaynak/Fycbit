@php $update = false; @endphp
<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Module Setting')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminCookieSettingsSave')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        @if(isset($dynamicFrom) && $dynamicFrom)
            @php $update = true; @endphp
            <div class="header-bar">
                <div class="table-title">
                    <h3>{{__('ICO Module')}}</h3>
                </div>
            </div><hr>
            <div class="row">
                <div class="col-lg-6 col-12 mt-20">
                    <div class="form-group">
                        <label>{{__('ICO Module')}}</label>
                        <div class="cp-select-area">
                            <select name="launchpad_settings" class="form-control">
                                <option @if(isset($settings['launchpad_settings']) && $settings['launchpad_settings'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Enable")}}</option>
                                <option @if(isset($settings['launchpad_settings']) && $settings['launchpad_settings'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Disable")}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12 mt-20">
                    <div class="form-group">
                        <label>{{__('ICO token buy request')}}</label>
                        <div class="cp-select-area">
                            <select name="icoTokenBuy_admin_approved" class="form-control">
                                <option @if(isset($settings['icoTokenBuy_admin_approved']) && $settings['icoTokenBuy_admin_approved'] == STATUS_ACTIVE) selected @endif value="{{STATUS_ACTIVE}}">{{__("Auto Accept")}}</option>
                                <option @if(isset($settings['icoTokenBuy_admin_approved']) && $settings['icoTokenBuy_admin_approved'] == STATUS_PENDING) selected @endif value="{{STATUS_PENDING}}">{{__("Need Admin Approved")}}</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        @endif
        @if(isset($update) && $update)
            <div class="row">
                <div class="col-lg-2 col-12 mt-20">
                    <button class="button-primary theme-btn">{{__('Update')}}</button>
                </div>
            </div>
        @endif
    </form>
</div>
