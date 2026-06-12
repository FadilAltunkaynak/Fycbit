<div class="row">
    <div class="col-md-12 ">
        <form action="{{url()->current()}}" method="get">
            <div class="row">
                <div class="col-12 d-flex gap-2">
                    <input placeholder="{{ __('Search Ticket ID or Title or Puchase Code') }}" class="form-control search-field" type="text" name="search" value="{{$search}}">
                    <button class="btn btn-search" type="submit">
                        {{__('Search')}}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-12">
         <form action="{{url()->current()}}" method="get">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <label for="">{{ __('Select Project') }}</label>
                    <select name="project" class="form-control">
                        @foreach (supportTicketProjectList() as $ticket_project_key=>$ticket_project)
                            <option value="{{$ticket_project->id }}"
                                {{$project == $ticket_project->id ? 'selected':''}}>{{$ticket_project->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 mt-3 mt-md-0">
                    <label for="">{{ __('Select Status') }}</label>
                    <select name="status" class="form-control">
                        @foreach (ticketStatus() as $ticket_status_key=>$ticket_status)
                            <option value="{{$ticket_status_key}}"
                                {{$status == $ticket_status_key ? 'selected':''}}>{{$ticket_status}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 mt-3 mt-lg-0">
                    <label for="">{{ __('From') }}</label>
                    <input class="form-control" type="date" name="from_date" value="{{$from_date}}">
                </div>
                <div class="col-lg-2 col-md-4 mt-3 mt-lg-0">
                    <label for="">{{ __('To') }}</label>
                    <input class="form-control" type="date" name="to_date" value="{{$to_date}}">
                </div>
                <div class="col-lg-2 col-md-4 mt-3 mt-lg-0">
                    <button type="submit" class="btn btn-search mt-4 w-100">{{ __('Filter Ticket') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
