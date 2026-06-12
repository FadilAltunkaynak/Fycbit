@extends('admin.master', ['menu' => 'coin_namagement', 'sub_menu' => 'network'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-9">
                <ul>
                    <li>{{ __('Network') }}</li>
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
                        {{ Form::open(['route' => 'createNetworkProcess', 'files' => true, 'method' => 'POST']) }}
                        @if (isset($item->id))
                            <input type="hidden" name="id" value="{{ $item->id }}" />
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Name') }}</div>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name', @$item->name) }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Type') }}</div>
                                        <select name="slug" id="base_type" class="form-control"
                                            @if (isset($item)) disabled @endif>
                                            <option>{{ __('Select a type') }}</option>
                                            @foreach ($network ?? [] as $net)
                                                <option value="{{ $net->slug }}"
                                                    {{ $net->slug == old('slug', @$item->slug) ? 'selected' : '' }}>
                                                    {{ $net->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-md-6 evm_base">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Block Confirmation') }}</div>
                                        <pre class="text-danger">{{ $errors->first('name') }}</pre>
                                    </div>
                                </div>
                            </div> --}}
                            <input type="hidden" class="form-control" name="block_confirmation"
                                value="{{ @$item->block_confirmation ?? 1 }}">

                            <div class="col-md-6 evm_base">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('RPC Node Url') }}</div>
                                        <input type="text" class="form-control" name="rpc_url" id="rpcUrl"
                                            value="{{ old('rpc_url', @$item->rpc_url) }}">
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-md-6 evm_base">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('WSS Url') }}</div>
                                        <input type="text" class="form-control" name="wss_url"
                                            @if (isset($item)) value="{{ $item->wss_url }}" @endif>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="col-md-6 evm_base">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Explorer Url') }}</div>
                                        <input type="text" class="form-control" name="explorer_url"
                                            value="{{ old('explorer_url', @$item->explorer_url) }}" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-label">{{ __('Network Icon') }}</div>
                                    <input type="file" class="dropify" name="logo"
                                        @if (isset($item)) data-default-file="{{ !empty($item->logo) ? asset(IMG_NETWORK_LOGO_PATH . $item->logo) : asset('assets/img/dlr.png') }}" @endif>
                                </div>
                            </div>

                            @if (isset($item->id))
                                <div class="col-md-6 evm_base">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{ __('Last Processed Block') }}</div>
                                            <input type="number" class="form-control" name="block_number"
                                                value="{{ old('block_number', @$item->block->block_number ?: 0) }}" />
                                        </div>
                                    </div>
                                    {{-- <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{ __('From Block Number') }}</div>
                                            <input type="number" class="form-control" name="from_block_number"
                                                value="{{ old('from_block_number', @$item->from_block_number ?: 0) }}" />
                                        </div>
                                    </div> --}}
                                    {{-- <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{ __('To Block Number') }}</div>
                                            <input type="number" class="form-control" name="to_block_number"
                                                value="{{ old('to_block_number', @$item->to_block_number ?: 0) }}" />
                                        </div>
                                    </div> --}}

                                    @if (isset($block_error) && $block_error)
                                        <div class="form-group">
                                            <div class="controls">
                                                <div class="form-label">
                                                    {{ __('Block Processing Error: ') }}
                                                    <span id="block_error" class="text-danger">{{ $block_error }}</span>
                                                </div>

                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="controls">
                                        <div class="form-label">{{ __('Status') }}</div>
                                        <label class="switch">
                                            <input type="checkbox" name="status"
                                                {{ @$item->status == STATUS_ACTIVE ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn theme-btn w-25">
                                    {{ isset($item) ? __('Update') : __('Create') }}
                                </button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Management -->
    @if (isset($item) &&
            ($item->base_type == TRC20_TOKEN || $item->base_type == EVM_BASE_COIN || $item->base_type == SOLANA_BASE_COIN))
        <hr>
        <div class="user-management mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="header-bar">
                        <div class="table-title">
                            <h3>{{ __('Check Current Block Number ') }}</h3>
                            <h3 class="mt-4" id="result"></h3>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="profile-info-form">
                        <div>
                            @if (isset($item->id))
                                <input type="hidden" id="getNetId" value="{{ $item->id }}" />
                            @endif

                            <div class="row">
                                <div class="col-md-2">
                                    @if (isset($item))
                                        <button type="button" id="checker"
                                            class="btn btn-success">{{ __('Check') }}</button>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    <script>
        (function($) {
            "use strict";
            var networks = <?= $network ?>;
            $('#base_type').change((e) => {
                var value = $(e.target).val();
                //change_base_type(value);
                fill_up(value);
            });

            function fill_up(net) {
                var network = null;
                networks.forEach(($net) => {
                    if ($net.slug == net) network = $net;
                });
                $('input[name="explorer_url"]').val(network.base_url)
                console.log('nn', network);
            }

            function change_base_type(type) {
                $('.evm_base').addClass('d-none');
                if (type == 8 || type == 6 || type == 10) $('.evm_base').removeClass('d-none');
            }
            @if (isset($item))
                change_base_type({{ $item->base_type }});
            @endif

            $('#checker').click(function() {
                var id = $('#getNetId').val();
                var rpc = $('#rpcUrl').val();

                if (rpc == '') {
                    VanillaToasts.create({
                        text: 'Add your RPC url first',
                        type: 'warning',
                        timeout: 40000
                    });
                }
                if (id == '') {
                    VanillaToasts.create({
                        text: 'Network id is missing',
                        type: 'warning',
                        timeout: 40000
                    });
                }
                $.ajax({
                    type: "POST",
                    url: "{{ route('checkCurrentBlock') }}",
                    data: {
                        '_token': "{{ csrf_token() }}",
                        'id': id
                    },
                    success: function(res) {
                        console.log(res);
                        if (res.success == true) {
                            VanillaToasts.create({
                                text: 'Latest block number is ' + res.data,
                                type: 'success',
                                timeout: 40000
                            });
                            $('#result').text('The result is ' + res.data)
                        } else {
                            VanillaToasts.create({
                                text: res.message,
                                type: 'warning',
                                timeout: 40000
                            });
                        }
                    }
                })
            })

        })(jQuery);
    </script>
@endsection
