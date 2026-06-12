<?php

namespace App\Http\Controllers\admin;

use App\Enums\CoinProvider;
use App\Enums\DepositCollectionStatus;
use App\Enums\DepositeStatus;
use App\Enums\NetworkBase;
use App\Facades\ResponseFacade;
use App\Http\Services\DataTable\DepositHistoryDataTableService;
use App\Model\Coin;
use App\Model\Network;
use App\Model\CoinNetwork;
use Illuminate\Http\Request;
use App\Model\AffiliationHistory;
use App\Model\DepositeTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Services\WalletService;
use App\Jobs\TokenReceiveToAdminJob;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\DepositService;
use App\Jobs\PendingDepositAcceptJob;
use App\Jobs\PendingDepositRejectJob;
use Illuminate\Support\Facades\Schema;
use App\Http\Services\BitgoWalletService;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\DepositRequest;
use App\Http\Services\Evm\EvmWalletService;
use App\Http\Repositories\CustomTokenRepository;
use App\Model\EstimateGasFeesTransactionHistory;
use App\Model\AdminReceiveTokenTransactionHistory;
use Modules\IcoLaunchpad\Entities\TokenBuyHistory;

class DepositController extends Controller
{
    // gas send history
    public function adminGasSendHistory(Request $request)
    {
        if ($request->ajax())
            return (new DepositHistoryDataTableService())->adminGasSendHistory();

        $data['title'] = __('Admin Estimate Gas Sent History');
        return view('admin.transaction.deposit.gas_sent_history', $data);
    }

    // token receive history
    public function adminTokenReceiveHistory(Request $request)
    {
        if ($request->ajax())
            return (new DepositHistoryDataTableService())->adminTokenReceiveHistory();

        $data['title'] = __('Admin Token Receive History');
        return view('admin.transaction.deposit.token_receive_history', $data);
    }

    // token pending deposit history
    public function adminPendingDepositHistory(Request $request)
    {
        if ($request->ajax())
            return (new DepositHistoryDataTableService())->adminPendingDepositHistory();

        $data = [
            'buy_token' => Schema::hasTable('token_buy_histories'),
            'title' => __('Pending Token Deposit History')
        ];
        return view('admin.transaction.deposit.token_pending_deposit_history', $data);
    }

    // pending deposit reject process
    public function adminPendingDepositReject($id)
    {
        if (isset($id)) {
            try {
                $wdrl_id = decrypt($id);
            } catch (\Exception $e) {
                return redirect()->back();
            }
            $transaction = DepositeTransaction::where(['id' => $wdrl_id, 'status' => STATUS_PENDING, 'address_type' => ADDRESS_TYPE_EXTERNAL])->first();

            if (!empty($transaction)) {
                dispatch(new PendingDepositRejectJob($transaction, Auth::id()))->onQueue('deposit');
                return redirect()->back()->with('success', __('Pending deposit reject process goes to queue. Please wait sometimes'));
            } else {
                return redirect()->back()->with('dismiss', __('Pending deposit not found'));
            }
        }
    }

    // pending deposit accept process
    public function adminPendingDepositAccept($id)
    {
        $response = (new DepositService())->transferPendingDepositUserToSystemWallet($id);
        return ResponseFacade::result($response)->send();
    }

    public function adminWalletNetworks(string $coinType)
    {
        $response = (new WalletService)->userWalletNetwork($coinType, DEPOSIT);
        return ResponseFacade::result($response)->send();
    }

    public function adminCheckDeposit()
    {
        $data = [
            'title' => __('Check Deposit'),
            // 'networks' => Network::whereStatus(STATUS_ACTIVE)->get(),
            'coins' => Coin::where(['active_provider' => CoinProvider::CUSTOM_RPC_NODE->value, 'status' => STATUS_ACTIVE])->get()
        ];
        return view('admin.transaction.deposit.check-deposit', $data);
    }

    public function submitCheckDeposit(DepositRequest $request)
    {
        $service = new DepositService();
        $response = $service->checkDepositByHash($request);
        if (!is_success($response))
            return ResponseFacade::result($response)->send();

        if ($request->type != CHECK_DEPOSIT)
            return ResponseFacade::result($response)->send();

        $data = [
            ...$response['data'],
            'type' => $request->type,
            'title' => __('Transaction Details'),
            'coins' => Coin::where(['status' => STATUS_ACTIVE])->get(),
        ];
        return view('admin.transaction.deposit.check-deposit', $data);


        // $data = $response['data'];
        // $data['network'] = $request->network;
        // $data['coin_network'] = $request->coin_network;
        // $data['transaction_id'] = $request->transaction_id;
        // $data['type'] = $request->type;
        // $data['title'] = __('Transaction Details');
        // $data['coins'] = Coin::where(['status' => STATUS_ACTIVE])->get();
        // if ($request->type == CHECK_DEPOSIT) {
        //     $data = array_merge($response['data'], [
        //         'coin_id' => $request->coin_id,
        //         'network_id' => $request->network_id,
        //         'txId' => $request->trx_id,
        //         'type' => $request->type,
        //         'title' => __('Transaction Details'),
        //         'coins' => Coin::where('status', STATUS_ACTIVE)->get()
        //     ]);
        //     $data['redirectView'] = view('admin.transaction.deposit.check-deposit');
        //     return $this->responseData(true, $response['message'], $data);
        // }
        // return $this->responseData(true, $response['message']);
    }

    public function icoTokenBuyListAccept()
    {
        $tokenBuyHistories = DB::table('token_buy_histories')->where('token_buy_histories.status', STATUS_ACCEPTED)
            ->join('coins', 'token_buy_histories.coin_id', '=', 'coins.id')
            ->join('ico_tokens', 'token_buy_histories.token_id', '=', 'ico_tokens.id')
            ->join('wallet_address_histories', 'token_buy_histories.wallet_id', '=', 'wallet_address_histories.wallet_id')
            ->where('coins.is_listed', STATUS_ACTIVE)
            ->where('token_buy_histories.is_admin_receive', STATUS_PENDING)
            ->select(
                'token_buy_histories.*',
                'coins.coin_type as coin_type',
                'ico_tokens.wallet_address as from_address',
                'wallet_address_histories.address as address',
                'token_buy_histories.blockchain_tx as transaction_id'
            )->get();

        return datatables()->of($tokenBuyHistories)
            ->addColumn('created_at', function ($item) {
                return $item->created_at;
            })
            ->addColumn('status', function ($item) {
                return DepositeStatus::tryFrom($item->status)->statusHtml();
            })
            ->addColumn('actions', function ($wdrl) {
                $action = '<ul>';
                $action .= accept_html('adminReceiveBuyTokenAmount', encrypt($wdrl->id));
                $action .= '<ul>';

                return $action;
            })
            ->rawColumns(['actions', 'status'])
            ->make(true);
    }

    // admin token receive process
    public function adminReceiveBuyTokenAmount($id)
    {
        if (isset($id)) {
            try {
                $wdrl_id = decrypt($id);
            } catch (\Exception $e) {
                return redirect()->back();
            }
            $transactions = DB::table('token_buy_histories')->where('token_buy_histories.status', STATUS_ACCEPTED)
                ->where('token_buy_histories.is_admin_receive', STATUS_PENDING)
                ->join('coins', 'token_buy_histories.coin_id', '=', 'coins.id')
                ->join('ico_tokens', 'token_buy_histories.token_id', '=', 'ico_tokens.id')
                ->join('wallet_address_histories', 'token_buy_histories.wallet_id', '=', 'wallet_address_histories.wallet_id')
                ->where('coins.is_listed', STATUS_ACTIVE)
                ->select(
                    'token_buy_histories.*',
                    'coins.coin_type as coin_type',
                    'ico_tokens.wallet_address as from_address',
                    'wallet_address_histories.address as address',
                    'token_buy_histories.blockchain_tx as transaction_id'
                )
                ->first();

            if (!empty($transactions)) {
                dispatch(new TokenReceiveToAdminJob($transactions, Auth::id()))->onQueue('deposit');
                return redirect()->back()->with('success', __("Token accept to admin address, process goes to queue. Please wait sometimes, don't click multiple"));
            } else {
                return redirect()->back()->with('dismiss', __('Data not found'));
            }
        }
    }

    public function getCurrencyByNetworkID($network_id)
    {
        if (!($network = Network::find($network_id)))
            return response()->json(responseData(false, __("Network not found")));

        if (!($currency = CoinNetwork::with("coin")->where("network_id", $network_id)->get()))
            return response()->json(responseData(false, __("currency not found")));

        return response()->json(responseData(true, __("Currency found successfully"), $currency));
    }

}
