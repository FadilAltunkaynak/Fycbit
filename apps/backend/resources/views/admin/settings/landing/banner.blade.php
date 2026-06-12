<div class="user-management">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" action="{{ route('adminLandingSettingSave') }}">
                        @csrf
                        <input type="hidden" name="tab" value="banner">
                        <div class="row align-items-end">
                            <div class="col-md-10">
                                <div class="form-group mb-md-0">
                                    <label for="landing_second_section_status">{{ __('Section Status') }}</label>
                                    <div class="cp-select-area">
                                        <select id="landing_second_section_status" name="landing_second_section_status" class="form-control">
                                            <option @if(isset($adm_setting['landing_second_section_status']) && $adm_setting['landing_second_section_status'] == ENABLE) selected @endif value="{{ ENABLE }}">{{ __('Enable') }}</option>
                                            <option @if(isset($adm_setting['landing_second_section_status']) && $adm_setting['landing_second_section_status'] == DISABLE) selected @endif value="{{ DISABLE }}">{{ __('Disable') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button class="button-primary theme-btn w-100">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="header-bar">
                <div class="table-title">
                    <h3>{{ __('Banner Section') }}</h3>
                </div>
                <div class="right d-flex align-items-center">
                    <div class="add-btn">
                        <a href="{{ route('adminBannerAdd') }}">{{ __('+ Add') }}</a>
                    </div>
                </div>
            </div>
            <div class="table-area">
                <div>
                    <table id="banner-table" class="table table-borderless custom-table display" width="100%">
                        <thead>
                        <tr>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Created At') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
