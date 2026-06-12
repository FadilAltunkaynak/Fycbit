<div class="row">
    <div class="col-12 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="table-title mb-4">
                    <h3>{{ __('Features Section') }}</h3>
                </div>
                <form method="POST" action="{{ route('adminLandingSettingSave') }}">
                    @csrf
                    <input type="hidden" name="tab" value="feature">
                    <div class="form-group">
                        <label for="landing_feature_title">{{ __('Title') }}</label>
                        <input
                            id="landing_feature_title"
                            class="form-control"
                            type="text"
                            name="landing_feature_title"
                            value="{{ $adm_setting['landing_feature_title'] ?? '' }}"
                        >
                    </div>
                    <div class="form-group">
                        <label for="landing_sixth_section_status">{{ __('Section Status') }}</label>
                        <div class="cp-select-area">
                            <select id="landing_sixth_section_status" name="landing_sixth_section_status" class="form-control">
                                <option @if(isset($adm_setting['landing_sixth_section_status']) && $adm_setting['landing_sixth_section_status'] == ENABLE) selected @endif value="{{ ENABLE }}">{{ __('Enable') }}</option>
                                <option @if(isset($adm_setting['landing_sixth_section_status']) && $adm_setting['landing_sixth_section_status'] == DISABLE) selected @endif value="{{ DISABLE }}">{{ __('Disable') }}</option>
                            </select>
                        </div>
                    </div>
                    <button class="button-primary theme-btn">{{ __('Update') }}</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="header-bar">
            <div class="table-title">
                <h3></h3>
            </div>
            <div class="right d-flex align-items-center">
                <div class="add-btn">
                    <a href="{{ route('adminFeatureAdd') }}">{{ __('+ Add Feature') }}</a>
                </div>
            </div>
        </div>
        <div class="table-area">
            <div>
                <table id="feature-table" class="table table-borderless custom-table display" width="100%">
                    <thead>
                    <tr>
                        <th class="all">{{ __('Title') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Url') }}</th>
                        <th>{{ __('Created At') }}</th>
                        <th class="all text-lg-center">{{ __('Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
