<?php

namespace Modules\FutureTrade\Services\LeverageSettingService;

use App\Facades\ResponseFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\FutureTrade\DataObject\FutureLeverageSettingData;
use Modules\FutureTrade\Emum\MarginModeEnum;
use Modules\FutureTrade\Emum\OrderStatusEnum;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Emum\PositionStatusEnum;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FutureLeverageSetting;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Entities\FutureUserSetting;
use Modules\FutureTrade\Http\Requests\Api\FutureLeverageSettingUpdateRequest;
use Modules\FutureTrade\Http\Requests\Api\FutureMarginModeUpdateRequest;
use Modules\FutureTrade\Http\Requests\FutureLeverageSettingsRequest;
use Modules\FutureTrade\Repositories\CoinPairRepository\CoinPairRepository;
use Modules\FutureTrade\Repositories\PositionRepository\PositionRepository;
use Modules\FutureTrade\Repositories\WalletRepository\FutureWalletRepository;
use Modules\FutureTrade\Services\CostService\CostService;
use Modules\FutureTrade\Services\LeverageSettingService\Traits\LeverageValidation;
use Modules\FutureTrade\Services\MathService\PositionMath;
use Modules\FutureTrade\Services\WalletServices\WalletService;

class LeverageSettingService
{
    use LeverageValidation;

    public function leverageSettingSave(FutureLeverageSettingsRequest $request)
    {
        $leverageSetting = null;
        $coin_pair = CoinPairRepository::getCoinPairByUid($request->coin_pair_uid);
        if (!$coin_pair) {
            return failed(__('Coin pair not found'));
        }

        $leverageData = $this->getLeverageDataObject($request);

        $this->leverageSettingMaxMaintenanceAmountCheck(
            maintenance_amount: $leverageData->maintenance_amount,
            min_position_amount: $leverageData->min_position_amount,
            maintenance_margin_rate: $leverageData->maintenance_margin_rate,
        );

        !$request?->leverage_setting_id && $this->leverageSettingRangeDuplicateCheck(
            coin_pair_uid: $leverageData->coin_pair_uid,
            min_position_amount: $leverageData->min_position_amount,
            max_position_amount: $leverageData->max_position_amount,
        );

        $this->leverageSettingMinPositionAmountCheck(
            coin_pair_uid: $leverageData->coin_pair_uid,
            min_position_amount: $leverageData->min_position_amount,
            uid: $request?->leverage_setting_id ?? 0
        );

        $this->leverageSettingNewLeverageCheck(
            coin_pair_uid: $leverageData->coin_pair_uid,
            max_leverage: $leverageData->max_leverage,
            min_position_amount: $leverageData->min_position_amount
        );

        if (isset($request?->leverage_setting_id)) {
            $leverageSetting = self::getLeverageSettingByUid($request->coin_pair_uid, $request?->leverage_setting_id);
            if (!$leverageSetting) {
                return failed(__('Leverage setting not found'));
            }
        } else {
            $leverageData->setProperty('uid', Str::uuid()->getHex());
        }

        $response = match (true) {
            (bool) $leverageSetting => $this->updateLeverageSetting($leverageSetting, $leverageData),
            default => $this->insertLeverageSetting($leverageData)
        };

        if ($response) {
            return success(__('Leverage setting saved successfully.'));
        }

        return failed(__('Leverage setting failed to save.'));
    }

    public function leverageSettingDelete(string $coin_pair_uid, string $uid)
    {
        $leverageSetting = self::getLeverageSettingByUid($coin_pair_uid, $uid);
        if (!$leverageSetting) {
            return failed(__('Leverage setting not found'));
        }

        $hasOpenPositions = FuturePosition::query()
            ->where('future_coin_pair_uid', $coin_pair_uid)
            ->where('status', PositionStatusEnum::OPEN->value)
            ->whereRaw('ABS(amount) BETWEEN ? AND ?', [
                $leverageSetting->min_position_amount,
                $leverageSetting->max_position_amount,
            ])->exists();

        if ($hasOpenPositions) {
            return failed(__('Leverage setting in use by open positions'));
        }

        $delete = $leverageSetting->delete();
        if (!$delete) {
            return failed(__('Leverage setting failed to delete.'));
        }

        return success(__('Leverage setting deleted successfully.'));
    }

    public static function getLeverageSettingByUid($coin_pair_uid, $uid): ?FutureLeverageSetting
    {
        return FutureLeverageSetting::where([
            'coin_pair_uid' => $coin_pair_uid,
            'uid' => $uid,
        ])->first();
    }

    public static function getLeverageByAmount($coin_pair_uid, $amount): ?FutureLeverageSetting
    {
        return FutureLeverageSetting::where('coin_pair_uid', $coin_pair_uid)
            ->where('min_position_amount', '<=', $amount)
            ->where('max_position_amount', '>=', $amount)
            ->first();
    }

    public function getLeverageDataObject(FutureLeverageSettingsRequest $request)
    {
        return new FutureLeverageSettingData(
            $request->coin_pair_uid,
            $request->min_position_amount,
            $request->max_position_amount,
            $request->max_leverage,
            $request->maintenance_margin_rate,
            $request->maintenance_amount
        );
    }

    public function insertLeverageSetting(FutureLeverageSettingData $data): ?FutureLeverageSetting
    {
        try {
            return FutureLeverageSetting::create($data->toArray());
        } catch (\Throwable $th) {
            storeException('futureCreateLeverageSetting', $th->getLine());
            storeException('futureCreateLeverageSetting', $th->getMessage());

            return null;
        }
    }

    public function updateLeverageSetting(FutureLeverageSetting $LeverageSetting, FutureLeverageSettingData $data): ?FutureLeverageSetting
    {
        try {
            $update = $LeverageSetting->update($data->toArray());
            if ($update) {
                return $LeverageSetting;
            }

            return null;
        } catch (\Throwable $th) {
            storeException('futureUpdateLeverageSetting', $th->getLine());
            storeException('futureUpdateLeverageSetting', $th->getMessage());

            return null;
        }
    }

    public function getMaxLeverage(Request $request): array
    {
        $coinPair = CoinPairRepository::getCoinPairByUid($request->coin_pair_uid ?? '');
        if (!$coinPair) {
            return failed(__('Coin pair not found'));
        }

        $leverage = FutureLeverageSetting::maxLeverage($coinPair->uid);

        if(is_numeric($leverage) && $leverage > 0){
            return success(__('Maximum leverage found successfully'), [
                'max_leverage' => $leverage,
            ]);
        }

        return failed(__('Maximum leverage not found'));
    }

    public static function getUserFutureSetting(int $user_id, int $coin_pair_id): ?FutureUserSetting
    {
        return FutureUserSetting::query()
            ->where('user_id', $user_id)
            ->where('coin_pair_id', $coin_pair_id)
            ->first();
    }

    public function leverageSettingUpdate(FutureLeverageSettingUpdateRequest $request, ?int $user_id = null): array
    {
        $user_id ??= authId();

        $coinPair = CoinPairRepository::getCoinPairByUid($request->coin_pair_uid);

        if (!$coinPair) {
            return failed(__('Coin pair not found'));
        }

        $this->validateLeverageSettingUpdate($coinPair, $request->leverage, $user_id);

        $userSetting = FutureUserSetting::where([
            'user_id' => $user_id,
            'coin_pair_id' => $coinPair->id,
        ])->first();

        if ($userSetting) {
            $userSetting->update([
                'leverage' => $request->leverage,
            ]);
        } else {
            $userSetting = FutureUserSetting::create([
                'uid' => Str::uuid()->getHex(),
                'user_id' => $user_id,
                'coin_pair_id' => $coinPair->id,
                'leverage' => $request->leverage,
            ]);
        }

        if (!$userSetting) {
            return failed(__('Leverage update failed'));
        }
        return success(__('Leverage updated successfully'));
    }

    private function validateLeverageSettingUpdate(FutureCoinPair $coinPair, int $new_leverage, int $user_id)
    {
        $max_leverage = $coinPair->max_leverage ?: 1;

        if ($max_leverage < $new_leverage) {
            ResponseFacade::failed(__('Max :leverage leverage is allow on this pair', ['leverage' => $max_leverage]))->throw();
        }

        $position = PositionRepository::getPosition($coinPair->id, $user_id);
        $positionMarginMode = $position?->margin_mode ?: null;

        if ($positionMarginMode == MarginModeEnum::ISOLATED) {
            $current_leverage = self::getUserFutureSetting($user_id, $coinPair->id)?->leverage ?? 1;
            if ($current_leverage < $new_leverage) {
                ResponseFacade::failed(__('You are not able to increase leverage for isolated mode'))->throw();
            }
        }

        if ($positionMarginMode == MarginModeEnum::CROSS) {
            $costService = new CostService($user_id);
            $costService->ignore_ids = [$position->id];
            $total_cost = $costService->getUserCost();

            $position->leverage = $new_leverage;
            $position_cost = PositionMath::initialMargin($position);

            $cost = bcaddx($total_cost, $position_cost, $coinPair->trade_decimal ?: 8);

            $wallet = FutureWalletRepository::getWallet($coinPair->trade_coin_id, $user_id);

            $available_balance = bcsubx($wallet->balance, $cost, $coinPair->trade_decimal ?: 8);
            if ($available_balance <= 0) {
                ResponseFacade::failed(__('Insufficient margin balance for open position, Try higher leverage'))->throw();
            }
        }
    }

    public function marginUpdate(FutureMarginModeUpdateRequest $request, ?int $user_id = null): array
    {
        $user_id ??= authId();

        $coinPair = CoinPairRepository::getCoinPairByUid($request->coin_pair_uid);

        if (!$coinPair) {
            return failed(__('Coin pair not found'));
        }

        $this->marginUpdateValidation(
            coin_pair_id: $coinPair->id,
            margin_mode: $request->margin_mode,
            user_id: $user_id
        );

        $dbResponse = FutureUserSetting::updateOrCreate([
            'user_id' => $user_id,
            'coin_pair_id' => $coinPair->id,
        ], ['margin_mode' => $request->margin_mode]);

        if (!$dbResponse) {
            return failed(__('Margin mode update failed'));
        }
        return success(__('Margin mode updated successfully'));
    }

    private function marginUpdateValidation(int $coin_pair_id, int $margin_mode, ?int $user_id = null)
    {
        $user_id ??= authId();
        $message = __('This margin mode cannot be changed while you have an open order/position');

        $hasOpenBuyOrder = FutureBuy::query()
            ->where('future_coin_pair_id', $coin_pair_id)
            ->where('user_id', $user_id)
            ->where('status', OrderStatusEnum::PENDING->value)
            ->where('margin_mode', '<>', $margin_mode)
            ->where('pending_amount', '>', 0)
            ->exists();

        if ($hasOpenBuyOrder) {
            ResponseFacade::failed($message)->throw();
        }

        $hasOpenSellOrder = FutureSell::query()
            ->where('future_coin_pair_id', $coin_pair_id)
            ->where('user_id', $user_id)
            ->where('status', OrderStatusEnum::PENDING->value)
            ->where('margin_mode', '<>', $margin_mode)
            ->where('pending_amount', '>', 0)
            ->exists();

        if ($hasOpenSellOrder) {
            ResponseFacade::failed($message)->throw();
        }

        $hasOpenPosition = FuturePosition::query()
            ->where('future_coin_pair_id', $coin_pair_id)
            ->where('user_id', $user_id)
            ->where('status', PositionStatusEnum::OPEN->value)
            ->where('margin_mode', '<>', $margin_mode)
            ->where('amount', '>', 0)
            ->exists();

        if ($hasOpenPosition) {
            ResponseFacade::failed($message)->throw();
        }
    }

    public static function getUserMarginLeverage(string $coin_pair_uid, int $max_leverage): ?FutureLeverageSetting
    {
        return FutureLeverageSetting::getUserMarginLeverage($coin_pair_uid, $max_leverage);
    }

    public function getLeverageSettingData(string $coin_pair_uid, ?string $leverage = null): array
    {
        $coinPair = CoinPairRepository::getCoinPairByUid($coin_pair_uid);

        if (!$coinPair) {
            return failed(__('Coin pair not found'));
        }

        if (!$leverage) {
            $userSetting = LeverageSettingService::getUserFutureSetting(authId(), $coinPair->id);

            if (!$leverage = $userSetting?->leverage) {
                return failed(__('Leverage not found'));
            }
        }

        $leverageSetting = FutureLeverageSetting::where('coin_pair_uid', $coin_pair_uid)
            ->where('max_leverage', '<=', $leverage)
            ->orderBy('max_leverage', 'desc')
            ->first();

        if (!$leverageSetting) {
            return failed(__('Leverage setting not found for the given leverage'));
        }

        return success(__('Leverage setting found successfully'), $leverageSetting);
    }
}
