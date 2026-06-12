<?php

namespace App\Services\HtmlRenders;

use App\Enums\CoinProvider;
use App\Enums\NetworkBase;

class CoinNetworkActionRenderer
{
    public static function render($network, $coin, $edit_route)
    {
        $html = '<ul class="d-flex activity-menu">';
        $html .= '
            <li class="viewuser">
                <a href="' . route($edit_route, encrypt($network->id)) . '" title="' . __("Update") . '" class="btn btn-primary btn-sm">
                    <i class="fa fa-pencil"></i>
                </a>
            </li>
        ';
        if (
            in_array(NetworkBase::tryFrom($network?->network?->base_type), [
                NetworkBase::COIN_PAYMENT,
                NetworkBase::BITGO_API,
                NetworkBase::BITCOIN_API,
            ])
        ) {
            $html .= '
                <li class="viewuser">
                    <a href="' . route('adminCoinSettings', encrypt($network->id)) . '" title="' . __("Settings") . '" class="btn btn-warning btn-sm">
                        <i class="fa fa-cog"></i>
                    </a>
                </li>
            ';
        }
        $html .= '</ul>';
        return $html;
    }
}