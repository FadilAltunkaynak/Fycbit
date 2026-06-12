<div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        {{ __("Actions") }}
    </button>

    <div class="dropdown-menu py-0 my-0 shadow-sm border-0" aria-labelledby="dropdownMenuButton">
        <a class="dropdown-item" href="{{ route('future.coin-pairs.edit', [$model->uid]) }}" style="background:#f8f6ff;">
            <i class="fa fa-edit text-purple mr-2"></i> {{ __("View") }}&{{ __("Edit") }}
        </a>

        <a class="dropdown-item" href="{{ route('future.leverage.list', $model->uid) }}" style="background:#f5faff;">
            <i class="fa fa-cogs text-info mr-2"></i> {{ __("Leverage Settings") }}
        </a>

        <a class="dropdown-item" href="{{ route('future.coin-pairs.bot-settings.edit', [$model->uid]) }}" style="background:#f5fff8;">
            <svg class="text-info mr-2" height="20" width="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free v5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M32,224H64V416H32A31.96166,31.96166,0,0,1,0,384V256A31.96166,31.96166,0,0,1,32,224Zm512-48V448a64.06328,64.06328,0,0,1-64,64H160a64.06328,64.06328,0,0,1-64-64V176a79.974,79.974,0,0,1,80-80H288V32a32,32,0,0,1,64,0V96H464A79.974,79.974,0,0,1,544,176ZM264,256a40,40,0,1,0-40,40A39.997,39.997,0,0,0,264,256Zm-8,128H192v32h64Zm96,0H288v32h64ZM456,256a40,40,0,1,0-40,40A39.997,39.997,0,0,0,456,256Zm-8,128H384v32h64ZM640,256V384a31.96166,31.96166,0,0,1-32,32H576V224h32A31.96166,31.96166,0,0,1,640,256Z"/></svg> {{ __("Bot Settings") }}
        </a>

        @if ($model->is_chart_updated == STATUS_PENDING)
            <a class="dropdown-item" href="#futureChartUpdate_{{ $model->id }}" data-toggle="modal" style="background:#f0fff4;">
                <i class="fa fa-bar-chart text-success mr-2"></i> {{ __("Update Chart Data") }}
            </a>
        @endif

        <!-- <a class="dropdown-item" href="#" style="background:#f4fff8;">
            <i class="fa fa-percent text-success mr-2"></i> {{ __("Funding Rate Settings") }}
        </a> -->

        <a class="dropdown-item delete-url" onclick="deleteCoinPairModel(event)" href="#" data-url="{{ route('future.coin-pairs.delete', [$model->uid]) }}" style="background:#fff5f5;">
            <i class="fa fa-trash text-danger mr-2"></i> {{ __("Delete") }}
        </a>
    </div>
</div>

@if ($model->is_chart_updated == STATUS_PENDING)
    <div id="futureChartUpdate_{{ $model->id }}" class="modal fade delete" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">{{ __('Update Chart Data') }}</h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Do you want to get chart data from api ?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Close') }}</button>
                    <a class="btn btn-danger" href="{{ route('future.coin-pairs.chart-update', [$model->uid]) }}">{{ __('Confirm') }}</a>
                </div>
            </div>
        </div>
    </div>
@endif
