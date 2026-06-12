<div class="user-management">
    <div class="row">
        <div class="col-12">
            <div class="header-bar">
                <div class="table-title">
                    <h3>{{ __('Announcement Section') }}</h3>
                </div>
                <div class="right d-flex align-items-center">
                    <div class="add-btn">
                        <a href="{{ route('adminAnnouncementAdd') }}">{{ __('+ Add') }}</a>
                    </div>
                </div>
            </div>
            <div class="table-area">
                <div>
                    <table id="announcement-table" class="table table-borderless custom-table display" width="100%">
                        <thead>
                        <tr>
                            <th class="all">{{ __('Title') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Created At') }}</th>
                            <th class="all text-lg-center">{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
