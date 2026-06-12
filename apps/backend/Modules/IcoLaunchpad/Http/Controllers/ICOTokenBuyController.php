<?php

namespace Modules\IcoLaunchpad\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;
use Modules\IcoLaunchpad\Http\Services\IcoTokenBuyService;

class ICOTokenBuyController extends Controller
{
    private $tokenService;
    public function __construct()
    {
        $this->tokenService = new IcoTokenBuyService();
    }
    public function tokenList()
    {
        return view('icolaunchpad::ico.token.token-list');
    }

    public function getPendingList(Request $request)
    {
        try {
            if ($request->ajax()) {
                $withdrawal_list = TokenBuyHistory::where('status', STATUS_PENDING)->orderBy('token_buy_histories.id', 'desc');

                return datatables()->of($withdrawal_list)
                    ->addColumn('user', function ($query) {
                        return isset($query->user) ? $query->user->first_name . ' ' . $query->user->last_name : 'N/A';
                    })
                    //                    ->addColumn('bank', function ($query) {
                    //                        return isset($query->bank)?$query->bank->bank_name:'N/A';
                    //                    })
                    ->addColumn('created_at', function ($query) {
                        return $query->created_at;
                    })
                    //                    ->addColumn('currency_amount', function ($query) {
                    //                        return $query->currency_amount.' '.$query->currency;
                    //                    })
                    ->addColumn('amount', function ($query) {
                        return $query->amount . ' ' . $query->coin->coin_type;
                    })
                    ->addColumn('paid', function ($query) {
                        return $query->pay_amount . ' ' . $query->pay_currency;
                    })
                    ->addColumn('payment', function ($query) {
                        if($query->payment_method == BANK_DEPOSIT){
                            $action  = '<div class="activity-icon"><ul>';
                            $action .= icoUserBankShowHtml($query->payment_method, $query->bank_slip);
                            $action .= '</ul> </div>';
                            return $action;
                        }
                        return htmlPaymentMethod($query->payment_method);
                    })
                    ->addColumn('status', function ($query) {
                        return deposit_status_text($query->status);
                    })
                    ->addColumn('actions', function ($query) {
                        $action = '<div class="activity-icon"><ul>';
                        $action .= accept_html('buyTokenAccept', encrypt($query->id));
                        $action .= reject_html('icoBuyTokenReject', encrypt($query->id));
                        $action .= '</ul> </div>';

                        return $action;
                    })
                    ->rawColumns(['actions', 'fees','payment'])
                    ->make(true);
            }
        } catch (\Exception $e) {
        }
    }

    public function getActiveList(Request $request)
    {
        try {
            if ($request->ajax()) {
                $withdrawal_list = TokenBuyHistory::where('status', STATUS_ACTIVE)->orderBy('token_buy_histories.id', 'desc');

                return datatables()->of($withdrawal_list)
                    ->addColumn('user', function ($query) {
                        return isset($query->user) ? $query->user->first_name . ' ' . $query->user->last_name : 'N/A';
                    })
                    ->addColumn('created_at', function ($query) {
                        return $query->created_at;
                    })
                    ->addColumn('amount', function ($query) {
                        return $query->amount . ' ' . $query->coin->coin_type;
                    })
                    ->addColumn('paid', function ($query) {
                        return $query->pay_amount . ' ' . $query->pay_currency;
                    })
                    ->addColumn('payment', function ($query) {
                        return htmlPaymentMethod($query->payment_method);
                    })
                    ->addColumn('status', function ($query) {
                        return deposit_status_text($query->status);
                    })
                    ->make(true);
            }
        } catch (\Exception $e) {
        }
    }

    public function getRejectedList(Request $request)
    {
        try {
            if ($request->ajax()) {
                $withdrawal_list = TokenBuyHistory::where('status', STATUS_REJECTED)->orderBy('token_buy_histories.id', 'desc');

                return datatables()->of($withdrawal_list)
                    ->addColumn('user', function ($query) {
                        return isset($query->user) ? $query->user->first_name . ' ' . $query->user->last_name : 'N/A';
                    })
                    ->addColumn('created_at', function ($query) {
                        return $query->created_at;
                    })
                    ->addColumn('amount', function ($query) {
                        return $query->amount . ' ' . $query->coin->coin_type;
                    })
                    ->addColumn('paid', function ($query) {
                        return $query->pay_amount . ' ' . $query->pay_currency;
                    })
                    ->addColumn('payment', function ($query) {
                        return htmlPaymentMethod($query->payment_method);
                    })
                    ->addColumn('status', function ($query) {
                        return deposit_status_text($query->status);
                    })
                    ->make(true);
            }
        } catch (\Exception $e) {
        }
    }

    public function acceptToken($id)
    {
        try {
            $response = $this->tokenService->tokenBuyRequestAccept($id);
            return redirect()->back()->with($response['success'] ? 'success' : 'dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('acceptTokenico:', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function rejectToken($id)
    {
        try {
            $response = $this->tokenService->tokenBuyRequestReject($id);
            return redirect()->back()->with($response['success'] ? 'success' : 'dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('rejectTokenIco:', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }
}
