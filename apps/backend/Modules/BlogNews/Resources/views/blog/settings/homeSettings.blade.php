    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <form action="{{ route('BlogSettingUpdate') }}" method="post" >
                            @csrf
                            <input type="hidden" name="tab" value="feature" />
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="blog_feature_enable">{{__('Feature Section')}}</label>
                                        <select id="blog_feature_enable" name="blog_feature_enable" class="selectpicker" data-width="100%" data-style="bg-dark">
                                            <option @if(isset($setting->blog_feature_enable) && $setting->blog_feature_enable == STATUS_ACTIVE ) selected @endif value="{{ STATUS_ACTIVE }}" >{{ __('Enable') }}</option>
                                            <option @if(isset($setting->blog_feature_enable) && $setting->blog_feature_enable == STATUS_INACTIVE ) selected @endif value="{{ STATUS_INACTIVE }}" >{{ __('Disable') }}</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="blog_feature_heading">{{__('Feature Heading')}}</label>
                                        <input id="blog_feature_heading" value="{{ $setting->blog_feature_heading ?? '' }}"  name="blog_feature_heading" class="form-control" placeholder="{{ __('Feature Heading') }}">
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="blog_feature_description">{{__('Feature Description')}}</label>
                                        <textarea id="blog_feature_description" name="blog_feature_description" class="form-control" placeholder="{{ __('Feature Description') }}">{{ $setting->blog_feature_description ?? '' }}</textarea>
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