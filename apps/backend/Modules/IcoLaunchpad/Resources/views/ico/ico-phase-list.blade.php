@extends('admin.master')
@section('title', isset($title) ? $title : __('ICO List'))
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
    @php($user = auth()->user())
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <div class="table-responsive">
                            <table id="table" class="table table-borderless custom-table display text-center" width="100%">
                                <thead>
                                <tr>
                                    <th scope="col">{{__('Coin Price')}}</th>
                                    <th scope="col">{{__('Coin Currency')}}</th>
                                    <th scope="col">{{__('Total Token Supply')}}</th>
                                    <th scope="col">{{__('Start Date')}}</th>
                                    <th scope="col">{{__('End Date')}}</th>
                                    <th scope="col">{{__('Featured')}}</th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th scope="col">{{__('Action')}}</th>
                                    
                                    
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($ico_phases_list))
                                    @foreach($ico_phases_list as $value)
                                        <tr>
                                            <td> {{$value->coin_price}} </td>
                                            <td> {{$value->coin_currency}} </td>
                                            
                                            <td>
                                                {{$value->total_token_supply}}
                                            </td>
                                           <td>
                                                {{$value->start_date}}
                                           </td>
                                           <td>
                                                {{$value->end_date}}
                                           </td>
                                           <td>
                                            <label class="switch">
                                                <input type="checkbox" onclick="processFeatured('{{$value->id}}')"
                                                    id="notification" name="status" @if($value->is_featured == STATUS_ACTIVE) checked @endif>
                                                <span class="slider" for="status"></span>
                                            </label>
                                        </td>
                                           <td>
                                                <label class="switch">
                                                    <input type="checkbox" onclick="processForm('{{$value->id}}')"
                                                        id="notification" name="status" @if($value->status == STATUS_ACTIVE) checked @endif>
                                                    <span class="slider" for="status"></span>
                                                </label>
                                            </td>
                                           <td>
                                                <ul class="d-flex activity-menu">
                                                    <li class="viewuser">
                                                        <a href="{{route('editICOPhase', encrypt($value->id))}}" title="{{__("Update")}}" class="btn btn-primary btn-sm">
                                                            {{__('View')}}
                                                        </a>
                                                    </li>
                                                    @if ($value->is_updated == STATUS_ACTIVE)
                                                        <li class="viewuser">
                                                            <a href="{{route('updateRequestTableInfo',['type'=>ICO_TOKEN_PHASE_TABLE, 'id'=> encrypt($value->id)])}}" title="{{__("Update")}}" class="btn btn-warning btn-sm">
                                                                {{__('Update Request')}}
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($user->id == $value->user_id)
                                                        <li class="viewuser">
                                                            <a href="#delete1WV4d6uF6Ytu8v1Pl_{{($value->id)}}" data-toggle="modal" title="{{__("Delete")}}" class="btn btn-danger btn-sm">
                                                                <i class="fa fa-trash"></i>
                                                            </a>
                                                            <div id="delete1WV4d6uF6Ytu8v1Pl_{{($value->id)}}" class="modal fade delete" role="dialog">
                                                                <div class="modal-dialog modal-sm">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header"><h6 class="modal-title">{{__('Delete')}}</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                                                        <div class="modal-body"><p>{{ __('Do you want to delete ?')}}</p></div>
                                                                        <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                                                                            <a class="btn btn-danger"href="{{route('deleteICOPhase', encrypt($value->id))}}">{{__('Confirm')}} </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>                                     
                                                    @endif
                                                </ul>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6">{{__('No data found')}}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
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
            $('#table').DataTable({
                responsive: true,
                paging: true,
                searching: true,
                ordering:  true,
                select: false,
                bDestroy: true
            });
        })(jQuery);
        
        function processFeatured(id) {
            $.ajax({
                type: "POST",
                url: "{{ route('saveICOPhaseFeatured') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'id': id
                },
                success: function (data) {
                    
                    if(data.success == true)
                    {
                        VanillaToasts.create({
                            text: data.message,
                            type: 'success',
                            timeout: 4000
                        });
                        
                    }else{
                        VanillaToasts.create({
                            text: data.message,
                            type: 'warning',
                            timeout: 4000
                        });
                        
                    }
                }
            });
        }

        function processForm(id) {
            $.ajax({
                type: "POST",
                url: "{{ route('saveICOPhaseStatus') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'id': id
                },
                success: function (data) {
                    
                    if(data.success == true)
                    {
                        VanillaToasts.create({
                            text: data.message,
                            type: 'success',
                            timeout: 4000
                        });
                        
                    }else{
                        VanillaToasts.create({
                            text: data.message,
                            type: 'warning',
                            timeout: 4000
                        });
                        
                    }
                }
            });
        }
</script>
@endsection