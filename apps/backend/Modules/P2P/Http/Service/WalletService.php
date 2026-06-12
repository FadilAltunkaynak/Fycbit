<?php
namespace Modules\P2P\Http\Service;

use App\Http\Repositories\AdminCoinRepository;
use App\Model\Coin;
use App\Model\Wallet;
use App\User;
use Illuminate\Support\Facades\DB;
use Modules\P2P\Entities\P2PWallet;
use Modules\P2P\Jobs\WalletBalanceTransfer;

class WalletService
{
    public function getWalletList($userId, $paginate = null, $search = null): array
    {
        $wallets = P2PWallet::join('coins', 'coins.id', '=', 'p2p_wallets.coin_id')
            ->join('p_coin_settings', 'p_coin_settings.coin_type', '=', 'coins.coin_type')
            ->where(['p2p_wallets.user_id' => $userId, 'p2p_wallets.type' => PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE, 'p_coin_settings.trade_status' => STATUS_ACTIVE])
            ->when(isset($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->orWhere('p2p_wallets.balance', 'LIKE', '%' . $search . '%')
                        ->orWhere('p2p_wallets.name', 'LIKE', '%' . $search . '%');
                });
            })
            ->orderByDesc('p2p_wallets.balance')
            ->select('p2p_wallets.*', 'coins.coin_icon', 'p_coin_settings.trade_status')
            ->paginate($paginate ?? 10);

        foreach ($wallets as $wallet) {
            $wallet->total_balance_usd = get_coin_usd_value($wallet->total, $wallet->coin_type);
            $wallet->coin_icon = empty($wallet->coin_icon) ? '' : show_image_path($wallet->coin_icon, 'coin/');
        }

        return responseData(true, __("Wallet get successfully"), $wallets);
    }

    public function walletDetails($request)
    {
        try {
            $userId = authUserId_p2p();
            if ($wallet = Wallet::where(['coin_type' => $request->coin_type, 'user_id' => $userId])->first())
                return responseData(true, __("Wallet get successfully"), ['wallet' => $wallet]);
            return responseData(false, __("Wallet not found"));
        } catch (\Exception $e) {
            storeException('getWalletList', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function walletBlanceTransfer($userId, $request)
    {
        try {
            if ($request->type == WALLET_BALANCE_TRANSFER_RECEIVE) {
                $wallet = Wallet::where(["user_id" => $userId, "coin_type" => $request->coin])->first();
                if ($wallet) {
                    if ($wallet->balance >= $request->amount) {
                        WalletBalanceTransfer::dispatch($wallet, $request->amount, $request->type, $request->coin, $userId);
                        return responseData(true, __("Balance transferd successfully"));
                    }
                    return responseData(false, __("Your fund wallet do not have sufficient balance"));
                }
                return responseData(false, __("Fund Wallet not found"));
            }
            if ($request->type == WALLET_BALANCE_TRANSFER_SEND) {
                $wallet = P2PWallet::where(["user_id" => $userId, "coin_type" => $request->coin])->first();
                if ($wallet) {
                    if ($wallet->balance >= $request->amount) {
                        WalletBalanceTransfer::dispatch($wallet, $request->amount, $request->type, $request->coin, $userId);
                        return responseData(true, __("Balance transferd successfully"));
                    }
                    return responseData(false, __("Your P2P Wallet do not have sufficient balance"));
                }
                return responseData(false, __("P2P Wallet not found"));
            }
            return responseData(false, __("Wallet Balance transfer successfully"));
        } catch (\Exception $e) {
            storeException('walletBlanceTransfer', $e->getMessage());
            return responseData(false, __("Something went wrong"));
        }
    }

    public function walletInfo(int $userID, Coin|int|string $coin): P2PWallet
    {
        $coinInfo = (new AdminCoinRepository)->coinInfo($coin);
        return P2PWallet::firstOrCreate(['user_id' => $userID, 'coin_id' => $coinInfo->id], [
            'name' => $coinInfo->coin_type . ' wallet',
            'coin_type' => $coinInfo->coin_type
        ]);
    }

    public function bulkWalletGenerate($id, $type)
    {
        if ($type == WALLET_GENERATE_BY_COIN) {
            $coin = Coin::find($id);
            $users = User::where(['status' => STATUS_ACTIVE, 'super_admin' => STATUS_INACTIVE])->get();

            foreach ($users as $user) {
                DB::beginTransaction();
                try {
                    $this->walletInfo($user->id, $coin);
                    DB::commit();
                } catch (\Exception $e) {
                    storeLog(processExceptionMsg($e));
                    DB::rollBack();
                }
            }
        } else if ($type == WALLET_GENERATE_BY_USER) {
            $user = User::find($id);
            $coins = Coin::where('status', STATUS_ACTIVE)->get();

            foreach ($coins as $coin) {
                DB::beginTransaction();
                try {
                    $this->walletInfo($user->id, $coin);
                    DB::commit();
                } catch (\Exception $e) {
                    storeLog(processExceptionMsg($e));
                    DB::rollBack();
                }
            }
        }
        return responseData(true, __('Wallet generated successfully'));
    }
}