  <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <form action="{{ route('p2pSettingUpdate') }}" method="post" >
                            @csrf
                            <input type="hidden" name="tab" value="condition" />
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="counterparty_condition">{{__('Counterparty Condition At Create Ads')}}</label>
                                        <select id="counterparty_condition" name="counterparty_condition" class="selectpicker" data-width="100%" data-style="bg-dark">
                                            <option @if(isset($setting->counterparty_condition) && $setting->counterparty_condition == STATUS_INACTIVE) selected @endif value="{{ STATUS_ACTIVE }}" >{{ __('Enable') }}</option>
                                            <option @if(isset($setting->counterparty_condition) && $setting->counterparty_condition == STATUS_ACTIVE) selected @endif value="{{ STATUS_INACTIVE }}" >{{ __('Disable') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                    <input type="submit" class="btn btn-primary bg-primary" value="{{ __("Update Setting") }}" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
