<div class="row  mb-5">
    <div class="col-md-6 col-lg-3 mt-3 mt-md-0">
        <div class="h-100">
            <div class="sub_title rounded">
                <div class="d-flex justify-content-center flex-column align-items-center pt-3">
                    <span class="card-top-icon mb-3">
                        <i class="fa fa-ticket" aria-hidden="true"></i>
                    </span>
                    <h5>{{__('Total Ticket')}} ({{ $ticket_count['total_ticket_count']??0}})</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mt-3 mt-md-0">
        <div class="h-100">
            <div class="sub_title rounded">
                <div class="d-flex justify-content-center flex-column align-items-center pt-3">
                    <span class="card-top-icon mb-3">
                        <i class="fa fa-ticket" aria-hidden="true"></i>
                    </span>
                    <h5>{{__('Pending Ticket')}} ({{ $ticket_count['total_pending_ticket_count']??0}})</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mt-3 mt-lg-0">
        <div class="h-100">
            <div class="sub_title rounded">
                <div class="d-flex justify-content-center flex-column align-items-center pt-3">
                    <span class="card-top-icon mb-3">
                        <i class="fa fa-ticket" aria-hidden="true"></i>
                    </span>
                    <h5>{{__('Open Ticket')}} ({{ $ticket_count['total_open_ticket_count']??0}})</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mt-3 mt-lg-0">
        <div class="h-100">
            <div class="sub_title rounded">
                <div class="d-flex justify-content-center flex-column align-items-center pt-3">
                    <span class="card-top-icon mb-3">
                        <i class="fa fa-ticket" aria-hidden="true"></i>
                    </span>
                    <h5>{{__('Close Ticket')}} ({{ $ticket_count['total_close_ticket_count']??0}})</h5>
                </div>
            </div>
        </div>
    </div>
</div>
