<div class="user-management">
    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="table-area payment-table-area">
                    <form action="{{ route('BlogSettingUpdate') }}" method="post">
                        @csrf
                        <input type="hidden" name="tab" value="comment" />
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group">
                                    <label class=" control-label"
                                        for="news_auto_comment_approval">{{__('Auto Comment Approval')}}</label>
                                    <select data-style="bg-dark" id="news_auto_comment_approval"
                                        name="news_auto_comment_approval" class="selectpicker" data-width="100%">
                                        <option value="{{ STATUS_ACTIVE }}">{{ __('Enable') }}</option>
                                        <option value="{{ STATUS_INACTIVE }}">{{ __('Disable') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <input type="submit" class="btn btn-primary bg-primary"
                                value="{{ __("Update Setting") }}" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>