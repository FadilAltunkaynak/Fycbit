<?php

namespace Modules\FutureTrade\Repositories\WalletRepository;

use App\Http\Repositories\CommonRepository;
use App\Model\Coin;
use Modules\FutureTrade\Entities\FutureWallet;
use Modules\FutureTrade\Repositories\WalletRepository\IFutureWalletRepository;

class FutureWalletRepository extends CommonRepository implements IFutureWalletRepository
{
    /**
     * Constructor
     *
     * @param  FutureWallet|null  $model
     */
    public function __construct($model = null)
    {
        parent::__construct($model ?: app(FutureWallet::class));
    }

    /**
     * Create New Future Wallet
     */
    public static function createWallet(int $coin_id, ?int $userID): FutureWallet
    {
        $userID ??= authId();
        return FutureWallet::create([
            'coin_id' => $coin_id,
            'user_id' => $userID
        ]);
    }

    /**
     * Get Future Wallet By User ID
     */
    public static function getWallet(int $coin_id, ?int $userID = null): FutureWallet
    {
        $userID ??= authId();
        return FutureWallet::where([
            'coin_id' => $coin_id,
            'user_id' => $userID
        ])->first() ?? self::createWallet(
            coin_id: $coin_id,
            userID : $userID
        );
    }

    /**
     * Lock Balance to in order balance
     * recommended to use DB::beginTransaction() lockForUpdate used
     * use rescue() to avoid exception
     */
    public function lockWalletBalance(Coin $coin, float|string $balance, ?int $user_id = null): bool
    {
        $wallet = FutureWallet::where('user_id', $user_id ?: authId())
            ->where('coin_id', $this->model->coin_id)
            ->lockForUpdate()->first();

        $status = $wallet->decrement('available_balance', $balance) &&
                $wallet->increment('in_order_balance', $balance);

        return $status;
    }

    /**
     * Release Balance from in order balance on cancel
     * recommended to use DB::beginTransaction() lockForUpdate used
     * use rescue() to avoid exception
     */
    public function releaseWalletBalance(Coin $coin, float|string $balance, ?int $user_id = null): bool
    {
        $wallet = FutureWallet::where('user_id', $user_id ?: authId())
            ->where('coin_id', $this->model->coin_id)
            ->lockForUpdate()->first();

        $status = $wallet->decrement('in_order_balance', $balance) &&
                $wallet->increment('available_balance', $balance);

        return $status;
    }

}
