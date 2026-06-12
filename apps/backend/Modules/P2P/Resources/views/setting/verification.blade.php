  <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <div class="form-group">
                            @php
                                $kyc_type = $setting->kyc_type_is ?? 0;
                                $active_kyc = ($kyc_type == KYC_TYPE_DISABLE ? __("Disabled") : ( $kyc_type == KYC_TYPE_MANUAL ? __("Manual KYC Enabled") : __("Third Party (Persona) KYC Enabled") ) );
                            @endphp
                            <label class=" control-label" for="p_phone_verification">{{__('Activated KYC')}}</label>
                            <input type="text" class="form-control mb-2 col-4" width="100px" value="{{ $active_kyc }}" readonly>
                            <a href="{{ route("kycList") }}" class="btn btn-info">{{ "Change" }}</a>
                        </div>
                        @if($kyc_type != KYC_TYPE_DISABLE)
                        <form action="{{ route('p2pSettingUpdate') }}" method="post" >
                            @csrf
                            <input type="hidden" name="tab" value="verification" />
                            <div class="row">
                                @if($kyc_type == KYC_TYPE_PERSONA)
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="p_persona_verification">{{__('Third Party (Persona) KYC')}}</label>
                                        <select id="p_persona_verification" name="p_persona_verification" class="selectpicker" data-width="100%" data-style="bg-dark">
                                            <option @if(isset($setting->p_persona_verification) && $setting->p_persona_verification == STATUS_INACTIVE) selected @endif value="{{ STATUS_INACTIVE }}" >{{ __('Disable') }}</option>
                                            <option @if(isset($setting->p_persona_verification) && $setting->p_persona_verification == STATUS_ACTIVE) selected @endif value="{{ STATUS_ACTIVE }}" >{{ __('Enable') }}</option>
                                        </select>
                                    </div>
                                </div>
                                @endif
                                @if($kyc_type == KYC_TYPE_MANUAL)
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="p_phone_verification">{{__('Phone Verification')}}</label>
                                        <select id="p_phone_verification" name="p_phone_verification" class="selectpicker" data-width="100%" data-style="bg-dark">
                                            <option @if(isset($setting->p_phone_verification) && $setting->p_phone_verification == STATUS_INACTIVE) selected @endif value="{{ STATUS_INACTIVE }}" >{{ __('Disable') }}</option>
                                            <option @if(isset($setting->p_phone_verification) && $setting->p_phone_verification == STATUS_ACTIVE) selected @endif value="{{ STATUS_ACTIVE }}" >{{ __('Enable') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="p_email_verification">{{__('Email Verification')}}</label>
                                        <select id="p_email_verification" name="p_email_verification" class="selectpicker" data-width="100%" data-style="bg-dark">
                                            <option @if(isset($setting->p_email_verification) && $setting->p_email_verification == STATUS_INACTIVE) selected @endif value="{{ STATUS_INACTIVE }}" >{{ __('Disable') }}</option>
                                            <option @if(isset($setting->p_email_verification) && $setting->p_email_verification == STATUS_ACTIVE) selected @endif value="{{ STATUS_ACTIVE }}" >{{ __('Enable') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="p_nid_verification">{{__('NID Verification')}}</label>
                                        <select id="p_nid_verification" name="p_nid_verification" class="selectpicker" data-width="100%" data-style="bg-dark">
                                            <option @if(isset($setting->p_nid_verification) && $setting->p_nid_verification == STATUS_INACTIVE) selected @endif value="{{ STATUS_INACTIVE }}" >{{ __('Disable') }}</option>
                                            <option @if(isset($setting->p_nid_verification) && $setting->p_nid_verification == STATUS_ACTIVE) selected @endif value="{{ STATUS_ACTIVE }}" >{{ __('Enable') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="p_passport_verification">{{__('Passport Verification')}}</label>
                                        <select id="p_passport_verification" name="p_passport_verification" class="selectpicker" data-width="100%" data-style="bg-dark">
                                            <option @if(isset($setting->p_passport_verification) && $setting->p_passport_verification == STATUS_INACTIVE) selected @endif value="{{ STATUS_INACTIVE }}" >{{ __('Disable') }}</option>
                                            <option @if(isset($setting->p_passport_verification) && $setting->p_passport_verification == STATUS_ACTIVE) selected @endif value="{{ STATUS_ACTIVE }}" >{{ __('Enable') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="p_driving_verification">{{__('Driving Verification')}}</label>
                                        <select id="p_driving_verification" name="p_driving_verification" class="selectpicker" data-width="100%" data-style="bg-dark">
                                            <option @if(isset($setting->p_driving_verification) && $setting->p_driving_verification == STATUS_INACTIVE) selected @endif value="{{ STATUS_INACTIVE }}" >{{ __('Disable') }}</option>
                                            <option @if(isset($setting->p_driving_verification) && $setting->p_driving_verification == STATUS_ACTIVE) selected @endif value="{{ STATUS_ACTIVE }}" >{{ __('Enable') }}</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="p_voter_verification">{{__('Voter Card Verification')}}</label>
                                        <select id="p_voter_verification" name="p_voter_verification" class="selectpicker" data-width="100%" data-style="bg-dark">
                                            <option @if(isset($setting->p_voter_verification) && $setting->p_voter_verification == STATUS_INACTIVE) selected @endif value="{{ STATUS_INACTIVE }}" >{{ __('Disable') }}</option>
                                            <option @if(isset($setting->p_voter_verification) && $setting->p_voter_verification == STATUS_ACTIVE) selected @endif value="{{ STATUS_ACTIVE }}" >{{ __('Enable') }}</option>
                                        </select>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="form-group">
                                    <input type="submit" class="btn btn-primary bg-primary" value="{{ __("Update Setting") }}" />
                            </div>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
