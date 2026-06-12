<div class="header-bar">
    <div class="table-title">
        <h3>{{__('Skrill Settings')}}</h3>
    </div>
</div>
<div class="profile-info-form">
    <form action="{{route('adminSaveSkrillApiSettings')}}" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Pay to Email')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                        <input type="email" name="skrill_pay_to_email" class="form-control" value="{{$settings['skrill_pay_to_email'] ?? ''}}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Return URL')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                        <input type="text" name="skrill_return_url" class="form-control" value="{{$settings['skrill_return_url'] ?? ''}}">
                    @endif
                </div>
            </div>
            <div class="col-lg-6 col-12 mt-20">
                <div class="form-group">
                    <label for="#">{{__('Cancel URL')}}</label>
                    @if(env('APP_MODE') == 'demo')
                        <input class="form-control" value="{{'disablefordemo'}}">
                    @else
                        <input type="text" name="skrill_cancel_url" class="form-control" value="{{$settings['skrill_cancel_url'] ?? ''}}">
                    @endif
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-2 col-12 mt-20">
                <button type="submit" class="button-primary theme-btn">{{__('Update')}}</button>
            </div>
        </div>
    </form>
</div>
