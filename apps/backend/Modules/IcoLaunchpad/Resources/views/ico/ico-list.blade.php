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
            <div class="col-sm-7 text-right">
                <a class="add-btn theme-btn" href="{{route('addNewICO')}}">{{__('Add New')}}</a>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    @php($user = auth()->user())
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                <div class="header-bar p-4">
                    <div class="table-title">
                        <h3>{{ $title }}</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-area payment-table-area">
                        <div class="table-responsive">
                            <table id="table" class="table table-borderless custom-table display text-center" width="100%">
                                <thead>
                                <tr>
                                    <th scope="col">{{__('Token Name')}}</th>
                                    <th scope="col">{{__('Coin Type')}}</th>
                                    <th scope="col">{{__('User Email')}}</th>
                                    <th scope="col">{{__('Approved Status')}} </th>
                                    <th scope="col">{{__('Created At')}}</th>
                                    <th scope="col">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($ico_list))
                                    @foreach($ico_list as $value)
                                        <tr>
                                            <td> {{$value->token_name}} </td>
                                            <td> {{$value->coin_type }}</td>
                                            <td>
                                                {{isset($value->user)?$value->user->email: __('Not Found')}}
                                            </td>
                                           <td>
                                                @if ($value->approved_status == STATUS_ACCEPTED)
                                                    
                                                    {!!ICOstatusAction(STATUS_ACCEPTED)!!}
                                                    
                                                @elseif($value->approved_status == STATUS_PENDING)
                                                    
                                                    {!!ICOstatusAction(STATUS_PENDING)!!}
                                                    
                                                @elseif($value->approved_status == STATUS_MODIFICATION)
                                                    
                                                    {!!ICOstatusAction(STATUS_MODIFICATION)!!}
                                                    
                                                @elseif($value->approved_status == STATUS_REJECTED)
                                                   
                                                    {!!ICOstatusAction(STATUS_REJECTED)!!}
                                                    
                                                @endif
                                           </td>
                                            <td>
                                                {{$value->created_at}}
                                            </td>
                                            <td>
                                                <ul class="d-flex activity-menu">
                                                    <li class="viewuser">
                                                        <a href="{{route('editICO', encrypt($value->id))}}" title="{{__("Update")}}" class="btn btn-primary btn-sm">
                                                            {{__('view')}}
                                                        </a>
                                                    </li>
                                                    <li class="viewuser">
                                                        <a href="{{route('listICOPhase', encrypt($value->id))}}" title="{{__("Update")}}" class="btn btn-warning btn-sm">
                                                            {{__('Phases')}}
                                                        </a>
                                                    </li>
                                                    @if ($value->is_updated == STATUS_ACTIVE)
                                                        <li class="viewuser">
                                                            <a href="{{route('updateRequestTableInfo',['type'=>ICO_TOKEN_TABLE, 'id'=> encrypt($value->id)])}}" title="{{__("Update")}}" class="btn btn-warning btn-sm">
                                                                {{__('Update Request')}}
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($user->id == $value->user_id)
                                                        <li class="viewuser">
                                                            <a href="{{route('addNewICOPhase', encrypt($value->id))}}" title="{{__("Update")}}" class="btn btn-info btn-sm">
                                                                {{__('Phases Create')}}
                                                            </a>
                                                        </li>
                                                        <li class="viewuser">
                                                            <a href="{{route('translationListICO', encrypt($value->id))}}" title="{{__("Update Language Text")}}" class="btn btn-info btn-sm">
                                                                {{__('Update Language')}}
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li class="viewuser">
                                                        <a href="{{route('getICOChatDetails', encrypt($value->id))}}" title="{{__("Chat")}}" class="btn btn-success btn-sm">
                                                            {{__('Chat')}}
                                                        </a>
                                                    </li>
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
        
        function processForm(id) {
            $.ajax({
                type: "POST",
                url: "{{ route('icoStatusChange') }}",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'id': id
                },
                success: function (data) {
                    console.log(data);
                }
            });
        }
</script>
@endsection