@extends('admin.master')
@section('title', isset($title) ? $title : __('Dynamic form create for ICO'))
@section('style')
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'dynamic_form_settings'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-9">
                <ul>
                    <li class="active-item">{{$title}}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    @php
        $optionCount = 0;
    @endphp
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card trade-card">
                    <div class="card-body">
                        <form action="{{url('dynamicform/store')}}" method="post">
                            @csrf
                            <div class="" id="add_new_title">
                                @if (count($formData)>0)
                                    @foreach ($formData as $dataKey=>$data)
                                    <div class="row" id="tile_no_{{$dataKey}}">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">{{__('Title')}}</label>
                                                <input type="text" class="form-control" name="option[{{$dataKey}}][title]" value="{{$data->title}}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="">{{__('Type')}}</label>
                                            <div class="cp-select-area">
                                                <select name="option[{{$dataKey}}][type]"  class="form-control dynamic_type" data-id='{{$dataKey}}' title="{{ __('Select Type') }}" >
                                                        <option value="{{FORM_INPUT_TEXT}}" 
                                                        @if ($data->type == FORM_INPUT_TEXT)  selected @endif>{{formOptionType(FORM_INPUT_TEXT)}} </option>
                                                        <option value="{{FORM_SELECT}}"
                                                        @if ($data->type == FORM_SELECT)  selected @endif>{{formOptionType(FORM_SELECT)}}</option>
                                                        <option value="{{FORM_RADIO}}"
                                                        @if ($data->type == FORM_RADIO)  selected @endif> {{formOptionType(FORM_RADIO)}} </option>
                                                        <option value="{{FORM_CHECKBOX}}"
                                                        @if ($data->type == FORM_CHECKBOX)  selected @endif> {{formOptionType(FORM_CHECKBOX)}}</option>
                                                        <option value="{{FORM_TEXT_AREA}}"
                                                        @if ($data->type == FORM_TEXT_AREA)  selected @endif> {{formOptionType(FORM_TEXT_AREA)}}</option>
                                                        <option value="{{FORM_FILE}}"
                                                        @if ($data->type == FORM_FILE)  selected @endif> {{formOptionType(FORM_FILE)}}</option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                        <div class="col-md-{{$dataKey==0?4:2}}">
                                            <label for="country">{{__('Required')}}</label>
                                            <div class="cp-select-area">
                                                <select name="option[{{$dataKey}}][required]" id="type" class="form-control" title="{{ __('Select Required Type') }}" data-live-search="true" data-width="100%"
                                                    data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                                        <option value="0" @if ($data->required == 0)  selected @endif>{{__('No')}} </option>
                                                        <option value="1"  @if ($data->required == 1)  selected @endif> {{__('Yes')}} </option>
                                                </select>
                                            </div>
                                        </div>
                                        @if ($dataKey!=0)
                                            <div class="col-md-2">
                                                <button id="" type="button" data-id="{{$dataKey}}" class="btn btn-danger delete-title" style="float: left;margin-top:28px;">{{__('Delete')}}</button>
                                            </div>
                                        @endif
                                        <div class="col-md-12" id="set_option{{$dataKey}}">
                                            @if (isset($data->is_option))
                                                @foreach (json_decode($data->optionList) as $keyOption=>$option)
                                                    <div class="row" id="option_no_{{$optionCount}}">
                                                        <div class="col-md-8">
                                                            <label for="">{{__('Option Name')}}</label>
                                                            <input name="option[{{$dataKey}}][optionList][]" class="form-control" value="{{$option}}">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <button id="" type="button" data-id="{{$optionCount}}" class="btn btn-danger delete-option" style="float: left;margin-top:28px;">{{__('Delete')}}</button>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $optionCount++;
                                                    @endphp
                                                @endforeach
                                            @endif
                                            @if (isset($data->is_file))
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label for="country">{{__('File Type')}}</label>
                                                        <div class="cp-select-area">
                                                            <select name="option[{{$dataKey}}][file_type]" id="file_type" class="form-control" title="{{ __('Select Required Type') }}">
                                                                    <option value="jpg_png"
                                                                    @if ($data->file_type == 'jpg_png') selected @endif>{{__('jpg or png')}} </option>
                                                                    <option value="pdf_word" @if ($data->file_type == 'pdf_word') selected @endif> {{__('pdf or word')}} </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <label>{{__('Google Drive Link')}} ({{__('optional')}})</label>
                                                        <input type="text" class="form-control" name="option[{{$dataKey}}][file_link]" value="{{$data->file_link}}">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-12" id="selecct_option{{$dataKey}}">
                                            @if (in_array($data->type, [FORM_SELECT, FORM_RADIO, FORM_CHECKBOX]))
                                                <button type="button" class="btn theme-btn mt-4 add-new-option"
                                                data-id={{$dataKey}} style="float: right">{{__('Add New Option')}}</button> 
                                            @endif        
                                        </div>
                                    </div>
                                    @endforeach    
                                @else 
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="">{{__('Title')}}</label>
                                                <input type="text" class="form-control" name="option[0][title]">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="">{{__('Type')}}</label>
                                            <div class="cp-select-area">
                                                <select name="option[0][type]"  class="form-control dynamic_type" data-id='0' title="{{ __('Select Type') }}" >
                                                        <option value="{{FORM_INPUT_TEXT}}">{{formOptionType(FORM_INPUT_TEXT)}} </option>
                                                        <option value="{{FORM_SELECT}}">{{formOptionType(FORM_SELECT)}} </option>
                                                        <option value="{{FORM_RADIO}}"> {{formOptionType(FORM_RADIO)}} </option>
                                                        <option value="{{FORM_CHECKBOX}}"> {{formOptionType(FORM_CHECKBOX)}}</option>
                                                        <option value="{{FORM_TEXT_AREA}}"> {{formOptionType(FORM_TEXT_AREA)}}</option>
                                                        <option value="{{FORM_FILE}}"> {{formOptionType(FORM_FILE)}}</option>
                                                </select>
                                            </div>
                                            
                                        </div>
                                        <div class="col-md-4">
                                            <label for="country">{{__('Required')}}</label>
                                            <div class="cp-select-area">
                                                <select name="option[0][required]" id="type" class="form-control" title="{{ __('Select Required Type') }}" data-live-search="true" data-width="100%"
                                                    data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                                        <option value="0">{{__('No')}} </option>
                                                        <option value="1"> {{__('Yes')}} </option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-12" id="set_option0">
                                            
                                        </div>
                                        <div class="col-md-12" id="selecct_option0">
                                                        
                                        </div>
                                    </div>
                                @endif
                            
                            </div>
                            
                        
                        <div class="row mt-2">
                            <div class="col-sm-6 col-6">
                                <button type="submit" class="btn theme-btn" id="submit-form">{{__('Update')}}</button>
                            </div>
                            <div class="col-sm-6 col-6">
                                <button type="button" class="btn theme-btn" id="add-new"
                                        style="float: right">{{__('Add New')}}</button>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')

    <script>
        (function($) {
            "use strict";
            // var addNewTitlelimits = '{{ !empty($settings) ? max(array_keys($settings)) : 0 }}';
            var addNewTitlelimits = '{{count($formData)>0?count($formData)-1:0}}';
            var optionLimits = '{{$optionCount}}';
            $('#add-new').on('click',function(){
                addNewTitlelimits++;
                var addNewTitleData = addNewTitle(addNewTitlelimits);
                $('#add_new_title').append(addNewTitleData);
            });
            
            function addNewTitle(valueAddNewTitle)
            {
                var addNewTitleHtml =
                `<div class="row" id="tile_no_`+valueAddNewTitle+`">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">{{__('Title')}}</label>
                            <input type="text" class="form-control" name="option[`+valueAddNewTitle+`][title]">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="">{{__('Type')}}</label>
                        <div class="cp-select-area">
                            <select name="option[`+valueAddNewTitle+`][type]"  class="form-control dynamic_type" data-id=` + valueAddNewTitle +` title="{{ __('Select Type') }}" >
                                <option value="{{FORM_INPUT_TEXT}}">{{formOptionType(FORM_INPUT_TEXT)}} </option>
                                <option value="{{FORM_SELECT}}">{{formOptionType(FORM_SELECT)}} </option>
                                <option value="{{FORM_RADIO}}"> {{formOptionType(FORM_RADIO)}} </option>
                                <option value="{{FORM_CHECKBOX}}"> {{formOptionType(FORM_CHECKBOX)}}</option>
                                <option value="{{FORM_TEXT_AREA}}"> {{formOptionType(FORM_TEXT_AREA)}}</option>
                                <option value="{{FORM_FILE}}"> {{formOptionType(FORM_FILE)}}</option>
                            </select>
                        </div>
                        
                    </div>
                    <div class="col-md-2">
                        <label for="country">{{__('Required')}}</label>
                        <div class="cp-select-area">
                            <select name="option[`+valueAddNewTitle+`][required]" id="type" class="form-control" title="{{ __('Select Required Type') }}" data-live-search="true" data-width="100%"
                                data-style="btn-info" data-actions-box="true" data-selected-text-format="count > 4">
                                    <option value="0">{{__('No')}} </option>
                                    <option value="1"> {{__('Yes')}} </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button id="" type="button" data-id=` + valueAddNewTitle +` class="btn btn-danger delete-title" style="float: left;margin-top:28px;">{{__('Delete')}}</button>
                    </div>
                    <div class="col-md-12" id="set_option`+valueAddNewTitle+`">
                    
                    </div>
                    <div class="col-md-12" id="selecct_option`+valueAddNewTitle+`">
                                    
                    </div>
                </div>`;
                return addNewTitleHtml;
            }

            function checkBoxRadioOption(getOptionDataId)
            {
                var checkboxRadioHtml = 
                `<button type="button" class="btn theme-btn mt-4 add-new-option"
                    data-id=`+getOptionDataId+` style="float: right">{{__('Add New Option')}}</button>`
                return checkboxRadioHtml;
            }
            function fileOption(getOptionDataId)
            {
                var fileOptionHtml = 
                `<div class="row">
                    <div class="col-md-3">
                        <label for="country">{{__('File Type')}}</label>
                        <div class="cp-select-area">
                            <select name="option[`+getOptionDataId+`][file_type]" id="file_type" class="form-control" title="{{ __('Select Required Type') }}">
                                    <option value="jpg_png">{{__('jpg or png')}} </option>
                                    <option value="pdf_word"> {{__('pdf or word')}} </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <label>{{__('Google Drive Link')}} ({{__('optional')}})</label>
                        <input type="text" class="form-control" name="option[`+getOptionDataId+`][file_link]">
                    </div>
                </div>`
                return fileOptionHtml;

            }

            function setBoxRadioOption(getOptionDataId)
            {   
                optionLimits++;
                var setOptionHtml = 
                `<div class="row" id="option_no_`+optionLimits+`">
                    <div class="col-md-8">
                        <label for="">{{__('Option Name')}}</label>
                        <input name="option[`+getOptionDataId+`][optionList][]" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <button id="" type="button" data-id=` + optionLimits +` class="btn btn-danger delete-option" style="float: left;margin-top:28px;">{{__('Delete')}}</button>
                    </div>
                </div>`
                return setOptionHtml;
            }

            $(document).on('change','.dynamic_type', function () {
                var value = $(this).val();
                console.log(value);
                var OptionDataId = $(this).data('id');
                if(value === '{{FORM_SELECT}}' || value==='{{FORM_RADIO}}' || value === '{{FORM_CHECKBOX}}')
                {
                    var responseData = checkBoxRadioOption(OptionDataId);
                    $('#set_option'+OptionDataId).empty();
                    $('#selecct_option'+OptionDataId).empty().append(responseData);
                }else if(value === '{{FORM_FILE}}')
                {
                    var responseData = fileOption(OptionDataId);
                    $('#set_option'+OptionDataId).empty();
                    $('#selecct_option'+OptionDataId).empty().append(responseData);
                }else 
                {
                    $('#set_option'+OptionDataId).empty();
                    $('#selecct_option'+OptionDataId).empty();
                }
                
            });

            $(document).on('click','.add-new-option', function () {
                var setOptionDataId = $(this).data('id');
                var setOptionData = setBoxRadioOption(setOptionDataId);
                $('#set_option'+setOptionDataId).append(setOptionData);
            });
            $(document).on('click','.delete-title', function () {
                var deleteTitleId = $(this).data('id');
                $('#tile_no_'+deleteTitleId).remove();
            });
            $(document).on('click','.delete-option', function () {
                var deleteOptionId = $(this).data('id');
                $('#option_no_'+deleteOptionId).remove();
            });

  
        })(jQuery)
    </script>
@endsection
