@extends('knowledgebase::layouts.master')
@section('content')
<!-- body text section start -->
<section class="my-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-lg-3">
                @include('knowledgebase::user.support.include.sidebar',$ticket_count)
            </div>
            <div class="col-md-8 col-lg-9">
                @include('knowledgebase::user.support.include.support_card',$ticket_count)
                @include('knowledgebase::user.support.include.ticket_filter')
                <div class="row mt-4">
                    @if (isset($ticket_list) && $ticket_list->count()>0)
                        @foreach ($ticket_list as $ticket)
                            <div class="col-12 my-2 ticket-card">
                                <div class="card p-3 {{$ticket->is_seen_by_user == UNSEEN ? 'bg-light' :''}}">
                                    <a href="{{route('support_ticket_conversation_details',[$ticket->unique_code])}}">
                                        <div class="row">
                                            <div class="col-md-8 ticket-card-inner">
                                                <h4 class="fw_600 text-dark p_color uppercase">{{__('#')}}{{$ticket->id}}</h4>
                                                <h6> <small>{{$ticket->created_at}}</small></h6>
                                                <h6 class="fw_600 text-dark py-2">{{__('Title')}}: {{$ticket->title}}</h6>
                                            </div>
                                            <div class="col-md-4 p_color">
                                                <p><b>{{__('Status')}}: {!! ticketStatusAdmin($ticket->status) !!}</b></p>
                                                <p><b>{{__('Project Name')}}:</b> <small>{{isset($ticket->project)?$ticket->project->name:__('Not Found')}}</small></p>
                                                <p><b>{{__('Assign To')}}:</b> <small>{{isset($ticket->agent) ? $ticket->agent->first_name.' '.$ticket->agent->last_name : 'Not Assign'}}</small></p>
                                                <p><b>{{__('Last Reply')}}:</b> <small>{{\Carbon\Carbon::parse($ticket->last_conversation_time)->diffForHumans()}}</small></p>
                                            </div>
                                            <hr>
                                            <div class="col-12">
                                                <p class="pt-3 p_color">
                                                    <b>{{__('Last Message')}}:</b>
                                                    <br>
                                                    <small>{!!isset($ticket->last_conversation)?Str::limit($ticket->last_conversation->message,300):Str::limit($ticket->description,150)!!}</small
                                                    >
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    {{$ticket_list->links()}}
                    @else
                        <div class="col-12 my-2 ticket-card">
                            <div class="card p-3 ">
                                <span class="fw_600 text-dark text-center">{{__('No Ticket Found')}}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
<!--  body text section end -->

@endsection
