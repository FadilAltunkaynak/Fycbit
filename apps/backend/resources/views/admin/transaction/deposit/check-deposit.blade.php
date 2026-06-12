@extends('admin.master', ['menu' => 'transaction', 'sub_menu' => 'check_deposit'])
@section('title', isset($title) ? $title : '')
@section('style')
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-9">
                <ul>
                    <li class="active-item">{{ $title }}</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="user-management">
        <div class="row">
            <div class="col-md-12">
                <div class="profile-info-form custom-box-shadow p-3">
                    <div>
                        <form action="{{ route('submitCheckDeposit') }}" method="get">
                            <div class="row">
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{ __('Coin Type') }}</div>
                                            <div class="cp-select-area">
                                                <select id="coin_type" class="form-control h-50">
                                                    <option>{{ __('Select a Coin') }}</option>
                                                    @foreach ($coins as $coin)
                                                        <option data-coin="{{ $coin->coin_type }}" value="{{ $coin->id }}">
                                                            {{ $coin->coin_type }}
                                                        </option>
                                                    @endforeach

                                                    <input type="hidden" name="coin_id">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{ __('Network') }}</div>
                                            <div class="cp-select-area">
                                                <select name="network_id" id="network_id" class="form-control h-50">
                                                    <option>{{ __('Select a network') }}</option>
                                                </select>
                                            </div>
                                            <small>{{ __('Please make sure your network is right.You never change this network. So be careful') }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group">
                                        <div class="controls">
                                            <div class="form-label">{{ __('Transaction Id') }}</div>
                                            <input type="text" class="form-control h-50" style="height: 38px !important"
                                                name="trx_id" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <input type="hidden" name="type" value="{{ CHECK_DEPOSIT }}">
                                    <button type="submit" class="btn theme-btn">{{ __('Submit') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mt-4">
                @if (isset($txId))
                    <div class="profile-info custom-box-shadow p-3">
                        <h4 class="text-center text-warning">{{ __('Transaction details') }}</h4>
                        <div class="table-responsive mt-3">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <td>{{ __('Transaction Coin') }}</td>
                                        <td>:</td>
                                        <td><span>{{ $coin_type ?? '' }}</span></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Transaction Hash') }}</td>
                                        <td>:</td>
                                        <td><span>{{ $txId ?? '' }}</span></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Address') }}</td>
                                        <td>:</td>
                                        <td><span>{{ $address ?? '' }}</span></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Amount') }}</td>
                                        <td>:</td>
                                        <td><span>{{ $amount ?? '' }} {{ $coin_type ?? '' }}</span></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Confirmations') }}</td>
                                        <td>:</td>
                                        <td><span>{{ $confirmations ?? '' }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <form action="{{ route('submitCheckDeposit') }}" method="get">
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="text-warning">
                                        {{ __('If deposit not found with this transaction id, you can adjust deposit by clicking below button') }}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <input type="hidden" name="coin_id" value="{{ $coin_id }}">
                                    <input type="hidden" name="network_id" value="{{ $network_id }}">
                                    <input type="hidden" name="trx_id" value="{{ $txId }}">
                                    <input type="hidden" name="type" value="{{ ADJUST_DEPOSIT }}">
                                    <button type="submit" class="btn theme-btn">{{ __('Adjust Deposit') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $('#coin_type').on('change', (e) => {
            const coin_id = $(e.currentTarget).val();
            const coin_type = $(e.currentTarget).find('option:selected').data('coin');
            const url = "{{ route('adminWalletNetworks', ':coin_type') }}".replace(':coin_type', coin_type);

            let optionsHtml = "<option value=''>{{ __('Select a network') }}</option>";

            $.get(url, function (response) {
                if(!response.success){
                    VanillaToasts.create({
                        text: response.message || '{{ _("Something went wrong") }}',
                        type: 'warning',
                        timeout: 5000
                    });
                    return;
                }
                optionsHtml += response.data.networks.map(network =>
                    `<option value="${network.id}">${network.name}</option>`
                ).join('');
                $('select[name="network_id"]').html(optionsHtml);

                $('input[name="coin_id"]').val(coin_id);
            });
        });
    </script>
@endsection