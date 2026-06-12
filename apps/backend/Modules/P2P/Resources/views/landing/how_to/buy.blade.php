<div class="page-title">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-inner">
                <div class="table-title mb-4">
                    <h3>{{__('How To Buy')}}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <form action="{{route('setLandingHowToP2p')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Step One Heading") }}</label>
                                        <input type="text" class="form-control" name="p2p_buy_step_1_heading"
                                            value="{{isset($settings['p2p_buy_step_1_heading'])?$settings['p2p_buy_step_1_heading']:''}}">
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Step One Description") }}</label>
                                        <textarea class="form-control" name="p2p_buy_step_1_des" rows="4">{{isset($settings['p2p_buy_step_1_des'])?$settings['p2p_buy_step_1_des']:''}}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="#">{{__('Step One Image')}}</label>
                                <div id="file-upload" class="section-width">
                                    <input type="file" placeholder="0.00" name="p2p_buy_step_1_icon" id="file" ref="file"
                                           class="dropify" @if(isset($settings['p2p_buy_step_1_icon'])) data-default-file="{{p2pLandingImg('p2p_buy_step_1_icon')}}"@endif />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Step Two Heading") }}</label>
                                        <input type="text" class="form-control" name="p2p_buy_step_2_heading"
                                            value="{{isset($settings['p2p_buy_step_2_heading'])?$settings['p2p_buy_step_2_heading']:''}}">
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Step Two Description") }}</label>
                                        <textarea class="form-control" name="p2p_buy_step_2_des" rows="4">{{isset($settings['p2p_buy_step_2_des'])?$settings['p2p_buy_step_2_des']:''}}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="#">{{__('Step Two Image')}}</label>
                                <div id="file-upload" class="section-width">
                                    <input type="file" placeholder="0.00" name="p2p_buy_step_2_icon" value="" id="file" ref="file"
                                           class="dropify" @if(isset($settings['p2p_buy_step_2_icon'])) data-default-file="{{p2pLandingImg('p2p_buy_step_2_icon')}}"@endif />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Step Three Heading") }}</label>
                                        <input type="text" class="form-control" name="p2p_buy_step_3_heading"
                                            value="{{isset($settings['p2p_buy_step_3_heading'])?$settings['p2p_buy_step_3_heading']:''}}">
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <label class="control-label">{{ __("Step Three Description") }}</label>
                                        <textarea class="form-control" name="p2p_buy_step_3_des" rows="4">{{isset($settings['p2p_buy_step_3_des'])?$settings['p2p_buy_step_3_des']:''}}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="#">{{__('Step Three Image')}}</label>
                                <div id="file-upload" class="section-width">
                                    <input type="file" placeholder="0.00" name="p2p_buy_step_3_icon" value="" id="file" ref="file"
                                           class="dropify" @if(isset($settings['p2p_buy_step_3_icon'])) data-default-file="{{p2pLandingImg('p2p_buy_step_3_icon')}}"@endif />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <button class="button-primary theme-btn" type="submit">
                                {{__('Save')}}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

