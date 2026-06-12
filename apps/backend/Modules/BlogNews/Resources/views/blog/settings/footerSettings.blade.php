    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <form action="{{ route('BlogSettingUpdate') }}" method="post" >
                            @csrf
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class=" control-label" for="account_holder_name">{{__('Test')}}</label>
                                        <input type="text" value="{{ "" }}" name="test" class="form-control" placeholder="{{__('test')}}" >
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                    <input type="submit" class="btn btn-primary" value="{{ __("Update Setting") }}" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>