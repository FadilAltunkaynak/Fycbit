@extends('admin.master')
@section('title', isset($title) ? $title : __('Submitted form details for ICO'))
@section('style')
@endsection
@section('sidebar')
@include('icolaunchpad::layouts.sidebar',['menu'=>'submitted_form_list'])
@endsection
@section('content')
    <!-- breadcrumb -->
    <div class="custom-breadcrumb">
        <div class="row">
            <div class="col-md-8">
                <ul>
                    <li class="active-item">{{$title}}</li>
                </ul>
            </div>
            <div class="col-md-4">
                <div class="pull-right">
                    @if ($submitted_form->status == STATUS_PENDING )
                        <a href="#accepted_ICO" data-toggle="modal" class="add-btn theme-btn">{{__('Accept')}}</a>
                        <a href="#reject_ICO" data-toggle="modal" class="add-btn theme-btn">{{__('Reject')}}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- /breadcrumb -->
    <div class="user-management">
        <div class="row">
            <div class="col-12">
                @if (isset($submitted_form->formDetails))
                    @foreach ($submitted_form->formDetails as $key=>$item)
                        <div class="card-body">
                            <div class="col-md-12">
                                <div class="text-white h4">
                                   <span>{{$key+1}}.</span>
                                    <span class="">{{$item->question}}</span>
                                    <br>
                                    <span>{{__('Answer')}}:</span>
                                    @if ($item->is_input == true)
                                        <span><strong>{{$item->answer}}</strong></span>
                                    @elseif($item->is_option == true)
                                    
                                        @if (isset($item->answer))
                                            @foreach (json_decode($item->answer) as $k=>$op_value)
                                                <span>
                                                    <strong>
                                                        {{$k+1}}. {{$op_value}}
                                                        @if (count(json_decode($item->answer)) !=$k+1)
                                                            ,
                                                        @endif
                                                    </strong>
                                                </span>
                                            @endforeach    
                                        @endif
                                    @elseif($item->is_file == true)
                                        @if (isset($item->answer))
                                            <span>
                                                <a href="{{$item->answer}}" target="_blank">
                                                    {{__('Click here to check File')}}
                                                </a>
                                            </span>
                                        @else
                                            <span>
                                                {{__('Not Upload')}}
                                            </span>
                                        @endif
                                    @endif
                                        
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div id="accepted_ICO" class="modal fade delete" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">{{__('Accepted')}} </h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{route('accpetedSubmittedFormICO')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <p>{{__('Are You sure? Want to accept it.')}}</p>
                        <input type="hidden" name="unique_id" value="{{$submitted_form->unique_id}}">
                        <label for="">{{__('Message')}}:</label>
                        <textarea class="form-control" name="message" rows="3" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                        <button class="btn btn-danger" type="submit">{{ __('Confirm')}}</a>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
    <div id="reject_ICO" class="modal fade delete" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">{{__('Reject')}} </h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{route('rejectedSubmittedFormICO')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <p>{{__('Are You sure? Want to reject it.')}}</p>
                        <input type="hidden" name="unique_id" value="{{$submitted_form->unique_id}}">
                        <label for="">{{__('Message')}}:</label>
                        <textarea class="form-control" name="message" rows="3" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{__("Close")}}</button>
                        <button class="btn btn-danger" type="submit">{{ __('Confirm')}}</a>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>
  
</script>
@endsection