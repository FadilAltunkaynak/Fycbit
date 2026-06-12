@extends('admin.master',['menu'=>'system_wallet'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{__('Network')}}</li>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->

    <!-- User Management -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="profile-info-form">
                    <div>
                        {{Form::open(['route'=>'createSystemWalletProccess', 'files' => true, 'method' => 'POST'])}}
                        @if(isset($item->uid))
                            <input type="hidden" name="uid" value="{{ $item->uid }}" />
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Network')}}</div>
                                        <select name="network_id" id="" class="form-control" @if(isset($item->uid)) disabled @endif>
                                                <option>{{ __("Select a network") }}</option>
                                            @foreach($networks as $network)
                                                <option @if(isset($item) && ($item->network_id == $network->id)) selected @endif value="{{ $network->id }}">{{ $network->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @if(!isset($item->uid))
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{__('Wallet')}}</div>
                                            <select name="creation_type" id="creation_type" class="form-control">
                                                    <option value="{{ STATUS_ACTIVE }}">{{ __("Add Existing Wallet") }}</option>
                                                    <option value="{{ STATUS_INACTIVE }}">{{ __("Genarate New Wallet") }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-6 hideByCreation">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Wallet Address')}}</div>
                                        <input type="text" class="form-control" name="address" @if(isset($item))value="{{$item->address}}" @else value="{{old('address')}}" @endif />
                                        <pre class="text-danger">{{$errors->first('address')}}</pre>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 hideByCreation">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Wallet Privat Key')}}</div>
                                        <input type="password" class="form-control" name="private_key" @if(isset($item->uid)) value="encrypted private key" disabled @endif>
                                        <pre class="text-danger">{{$errors->first('private_key')}}</pre>
                                    </div>
                                </div>
                                @if(isset($item->uid))
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="float-right">
                                                <a href="#viewWalletKey" data-toggle="modal" class="bg-success col-md-4 p-1 text-white font-weight-bold view-wallet-key">
                                                    {{__('View Wallet Private Key')}}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{__('Status')}}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="status" @if(isset($item) && $item->status==1)checked  @endif>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                @if(isset($item))  
                                    <button type="submit" class="btn theme-btn">{{ __("Update") }}</button>
                                @else
                                    <button type="submit" class="btn theme-btn">{{ __("Create") }}</button>
                                @endif
                                
                            </div>
                        </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(isset($item->uid))
        <div class="custom-breadcrumb mt-4">
            <div class="row">
                <div class="col-md-9">
                    <ul>
                        <li>{{__('Network')}}</li>
                        <li class="active-item">{{ $title }}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row ">
            <div class="col-md-6">
                <input type="password" id="pkkey" class="form-control" placeholder="Insert private key and check with address">
                <input type="hidden" id="network_id" value="{{ $item->network_id }}">
            </div>
            <div class="col-md-6">
                <a href="javascript:" class="btn theme-btn " onclick="check_wallet_address()">{{__('Check Wallet Address')}}</a>
            </div>
            <div class="col-md-6">
                <h3 class="text-danger mt-2 " id="check_wallet_address_message"></h3>
            </div>
        </div>
    @endif


    <!-- /User Management -->
    <div id="viewWalletKey" class="modal fade delete" role="dialog">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        {{__('View Wallet Key')}}
                    </h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="view_wallet_key_submit_form">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="id" value="{{ isset($item->id) ? custom_encrypt($item->id) : 0}}">
                            <div class="col-md-12" id="user_password_input_details">
                                <div class="form-group">
                                    <label for="#">{{__('Enter Your System Password')}}</label>
                                    <input id="view_wallet_key_user_password" type="password" name="password" class="form-control">
                                </div>
                            </div>
                            <div id="view_details_wallet_key" class="col-md-12 d-none">

                                    <div class="form-group">
                                        <div class="d-flex justify-content-between">
                                        <label for="#">{{__('Wallet Key')}}</label>
                                        <a class="btn btn-sm btn-success" id="copy_wallet_key">
                                            {{__('Copy')}}
                                        </a>
                                    </div>
                                        <input type="text" id="wallet_key_view" class="form-control" value="">
                                    </div>
                            </div>
                            @if(checkGoogleAuth())
                                <div class="col-md-12" id="user_password_google_authenticator_input_details">
                                    <div class="form-group">
                                        <label for="#">{{__('Google Authenticator Code')}}</label>
                                        <input id="view_wallet_key_google_authenticator" type="text" name="google_authenticator" class="form-control">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" data-dismiss="modal">
                            {{__("Cancel")}}
                        </button>
                        <button type="submit" id="submit_password" class="btn theme-btn">
                            {{__('Confirm')}}
                        </button>
                        <button type="button" id="update_pv" class="btn btn-primary d-none">
                            {{__('Update')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>
    (function($) {
        "use strict";
        $("#creation_type").change((e)=>{
            var value = $(e.target).val();
            if(value == 0) {
                $(".hideByCreation").hide();
            }else{
                $(".hideByCreation").show();
            }
        });

        $("#viewWalletKey").on('show.bs.modal', function () {
            $('#view_details_wallet_key').addClass('d-none');
            $('#user_password_input_details').removeClass('d-none');
            $('#wallet_key_view').val('');
            $('#view_wallet_key_user_password').val('');
            $('#submit_password').removeClass('d-none');
            $('#update_pv').addClass('d-none');
            @if(checkGoogleAuth())
                $('#user_password_google_authenticator_input_details').removeClass('d-none');
                $('#view_wallet_key_google_authenticator').val('');
            @endif
        });

        $('#copy_wallet_key').click(function() {
            var textToCopy = $('#wallet_key_view').val();
            console.log(textToCopy);
            var tempTextarea = $('<textarea>');
            $('body').append(tempTextarea);
            tempTextarea.val(textToCopy).select();
            document.execCommand('copy');
            tempTextarea.remove();
        });

        $('#view_wallet_key_submit_form').submit(function(event) {
        event.preventDefault(); // Prevent default form submission

        var formData = $(this).serialize(); // Serialize form data

        console.log(formData);

        $.ajax({
            url: "{{ route('viewSystemWalletey') }}", // URL to submit the form
            type: "POST",
            data: formData, // Form data
            dataType: "json", // Response type
            success: function(response) {
                // Handle success response
                console.log(response);
                if(response.success == true)
                {
                    VanillaToasts.create({
                        text: response.message,
                        backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                        type: 'success',
                        timeout: 40000
                    });

                    $('#view_details_wallet_key').removeClass('d-none');
                    $('#user_password_input_details').addClass('d-none');
                    $('#wallet_key_view').val(response.data);
                    $('#view_wallet_key_user_password').val('');
                    $('#submit_password').addClass('d-none');
                    $('#update_pv').removeClass('d-none');
                    @if(checkGoogleAuth())
                        $('#view_wallet_key_google_authenticator').val('');
                    @else
                        $('#user_password_google_authenticator_input_details').addClass('d-none');
                    @endif
                }else{
                    VanillaToasts.create({
                        text: response.message,
                        type: 'warning',
                        timeout: 40000
                    });
                    console.log('else');
                }
            },
            error: function(xhr, status, error) {
                // Handle error response
                console.log(error);
            }
        });
    });

    $("#update_pv").on('click', (event)=>{
        let id = $('input[name="uid"]').val();
        let pv = $("#wallet_key_view").val();
        update_pv(id, pv);
    });

    function update_pv(id, pv){
        @if(checkGoogleAuth())
            let code = $("#view_wallet_key_google_authenticator").val();
        @endif
        $.post(
            '{{ route("updateSystemWalletey") }}',
            {
                _token: '{{ csrf_token() }}',
                id: id,
                pv: pv,
                @if(checkGoogleAuth())
                    code : code,
                @endif
            },
            function(response){
                if(response.success){
                    VanillaToasts.create({
                        text: response.message,
                        backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                        type: 'success',
                        timeout: 40000
                    });
                }else{
                    VanillaToasts.create({
                        text: response.message,
                        type: 'warning',
                        timeout: 40000
                    });
                }
            }
        );
    }

    })(jQuery);

    function check_wallet_address()
    {
        var wallet_key = $('#pkkey').val();
        var network_id = $('#network_id').val();

        if(wallet_key !== '')
        {
            $.ajax({
                type: "POST",
                url: "{{ route('systemWalletCheckAddress') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'wallet_key': wallet_key,
                    'network_id': network_id
                },
                success: function (data) {
                    if(data.success)
                        $('#check_wallet_address_message').empty().text(data.message + "\n" + data.data.address);
                    else
                        $('#check_wallet_address_message').empty().text(data.message || "{{ __('Wallet address not found by private key') }}");
                }
            });


        }else{
            $('#check_wallet_address_message').empty().text('{{__('please, Insert wallet key First')}}');
        }

    }
</script>
@endsection
