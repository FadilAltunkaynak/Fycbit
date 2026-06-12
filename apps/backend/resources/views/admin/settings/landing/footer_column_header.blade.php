@extends('admin.master',['menu'=>'landing_setting','sub_menu'=>'footer_column_header'])
@section('title', 'Landing Setting')
@section('style')
@endsection
@section('content')
    <!-- coin-area start -->
    <div class="landing-page-area user-management">
        <div class="page-wraper section-padding">
            <div class="page-title">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-inner">
                            <div class="table-title mb-4">
                                <h3>{{__('Footer Column Headers')}}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-area plr-65 profile-info-form">
                <form action="{{route('themesSettingSave')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="tab" value="footer_column_header">
                    <div class="row">
                        <div class="col-6 mt-20">
                            <div class="form-group">
                                <input type="text" name="user_footer_title_about" value="{{ $settings['user_footer_title_about'] ?? 'About Us' }}" class="form-control" />
                            </div>
                            
                            <div class="form-group">
                                <input type="text" name="user_footer_title_product" value="{{ $settings['user_footer_title_product'] ?? 'Products' }}" class="form-control" />
                            </div>
                        
                            <div class="form-group">
                                <input type="text" name="user_footer_title_service" value="{{ $settings['user_footer_title_service'] ?? 'Service' }}" class="form-control" />
                            </div>
                    
                            <div class="form-group">
                                <input type="text" name="user_footer_title_support" value="{{ $settings['user_footer_title_support'] ?? 'Support' }}" class="form-control" />
                            </div>
                        
                            <div class="form-group">
                                <input type="text" name="user_footer_title_community" value="{{ $settings['user_footer_title_community'] ?? 'Community' }}" class="form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-2 col-12 mt-20">
                            <button class="button-primary theme-btn">{{__('Update')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
@endsection
