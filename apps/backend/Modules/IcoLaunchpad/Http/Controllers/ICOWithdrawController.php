<?php

namespace Modules\IcoLaunchpad\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IcoLaunchpad\Http\Services\ICOWithdrawService;
use Modules\IcoLaunchpad\Entities\TokenWithdrawTransaction;

class ICOWithdrawController extends Controller
{
    private  $services;
    public function __construct()
    {
        $this->services = new ICOWithdrawService();
    }
    public function getICOWithdrawhistoryList(Request $request)
    {
        $data['title'] = __('Withdraw History');

        return view('icolaunchpad::ico.withdraw.list', $data);
    }

    public function getICOWithdrawPendingList(Request $request)
    {
        try {
            if ($request->ajax()) {
                $withdrawal_list = TokenWithdrawTransaction::where('approved_status', STATUS_PENDING)->orderBy('id', 'desc');

                return datatables()->of($withdrawal_list)
                    ->addColumn('user', function ($query) {
                        return isset($query->user) ? $query->user->first_name . ' ' . $query->user->last_name : 'N/A';
                    })
                    ->addColumn('created_at', function ($query) {
                        return $query->created_at;
                    })
                    ->addColumn('request_amount', function ($query) {
                        return $query->request_amount . ' ' . $query->request_currency;
                    })
                    ->addColumn('convert_amount', function ($query) {
                        return $query->convert_amount . ' ' . $query->convert_currency;
                    })
                    ->addColumn('tran_type', function ($query) {
                        return getCurrencyType($query->tran_type);
                    })
                    ->addColumn('payment_details', function ($query) {
                        return icoBankShowHtml($query->payment_details);
                    })
                    ->addColumn('actions', function ($query) {
                        $action = '<div class="activity-icon"><ul>';
                        $action .= ($query->tran_type == FIAT_TYPE)
                                    ? ico_html_form_send("IcoFiatWithdrawAcceptRequest", $query->id)
                                    : accept_html('IcoWithdrawAcceptRequest', encrypt($query->id));
                        $action .= reject_html('IcoWithdrawRejectRequest', encrypt($query->id));
                        $action .= '</ul> </div>';

                        return $action;
                    })
                    ->rawColumns(['actions','payment_details'])
                    ->make(true);
            }
        } catch (\Exception $e) {
        }
    }

    public function getICOWithdrawAcceptList(Request $request)
    {
        try {
            if ($request->ajax()) {
                $withdrawal_list = TokenWithdrawTransaction::where('approved_status', STATUS_ACCEPTED)->orderBy('id', 'desc');

                return datatables()->of($withdrawal_list)
                    ->addColumn('user', function ($query) {
                        return isset($query->user) ? $query->user->first_name . ' ' . $query->user->last_name : 'N/A';
                    })
                    ->addColumn('created_at', function ($query) {
                        return $query->created_at;
                    })
                    ->addColumn('request_amount', function ($query) {
                        return $query->request_amount . ' ' . $query->request_currency;
                    })
                    ->addColumn('convert_amount', function ($query) {
                        return $query->convert_amount . ' ' . $query->convert_currency;
                    })
                    ->addColumn('tran_type', function ($query) {
                        return getCurrencyType($query->tran_type);
                    })
                    ->addColumn('payment_details', function ($query) {
                        return icoBankShowHtml($query->payment_details, $query->tran_type == FIAT_TYPE ? $query->payment_sleep: null);
                    })
                    ->rawColumns(['payment_details'])
                    ->make(true);
            }
        } catch (\Exception $e) {
        }
    }

    public function getICOWithdrawRejectList(Request $request)
    {
        try {
            if ($request->ajax()) {
                $withdrawal_list = TokenWithdrawTransaction::where('approved_status', STATUS_ACCEPTED)->orderBy('id', 'desc');

                return datatables()->of($withdrawal_list)
                    ->addColumn('user', function ($query) {
                        return isset($query->user) ? $query->user->first_name . ' ' . $query->user->last_name : 'N/A';
                    })
                    ->addColumn('created_at', function ($query) {
                        return $query->created_at;
                    })
                    ->addColumn('request_amount', function ($query) {
                        return $query->request_amount . ' ' . $query->request_currency;
                    })
                    ->addColumn('convert_amount', function ($query) {
                        return $query->convert_amount . ' ' . $query->convert_currency;
                    })
                    ->addColumn('tran_type', function ($query) {
                        return getCurrencyType($query->tran_type);
                    })
                    ->addColumn('payment_details', function ($query) {
                        return icoBankShowHtml($query->payment_details);
                    })
                    ->rawColumns(['payment_details'])
                    ->make(true);
            }
        } catch (\Exception $e) {
        }
    }

    public function IcoWithdrawAcceptRequest($id)
    {
        try {
            $response = $this->services->IcoWithdrawAcceptRequest($id);
            if ($response['success'])
                return redirect()->back()->with('success', $response['message']);
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('IcoWithdrawAcceptRequest', $e->getMessage());
        }
    }
    public function IcoFiatWithdrawAcceptRequest(Request $request)
    {
        try {
            $response = $this->services->IcoFiatWithdrawAcceptRequest($request);
            if ($response['success'])
                return redirect()->back()->with('success', $response['message']);
            return redirect()->back()->with('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('IcoFiatWithdrawAcceptRequest', $e->getMessage());
        }
    }

    public function IcoWithdrawRejectRequest($id)
    {
        try {
            $response = $this->services->IcoWithdrawRejectRequest($id);
            if ($response['success'])
                return redirect()->back()->withdraw('success', $response['message']);
            return redirect()->back()->withdraw('dismiss', $response['message']);
        } catch (\Exception $e) {
            storeException('IcoWithdrawRejectRequest', $e->getMessage());
        }
    }
}
