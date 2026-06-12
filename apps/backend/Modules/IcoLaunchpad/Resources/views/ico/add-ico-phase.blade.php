@extends('admin.master')
@section('title', isset($title) ? $title :  __('ICO Add'))
@section('style')
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'ico_list'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-5">
                <ul>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div class="card-body">
                        <form action="{{route('saveICOPhase')}}" method="post" enctype="multipart/form-data">
                            @csrf

                            @if(isset($item))
                                <input type="hidden" name="id" value="{{$item->id}}">
                                <input type="hidden" name="ico_token_id" value="{{$item->ico_token_id}}">
                            @endif

                            @if (isset($ico_token))
                                <input type="hidden" name="ico_token_id" value="{{$ico_token->id}}">
                            @endif

                            <div class="row">
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="coin_price">{{__('Coin Price')}}</label>
                                        <input type="text" name="coin_price" class="form-control" id="coin_price" placeholder="{{__('Coin Price')}}"
                                               @if(isset($item)) value="{{$item->coin_price}}" @else value="{{old('coin_price')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="chain_id">{{__('Coin Currency')}}</label>
                                        <select class=" form-control" name="coin_currency"  style="width: 100%;">
                                            <option value="">{{__('Select')}}</option>
                                            @if(isset($coins[0]))
                                                @foreach($coins as $coin)

                                                    <option value="{{$coin->coin_type}}"
                                                        {{( isset($item) && $item->coin_currency == $coin->coin_type)?'selected' : ''}} >{{check_default_coin_type($coin->coin_type)}}</option>

                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="minimum_purchase_price">{{__('Minimum Purchase Amount')}}</label>
                                        <input type="text" name="minimum_purchase_price" class="form-control" placeholder="{{__('Minimum Purchase Amount')}}"
                                               @if(isset($item)) value="{{$item->minimum_purchase_price}}" @else value="{{old('minimum_purchase_price')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="maximum_purchase_price">{{__('Maximum Purchase Amount')}}</label>
                                        <input type="text" name="maximum_purchase_price" class="form-control" placeholder="{{__('Maximum Purchase Amount')}}"
                                               @if(isset($item)) value="{{$item->maximum_purchase_price}}" @else value="{{old('maximum_purchase_price')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="total_token_supply">{{__('Total Token Supply')}}</label>
                                        <input type="text" name="total_token_supply" class="form-control" id="total_token_supply" placeholder="{{__('Total Token Supply')}}"
                                               @if(isset($item)) value="{{$item->total_token_supply}}" @else value="{{old('total_token_supply')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="phase_title">{{__('Phase Title')}}</label>
                                        <input type="text" name="phase_title" class="form-control" id="phase_title" placeholder="{{__('Phase Title')}}"
                                               @if(isset($item)) value="{{$item->phase_title}}" @else value="{{old('phase_title')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="start_date">{{__('Start Date')}}</label>
                                        <input type="date" name="start_date" class="form-control" id="start_date"
                                               @if(isset($item)) value="{{$item->start_date}}" @else value="{{old('start_date')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="end_date">{{__('End Date')}}</label>
                                        <input type="date" name="end_date" class="form-control" id="end_date"
                                               @if(isset($item)) value="{{$item->end_date}}" @else value="{{old('end_date')}}" @endif>
                                    </div>
                                </div>

                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="wallet_private_key">{{__('Description')}}</label>
                                        <textarea class="form-control" name="description" id="" rows="2">@if (isset($item)){{$item->description}}@else {{old('description')}} @endif</textarea>

                                    </div>
                                </div>
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="video_link">{{__('Video Link')}}</label>
                                        <input type="text" name="video_link" class="form-control" id="video_link" placeholder="{{__('Video Link')}}"
                                               @if(isset($item)) value="{{$item->video_link}}" @else value="{{old('video_link')}}" @endif>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="row">
                                        <div class="col-lg-12 mt-20">
                                            <div class="single-uplode">
                                                <div class="uplode-catagory">
                                                    <span>{{__('Upload Image')}}</span>
                                                </div>
                                                <div class="form-group buy_coin_address_input ">
                                                    <div id="file-upload" class="section-p">
                                                        <input type="file" placeholder="0.00" name="image" value=""
                                                            id="file" ref="file" class="dropify"
                                                            @if(isset($item))  data-default-file="{{asset(FILE_ICO_VIEW_PATH.$item->image)}}" @endif />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @php
                                    if(isset($item))
                                    {
                                        $social_link = json_decode($item->social_link);
                                    }
                                @endphp

                                @foreach (socialMediaList() as $key=>$value)
                                <div class="col-md-6 mt-20">
                                    <div class="form-group">
                                        <label for="social_link">{{ $value. ' ' . __('Link')}} ({{__('optional')}}) </label>

                                        <input type="text" name="social_link[{{$key}}]" class="form-control" id="social_link" placeholder="{{$value. ' ' . __('Link')}}"
                                            @if(isset($item))
                                                @foreach ($social_link as $k=>$social_item)
                                                    @if ($value == $k)
                                                        value="{{$social_item}}"
                                                    @endif
                                                @endforeach
                                            @else value="{{old('social_link[$key]')}}" @endif>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="row" id="additional_info">
                                @if (isset($item) && isset($item->icoPhaseAdditionalDetails))
                                    @foreach ($item->icoPhaseAdditionalDetails as $key=>$ico_phase_additional_item)

                                        <input type="hidden" name="additional[{{$key}}][id]" value="{{$ico_phase_additional_item->id}}">
                                        <div class="col-md-12" id="additional_info_no{{$key}}">
                                            <div class="row">
                                                <div class="col-md-5 mt-20">
                                                    <div class="form-group">
                                                        <label for="video_link">{{__('Title')}}</label>
                                                        <input type="text" name="additional[{{$key}}][title]" class="form-control" id="additional"
                                                        placeholder="{{__('Title')}}" value="{{$ico_phase_additional_item->title}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-5 mt-20">
                                                    <div class="form-group">
                                                        <label for="video_link">{{__('File')}} ({{__('optional')}})</label>
                                                        @if (isset($ico_phase_additional_item->file))
                                                        <a class="ml-2" href="{{url(FILE_ICO_VIEW_PATH.$ico_phase_additional_item->file)}}" target="_blank">{{__('View Previous File')}}</a>
                                                        @endif
                                                        <input type="file" name="additional[{{$key}}][file]" class="form-control" id="additional" placeholder="{{__('Value')}}"
                                                            value="{{$ico_phase_additional_item->file}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 mt-20">
                                                    <a href="{{route('deleteICOPhaseAdditionalInfo',['id'=>encrypt($ico_phase_additional_item->id)])}}"  class="btn btn-danger" style="float: left;margin-top:28px;">{{__('Delete')}}</a>
                                                </div>
                                                <div class="col-md-10 mt-20">
                                                    <div class="form-group">
                                                        <label for="video_link">{{__('Value')}}</label>
                                                        <textarea name="additional[{{$key}}][value]" class="form-control" id="" cols="30" rows="2" >{{$ico_phase_additional_item->value}}</textarea>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                @endif

                            </div>
                            @php($user = auth()->user())
                            @if (isset($item) && $user->id == $item->user_id)
                                <div class="row">
                                    <div class="col-md-12">
                                        <a class="button-primary theme-btn pull-right" id="add_new_additional_info">
                                            {{__('Add New Additional Info')}}
                                        </a>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <button class="button-primary theme-btn">@if(isset($item)) {{__('Update')}} @else {{__('Save')}} @endif</button>
                                    </div>
                                </div>
                            @elseif(!isset($item))
                                <div class="row">
                                    <div class="col-md-12">
                                        <a class="button-primary theme-btn pull-right" id="add_new_additional_info">
                                            {{__('Add New Additional Info')}}
                                        </a>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <button class="button-primary theme-btn">@if(isset($item)) {{__('Update')}} @else {{__('Save')}} @endif</button>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
   <script>
        //input file size limit from client side
        const fileInput = document.querySelector('input[type=file]');

        fileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        const maxSize = 2000000; // 1 MB in bytes

        if (file.size > maxSize) {

            VanillaToasts.create({
                text: '{{__('File size is too large. Maximum allowed size is 2 MB.')}}',
                type: 'warning',
                timeout: 40000
            });
            fileInput.value = ''; // clear the selected file
        }
        });

        var additionalInfoNumber = '{{(isset($item) && isset($item->icoPhaseAdditionalDetails))? count($item->icoPhaseAdditionalDetails): 0}}';
        function additionalInfoHtml(additionalInfoNumber)
        {
            var html =
            `<div class="col-md-12" id="additional_info_no`+additionalInfoNumber+`">
                <div class="row">
                    <div class="col-md-5 mt-20">
                        <div class="form-group">
                            <label for="video_link">{{__('Title')}}</label>
                            <input type="text" name="additional[`+additionalInfoNumber+`][title]" class="form-control" id="additional"
                            placeholder="{{__('Title')}}" >
                        </div>
                    </div>
                    <div class="col-md-5 mt-20">
                        <div class="form-group">
                            <label for="video_link">{{__('File')}} ({{__('optional')}}) </label>
                            <input type="file" name="additional[`+additionalInfoNumber+`][file]" class="form-control" id="additional" placeholder="{{__('Value')}}" >
                        </div>
                    </div>
                    <div class="col-md-2 mt-20">
                        <button data-id="`+additionalInfoNumber+`" type="button"  class="btn btn-danger delete-additional-info" style="float: left;margin-top:28px;">{{__('Delete')}}</button>
                    </div>
                    <div class="col-md-10 mt-20">
                        <div class="form-group">
                            <label for="video_link">{{__('Value')}}</label>
                            <textarea name="additional[`+additionalInfoNumber+`][value]" class="form-control" id="" cols="30" rows="2" ></textarea>

                        </div>
                    </div>
                </div>
            </div>`;

            return html;
        }
        $('#add_new_additional_info').on('click', function(){

            var additional_info = additionalInfoHtml(additionalInfoNumber);

            $('#additional_info').append(additional_info);
            console.log(additional_info);
            additionalInfoNumber++;
        });

        $(document).on('click','.delete-additional-info', function () {
            var deleteId = $(this).data('id');
            $('#additional_info_no'+deleteId).remove();
        });
   </script>
@endsection
