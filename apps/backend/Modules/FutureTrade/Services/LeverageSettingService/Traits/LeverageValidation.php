<?php

namespace Modules\FutureTrade\Services\LeverageSettingService\Traits;

use App\Facades\ResponseFacade;
use Modules\FutureTrade\Entities\FutureLeverageSetting;
use Modules\FutureTrade\Services\MathService\LeverageMath;

trait LeverageValidation
{
    /**
     * Validate Leverage Setting Range Duplicate
     *
     * @throws \Exception
     */
    public function leverageSettingRangeDuplicateCheck(
        string $coin_pair_uid,
        float $min_position_amount,
        float $max_position_amount
    ): bool {
        $hasDuplicate = FutureLeverageSetting::query()
            ->where('coin_pair_uid', $coin_pair_uid)
            ->where(
                fn ($q) => $q->where('min_position_amount', $min_position_amount)
                    ->orWhere('max_position_amount', $max_position_amount)
            )->first();

        if ($hasDuplicate) {
            if ($hasDuplicate?->min_position_amount == $min_position_amount) {
                ResponseFacade::failed(__('Minimum position range already exists'))->safeThrow();
            }

            if ($hasDuplicate?->max_position_amount == $max_position_amount) {
                ResponseFacade::failed(__('Maximum position range already exists'))->safeThrow();
            }

            return false;
        }

        return true;
    }

    /**
     * Validate New Minimum Position Amount
     *
     * @param  float  $min_position_amount  new position amount
     */
    public function leverageSettingMinPositionAmountCheck(
        string $coin_pair_uid,
        float $min_position_amount,
        string $uid = ''
    ): bool {
        $currentLeverageMaxPosition = FutureLeverageSetting::query()
            ->when($uid, fn ($q) => $q->where('uid', '!=', $uid))
            ->where('coin_pair_uid', $coin_pair_uid)
            ->where('min_position_amount', '<', $min_position_amount)
            ->max('max_position_amount') ?? 0;

        if (
            $currentLeverageMaxPosition &&
            $currentLeverageMaxPosition >= $min_position_amount
        ) {
            ResponseFacade::failed(__('Minimum position amount must be greater than existing maximum position amount'))->safeThrow();

            return false;
        }

        return true;
    }

    /**
     * Validate New MaxLeverage Amount
     */
    public function leverageSettingNewLeverageCheck(
        string $coin_pair_uid,
        float $max_leverage,
        float $min_position_amount,
    ): bool {
        $currentMinLeverage = FutureLeverageSetting::query()
            ->where('coin_pair_uid', $coin_pair_uid)
            ->where('max_position_amount', '<', $min_position_amount)
            ->min('max_leverage') ?? 0;

        if (
            $currentMinLeverage &&
            $currentMinLeverage <= $max_leverage
        ) {
            ResponseFacade::failed(__('Max Leverage must be less than existing minimum position max leverage amount'))->safeThrow();

            return false;
        }

        return true;
    }

    /**
     * Check User Maintenance Amount Not Greater Than Max Maintenance Amount
     */
    public function leverageSettingMaxMaintenanceAmountCheck(
        float $maintenance_amount,
        float $min_position_amount,
        float $maintenance_margin_rate,
    ): bool {
        $max_maintenance_amount = LeverageMath::max_maintenance_amount(
            position_amount: $min_position_amount,
            maintenance_margin_rate: $maintenance_margin_rate
        );

        if (
            $maintenance_amount > 0 &&
            $maintenance_amount >= $max_maintenance_amount
        ) {
            $message = $max_maintenance_amount > 0
                ? __('Maintenance Amount must be lower than :amount for this range', ['amount' => $max_maintenance_amount])
                : __('Maintenance Amount must be 0 for this range');

            ResponseFacade::failed($message)->safeThrow();
        }

        return true;
    }
}
