<?php

use App\Model\Network;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isNull;

use Modules\IcoLaunchpad\Entities\IcoPhaseInfo;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;

const IMG_ICO_PATH = 'uploaded_file/uploads/ico/';
const FILE_ICO_STORAGE_PATH = 'ico/';
const FILE_ICO_CHAT_STORAGE_PATH = 'ico/chat/';
const FILE_ICO_VIEW_PATH = 'storage/ico/';
const FILE_ICO_CHAT_VIEW_PATH = 'storage/ico/chat/';

// Table type to update
const ICO_TOKEN_TABLE = 1;
const ICO_TOKEN_PHASE_TABLE = 2;
const COIN_TABLE = 3;

//PHASE SORT BY
const PHASE_SORT_BY_EXPIRED = 1;
const PHASE_SORT_BY_FEATURED = 2;
const PHASE_SORT_BY_RECENT = 3;
const PHASE_SORT_BY_FUTURE = 4;

//Phase Available Status
const PHASE_AVAILABLE_STATUS_EXPIRED = 1;
const PHASE_AVAILABLE_STATUS_ACTIVE = 2;
const PHASE_AVAILABLE_STATUS_FUTURE = 3;

//conversation type
const CONVERSATION_TYPE_ICO_TOKEN = 1;

//conversation seen
const CONVERSATION_UNSEEN = 0;
const CONVERSATION_SEEN = 1;

//form option type
const FORM_INPUT_TEXT = 1;
const FORM_SELECT = 2;
const FORM_RADIO = 3;
const FORM_CHECKBOX = 4;
const FORM_TEXT_AREA = 5;
const FORM_FILE = 6;

// Approve Status
const APPROVE_STATUS_PENDING = 0;
const APPROVE_STATUS_MODIFICATION = 3;


//form
function formOptionType($input = null)
{
    $output = [
        FORM_INPUT_TEXT => __('Input Text'),
        FORM_SELECT => __('Select'),
        FORM_RADIO => __('Radio'),
        FORM_CHECKBOX => __('Checkbox'),
        FORM_TEXT_AREA => __('Text Area'),
        FORM_FILE => __('File'),
    ];
    if (is_null($input)) {
        return $output;
    } else {
        return $output[$input];
    }
}

function htmlPaymentMethod($index = null)
{
    try {
        $arr = [
            BANK_DEPOSIT => __("Bank Deposit"),
            STRIPE => __("Stripe"),
            PAYPAL => __("PayPal"),
            PAYSTACK => __('Paystack'),
            CRYPTO => __("Crypto")
        ];
        if ($index)
            return $arr[$index];
        else
            return $arr;
    } catch (\Exception $e) {
        return __("Payment method not found");
    }
}

function getTotalSoldTokenICO()
{
    return TokenBuyHistory::whereIn('status', [STATUS_PENDING, STATUS_SUCCESS])->sum('amount');
}

function getTotalSuppliedTokenICO()
{
    return IcoPhaseInfo::sum('total_token_supply');
}

function getAllowedImagesICO()
{
return ['png', 'jpg', 'jpeg', 'gif'];
}

function checkSoldIcoToken($phaseId=null)
{
    if (isNull($phaseId)) {
        return TokenBuyHistory::
            where('phase_id',$phaseId)
            ->whereIn('status',[STATUS_PENDING,STATUS_ACTIVE])
            ->sum('amount');
    } else {
        return TokenBuyHistory::where('phase_id',$phaseId)
            ->whereIn('status',[STATUS_PENDING,STATUS_ACTIVE])
            ->sum('amount');
    }

}

// check phase total participated
function phaseTotalParticipated($phase_id = null)
{
    if (!is_null($phase_id)) {
        return DB::table('token_buy_histories')->select("user_id")->where("phase_id", $phase_id)->groupBy("user_id")->get()->count();
    } else {
        return DB::table('token_buy_histories')->select("user_id")->groupBy("user_id")->get()->count();
    }
}

function getNetworkNameByType($type = null)
{
    if($network = Network::find($type)){
        return $network->name;
    }
    return __('Network Not Found');

    $output = [
        ERC20_TOKEN => __('ERC20 Token'),
        BEP20_TOKEN => __('BEP20 Token'),
    ];
    if (is_null($type)) {
        return $output;
    }else if ($type === 'network_list') {
        $network_list = [];
        $index        = 0;
        foreach($output as $id => $value){
            $network_list[$index]['id'] = $id;
            $network_list[$index++]['name'] = $value;
        }
        return $network_list;
    } else {
        return $output[$type] ?? __('N/A');
    }
}


function icoBankShowHtml($payment_details, $sleep = null)
{
    if(!$payment_details) return __('N/A');
    $id    = uniqid();
    $sleep = $sleep ? asset(IMG_ICO_PATH.$sleep): null;
    $image = $sleep ? '<img src="' .$sleep. '" height="60%" alt=""></img>': __("Not found");

    $html  = '<li style="list-style: none;" class="userbank"><a title="'.__('Bank Details').'" href="#show_bank_html_' . $id . '" data-toggle="modal"><span class="">'.__("Payment Details").'</span></a> </li>';
    $html .= '<div id="show_bank_html_' . $id . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-lg">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('User Payment Details') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body">';
    $html .=
    '<div>
        <pre>
            <p>'.$payment_details.'</p>
        </pre>
        '.$image.'
    </div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function ico_html_form_send($route, $id)
{
    $html = '<li class="deleteuser"><a title="' . __('Accept') . '" href="#htmlFormSending_' . $id . '" data-toggle="modal"><span class=""><i style="font-size: 22px;color: #718a71" class="fa fa-check-circle" aria-hidden="true"></i>
    </span></a> </li>';
    $html .= '<div id="htmlFormSending_' . $id . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Accept') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<form action="' . route($route) . '"method="post" enctype="multipart/form-data">';
    $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
    $html .= '<input type="hidden" name="id" value="' . $id . '" />';
    $html .= '<div class="modal-body">';
    $html .= '<label>' . __('Upload Payment Slip') . '</label>';
    $html .= '<input type="file" name="file" class="form-control-file" required />';
    $html .= '</div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<button class="btn btn-danger" type="submit">' . __('Confirm') . '</button>';
    $html .= '</div>';
    $html .= '</form>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
function icoUserBankShowHtml($payment_method, $slip = null)
{
    if(!$payment_method) return __('N/A');
    $id    = uniqid();
    $slip  = $slip ? asset(IMG_SLEEP_VIEW_PATH.$slip): null;
    $image = $slip ? '<img src="' .$slip. '" height="100%" width="100%" alt=""></img>': "";

    $html  = '<li style="list-style: none;" class="userbank"><a title="'. htmlPaymentMethod($payment_method) .'" href="#show_bank_html_' . $id . '" data-toggle="modal"><span class="">'. htmlPaymentMethod($payment_method) .'</span></a> </li>';
    $html .= '<div id="show_bank_html_' . $id . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-lg">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('User Payment Slip') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body">';
    $html .=
    '<div>
        '.$image.'
    </div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}