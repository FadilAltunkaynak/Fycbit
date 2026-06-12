<?php

namespace App\Http\Controllers\admin;

use App\Enums\CoinProvider;
use App\Enums\YesNoStatus2;
use App\Exceptions\InvalidRequestException;
use App\Facades\ResponseFacade;
use App\Http\Repositories\AffiliateRepository;
use App\Http\Requests\Admin\CoinRequest;
use App\Http\Requests\Admin\CoinSaveRequest;
use App\Http\Requests\Admin\CoinSettingRequest;
use App\Http\Requests\Admin\GiveCoinRequest;
use App\Http\Requests\Admin\WebhookRequest;
use App\Http\Requests\UpdateWalletKeyRequest;
use App\Http\Services\CoinPaymentsAPI;
use App\Http\Services\CoinService;
use App\Http\Services\CoinSettingService;
use App\Http\Services\CurrencyService;
use App\Http\Services\Logger;
use App\Jobs\AdjustWalletJob;
use App\Jobs\BulkWalletGenerateJob;
use App\Jobs\NewCoinCreateJob;
use App\Model\AdminGiveCoinHistory;
use App\Model\BuyCoinHistory;
use App\Model\Coin;
use App\Model\CurrencyList;
use App\Model\Wallet;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Repositories\CoinSettingRepository;
use App\Http\Services\DataTable\CoinDataTableService;
use App\Http\Services\ERC20TokenApi;
use App\Http\Services\WalletService;
use App\Model\CoinSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;


class CoinController extends Controller
{
    private CoinService $coinService;
    private $coinSettingService;
    public function __construct()
    {
        $this->coinService = new CoinService();
        $this->coinSettingService = new CoinSettingService();
    }


    // all coin list
    public function adminCoinList(Request $request)
    {
        $check_module = Module::allEnabled();

        if ($request->ajax())
            return (new CoinDataTableService)->getData($check_module);

        $data = [
            'module' => $check_module,
            'title' => __('Currency List')
        ];
        return view('admin.coin_management.crypto_currency.coin', $data);
    }

    // change coin status
    public function adminCoinStatus(Request $request)
    {
        $coin = Coin::find($request->active_id);
        if ($coin) {
            if ($coin->status == STATUS_ACTIVE) {
                $coin->update(['status' => STATUS_INACTIVE]);
            } else {
                $coin->update(['status' => STATUS_ACTIVE]);
            }
            return response()->json(['message' => __('Status changed successfully')]);
        } else {
            return response()->json(['message' => __('Coin not found')]);
        }
    }

    // edit coin
    public function adminCoinEdit($id)
    {
        $coinId = decryptId($id);

        if (is_array($coinId))
            return failed(__('Coin not found'));

        $data = [
            'module' => Module::allEnabled(),
            'item' => Coin::find($coinId),
            'providers' => CoinProvider::getList(),
            'adminApprovalStatus' => YesNoStatus2::getList(),
            'title' => __('Update Coin'),
            'button_title' => __('Update')
        ];
        return view('admin.coin_management.crypto_currency.edit_coin', $data);
    }


    //    coin save process
    public function adminCoinUpdateProcess(CoinRequest $request)
    {
        $coinId = decryptId($request->coin_id);
        if (is_array($coinId))
            return failed(__('Coin not found'));

        $response = $this->coinService->updateCoin($request, $coinId);

        return ResponseFacade::result($response)->send();
    }

    // add coin page
    public function adminAddCoin()
    {
        $data = [
            'title' => __('Add New Coin'),
            'button_title' => __('Save'),
            'currency' => CurrencyList::where('status', STATUS_ACTIVE)->get(),
            'providers' => CoinProvider::getList(),
        ];
        return view('admin.coin_management.crypto_currency.add_coin', $data);
    }

    // admin new coin save process
    public function adminSaveCoin(CoinSaveRequest $request): RedirectResponse
    {
        $response = $this->coinService->addNewCoin($request);
        return ResponseFacade::redirect_next('adminCoinList')->result($response)->send();
    }

    // edit coin settings
    public function adminCoinSettings($id)
    {
        return $this->handlerResponseAndRedirect(function () use ($id) {
            $coinId = decryptId($id);
            if (is_array($coinId))
                throw new InvalidRequestException(__('Invalid coin'));

            $coinSetting = $this->coinSettingService->getCoinSettings($coinId);

            $data = array_merge([
                'item' => $coinSetting,
                'title' => __('Update Coin Setting'),
                'button_title' => __('Update Setting')
            ]);

            if ($coinSetting->network == COIN_PAYMENT) {
                $data['redirectUrl'] = route('adminCoinApiSettings', ['tab' => 'payment']);
            } else {
                $data['redirectView'] = view('admin.coin-order.edit_coin_settings');
            }

            return $this->responseData(true, '', $data);
        });
    }

    // admin save coin setting
    public function adminSaveCoinSetting(CoinSettingRequest $request)
    {
        return $this->handlerResponseAndRedirect(function () use ($request) {
            return $this->coinSettingService->updateCoinSetting($request);
        });
    }

    // admin bitgo wallet adjust
    public function adminAdjustBitgoWallet($id)
    {

        try {
            $coinId = decryptId($id);
            if (is_array($coinId)) {
                return redirect()->back()->with(['dismiss' => __('Coin not found')]);
            }
            $response = $this->coinSettingService->adjustBitgoWallet($coinId);
            if ($response['success'] == true) {
                return redirect()->back()->with('success', $response['message']);
            } else {
                return redirect()->back()->with('dismiss', $response['message']);
            }
        } catch (\Exception $e) {
            storeException('adminAdjustBitgoWallet', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    public function adminCoinRate()
    {

        $currency = new CurrencyService();
        $response = $currency->updateCoinRate();
        if ($response["success"])
            return redirect()->back()->with("success", $response["message"]);
        return redirect()->back()->with("dismiss", $response["message"]);
    }

    // admin coin delete
    public function adminCoinDelete($id)
    {

        try {
            $coinId = decryptId($id);
            if (is_array($coinId)) {
                return redirect()->back()->with(['dismiss' => __('Currency not found')]);
            }
            $response = $this->coinService->adminCoinDeleteProcess($coinId);
            if ($response['success'] == true) {
                return redirect()->back()->with('success', $response['message']);
            } else {
                return redirect()->back()->with('dismiss', $response['message']);
            }
        } catch (\Exception $e) {
            storeException('adminCoinDelete', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    // admin user coin
    public function adminUserCoinList()
    {
        $data['title'] = __('User Total Currency Amount');
        $data['items'] = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where(['coins.status' => STATUS_ACTIVE])
            ->selectRaw('sum(wallets.balance) as total_balance, coins.coin_type, coins.name')
            ->groupBy('coins.id')
            ->get();

        return view('admin.coin_management.crypto_currency.user_coin', $data);
    }

    public function webhookSave(WebhookRequest $request)
    {
        $response = $this->coinService->webhookSaveProcess($request);
        return ResponseFacade::result($response)->send();
    }

    public function check_wallet_address(Request $request)
    {
        return $this->handlerApiResponse(function () use ($request) {
            return (new WalletService)->checkWalletAddress($request);
        });
    }

    public function coinMakeListed($id)
    {
        $response = $this->coinService->makeTokenListedToCoin(decrypt($id));
        if ($response['success']) {
            return back()->with(['success' => $response['message']]);
        } else {
            return back()->with(['dismiss' => $response['message']]);
        }
    }

    public function updateWalletKey(UpdateWalletKeyRequest $request)
    {
        return $this->handlerResponseAndRedirect(function () use ($request) {
            return $this->coinService->updateWalletKey($request, auth()->user());
        });
    }

    public function viewWalletKey(Request $request)
    {
        return $this->handlerApiResponse(function () use ($request) {
            return $this->coinService->viewWalletKey($request, auth()->user());
        });
    }

    public function demoTradeCoinStatus($coin_type)
    {
        try {
            if ($coin = Coin::whereCoinType($coin_type)->first()) {
                if ($coin->update(['is_demo_trade' => (!$coin->is_demo_trade)]))
                    return response()->json(responseData(true, __("Coin status updated")));
                return response()->json(responseData(false, __("Coin not updated")));
            }
            return response()->json(responseData(false, __("Coin not found")));
        } catch (\Exception $e) {
            storeException('demoTradeCoinStatus', $e->getMessage());
            return response()->json(responseData(false, __("Something went wrong")));
        }
    }
}
