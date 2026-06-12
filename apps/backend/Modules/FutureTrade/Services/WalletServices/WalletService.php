<?php

namespace Modules\FutureTrade\Services\WalletServices;

use App\Facades\ResponseFacade;
use App\Model\Coin;
use Illuminate\Database\Eloquent\Collection;
use Modules\FutureTrade\Entities\FutureWallet;
use Modules\FutureTrade\Repositories\WalletRepository\FutureWalletRepository;
use Modules\FutureTrade\Repositories\WalletRepository\IFutureWalletRepository;
use Modules\FutureTrade\Services\CostService\CostService;
use Modules\FutureTrade\Services\CostService\OrderCost;
use Modules\FutureTrade\Services\CostService\PositionCost;
use Modules\FutureTrade\Services\FuturePositionServices\FuturePositionService;
use Modules\FutureTrade\Services\MathService\PositionMath;
use Psr\Http\Message\ResponseInterface;

class WalletService
{
    public FutureWalletRepository $repository;

    public function __construct(IFutureWalletRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Validate Wallet Balance By Amount
     *
     * @throws
     * use rescue() to avoid exception
     */
    public static function validateWalletBalance(int $coin_id, ?int $userID, float $amount): bool
    {
        $wallet = FutureWalletRepository::getWallet($coin_id, $userID);
        if (!$wallet) {
            ResponseFacade::failed('Wallet not found')->safeThrow();
        }

        if ($wallet?->balance < $amount) {
            ResponseFacade::failed('Wallet has not enough balance')->safeThrow();
        }

        return true;
    }

    public function getAvailableBalance(int $coin_id, ?int $user_id = null): string|float|int
    {
        $user_id ??= authId();

        $wallet = FutureWalletRepository::getWallet($coin_id, $user_id);
        if (!$wallet) {
            ResponseFacade::failed('Wallet not found')->safeThrow();
        }

        if (!$wallet?->balance) {
            return 0;
        }

        $costService = new CostService($user_id);
        $total_cost = $costService->getUserCost();

        return bcsubx($wallet->balance, $total_cost);
    }

    public function userWalletDetails(?int $user_id = null)
    {
        $user_id ??= authId();
        $coin = Coin::where('coin_type', 'USDT')->first();
        $wallet = FutureWalletRepository::getWallet($coin->id, $user_id);

        $costService = new CostService($user_id);
        $total_cost = $costService->getUserCost();
        $available_balance = bcsubx($wallet?->balance ?: 0, $total_cost ?: 0, $coin->trade_decimal ?: 8);

        return success([
            'balance' => $wallet->balance,
            'cost' => $total_cost,
            'available_balance' => $available_balance
        ]);
    }

    public function getUserAssets()
    {
        $user = authUser();

        if (!$user) {
            ResponseFacade::failed(__('User not found'))->throw();
        }

        $coin = Coin::where('coin_type', 'USDT')->first();

        if (!$coin) {
            ResponseFacade::failed(__('Coin not found'))->throw();
        }

        $wallet = rescue(fn() => FutureWalletRepository::getWallet($coin->id, $user->id));

        if (!$wallet) {
            ResponseFacade::failed(__('Wallet not found'))->throw();
        }

        $available_balance = $this->getAvailableBalance($coin->id, $user->id);

        $orderService = new OrderCost($user->id);
        $in_order_cost = $orderService->cost()->total_cost ?: 0;

        $returnData = [
            'coin' => $coin->coin_type,
            'total' => trim_num($wallet->balance, 18),
            'available_balance' => trim_num($available_balance, 18),
            'in_order_cost' => trim_num($in_order_cost, 18),
            'btc' => trim_num(convert_currency($wallet->balance, 'BTC', 'USDT'), 18),
            'btc_to_currency' => 'USD'
        ];

        return ResponseFacade::success($returnData)->send();
    }

    public function getWalletDetailsWithBalance(?int $user_id = null)
    {
        $user_id ??= authId();
        $user = authUser();

        if (!$user) {
            ResponseFacade::failed(__('User not found'))->throw();
        }

        $coin = Coin::where('coin_type', 'USDT')->first();

        if (!$coin) {
            ResponseFacade::failed(__('Coin not found'))->throw();
        }

        $wallet = rescue(fn() => FutureWalletRepository::getWallet($coin->id, $user_id));

        if (!$wallet) {
            ResponseFacade::failed(__('Wallet not found'))->throw();
        }

        $available_balance = $this->getAvailableBalance($coin->id, $user_id);
        // $available_balance_usd = convert_currency($available_balance, $coin->coin_type, 'USDT');

        $positionService = app(FuturePositionService::class);
        $openPositions = $positionService->getMyOpenPositions($user_id);
        $today_pnl = $positionService->totalPnlCalculation(
            new Collection($openPositions['data'] ?? [])
        );

        $returnData = [
            'coin_type' => $coin->coin_type,
            'total_balance' => trim_num($wallet->balance, 8),
            'available_balance' => trim_num($available_balance, 8),
            // 'available_balance_usd' => trim_num($available_balance_usd, 6),
            'in_order_balance' => bcsubx($wallet->balance, $available_balance, $coin->trade_decimal ?: 8),
            'today_pnl' => trim_num($today_pnl, 8)
        ];

        return success($returnData);
    }

    public function getUserAllFutureWallet(?int $user_id = null)
    {
        $user_id ??= authId();
        $user = authUser();

        if (!$user) {
            ResponseFacade::failed(__('User not found'))->throw();
        }

        $coin = Coin::where('coin_type', 'USDT')->first();

        if (!$coin) {
            ResponseFacade::failed(__('Coin not found'))->throw();
        }

        $wallet = rescue(fn() => FutureWalletRepository::getWallet($coin->id, $user_id));

        if (!$wallet) {
            ResponseFacade::failed(__('Wallet not found'))->throw();
        }

        // available balance
        $available_balance = $this->getAvailableBalance($coin->id, $user_id);

        $orderService = new OrderCost($user_id);
        $in_order_balance = $orderService->cost()->total_cost ?: 0;

        $positionService = app(FuturePositionService::class);
        $today_pnl = $positionService->totalPnlCalculation(
            new Collection($positionService->getMyOpenPositions($user_id)['data'] ?? [])
        );

        $total_balance = $wallet->balance;
        $available_balance_btc = convert_currency($available_balance, 'BTC', 'USDT');
        $total_balance_btc = convert_currency($total_balance, 'BTC', 'USDT');

        $returnData = [
            'coin_type' => $coin->coin_type,
            'today_pnl' => trim_num($today_pnl, 8),
            'available_balance' => trim_num($available_balance, 8),
            'in_order_balance' => trim_num($in_order_balance, 8),
            'total_balance' => trim_num($total_balance, 8),
            'available_balance_btc' => trim_num($available_balance_btc, 8),
            'total_balance_btc' => trim_num($total_balance_btc, 8),
            'wallet_id' => $wallet->id,
        ];

        return success($returnData);
    }

    public function getTodayPnl(?int $user_id = null)
    {
        $user_id ??= authId();
        $user = authUser();

        if (!$user) {
            ResponseFacade::failed(__('User not found'))->throw();
        }

        $coin = Coin::where('coin_type', 'USDT')->first();

        if (!$coin) {
            ResponseFacade::failed(__('Coin not found'))->throw();
        }

        $positionService = app(FuturePositionService::class);
        $openPositions = $positionService->getMyOpenPositions($user_id);
        $today_pnl = $positionService->totalPnlCalculation(
            new Collection($openPositions['data'] ?? [])
        );

        $returnData = [
            'today_pnl' => $today_pnl,
            'currency' => 'USDT',
        ];

        return success($returnData);
    }

    /**
     * Get combined wallet data with all details, future wallet info, and PnL in one request
     *
     * @param int|null $user_id
     * @return mixed
     */
    public function getCombinedWalletData(?int $user_id = null)
    {
        $user_id ??= authId();
        $user = authUser();

        if (!$user) {
            ResponseFacade::failed(__('User not found'))->throw();
        }

        $coin = Coin::where('coin_type', 'USDT')->first();

        if (!$coin) {
            ResponseFacade::failed(__('Coin not found'))->throw();
        }

        $wallet = rescue(fn() => FutureWalletRepository::getWallet($coin->id, $user_id));

        if (!$wallet) {
            ResponseFacade::failed(__('Wallet not found'))->throw();
        }

        // Get wallet details with balance
        $available_balance = $this->getAvailableBalance($coin->id, $user_id);
        $available_balance_usd = convert_currency($available_balance, $coin->coin_type);

        $wallet_details = [
            'coin_type' => $coin->coin_type,
            'total_balance' => $wallet->balance,
            'available_balance' => $available_balance,
            'available_balance_usd' => $available_balance_usd,
            'locked_balance' => bcsubx($wallet->balance, $available_balance, $coin->trade_decimal ?: 8),
        ];

        // Get all future wallet data
        $orderService = new OrderCost($user_id);
        $in_order_balance = $orderService->cost()->total_cost ?: 0;

        $positionService = app(FuturePositionService::class);
        $openPositions = $positionService->getMyOpenPositions($user_id);
        $today_pnl = $positionService->totalPnlCalculation(
            new Collection($openPositions['data'] ?? [])
        );

        $total_balance = $wallet->balance;
        $available_balance_btc = convert_currency($available_balance, $coin->coin_type);
        $total_balance_btc = convert_currency($total_balance, $coin->coin_type);

        $future_wallet = [
            'coin_type' => $coin->coin_type,
            'today_pnl' => $today_pnl,
            'available_balance' => $available_balance,
            'in_order_balance' => $in_order_balance,
            'total_balance' => $total_balance,
            'available_balance_btc' => $available_balance_btc,
            'total_balance_btc' => $total_balance_btc,
            'wallet_id' => $wallet->id,
        ];

        // Get today PnL
        $pnl_data = [
            'today_pnl' => $today_pnl,
            'currency' => 'USDT',
        ];

        $combinedData = [
            'wallet_details' => $wallet_details,
            'future_wallet' => $future_wallet,
            'pnl' => $pnl_data,
        ];

        return success($combinedData);
    }

    public function getOpenOrderAndPositionCost(?int $user_id = null): array
    {
        $user_id ??= authId();

        $orderCost = new OrderCost($user_id);
        $orderCost->cost();

        $positionCost = new PositionCost($user_id);
        $positionCost->cost();

        return [
            'open_buy_cost' => $orderCost->buy_order_cost ?: 0,
            'open_sell_cost' => $orderCost->sell_order_cost ?: 0,
            'open_position_cost' => $positionCost->total_margin ?: 0,
        ];
    }
}
