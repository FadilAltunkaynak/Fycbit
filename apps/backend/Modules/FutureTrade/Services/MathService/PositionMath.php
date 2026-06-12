<?php

namespace Modules\FutureTrade\Services\MathService;

use Modules\FutureTrade\DataObject\FuturePositionData;
use Modules\FutureTrade\Emum\OrderType as PositionType;
use Modules\FutureTrade\Emum\OrderType;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Entities\FutureTrade;
use Modules\FutureTrade\Services\LeverageSettingService\LeverageSettingService;

class PositionMath
{
    /**
     * Get PNL (Profit and Loss)
     * Return 3 type of PNL
     * 1. Normal PNL     (Market Price)
     * 2. UnRealized PnL (Mark Price)
     * 3. Realized PnL   (Close Price)
     */
    public static function getPnl(mixed $mark_price, mixed $entryPrice, mixed $position_size, OrderType $direction, int $scale = 8): string
    {
        // Position Size * Direction(1 or -1) * (Mark Price - Entry Price)
        $priceDiff = bcsubx($mark_price, $entryPrice, $scale);
        $pnl = bcmulx($priceDiff, $position_size, $scale);

        return bcmulx($pnl, $direction == OrderType::BUY ? 1 : -1, $scale);
    }

    /**
     * Get ROI Of Position
     *
     * pnl    from PositionMath::getPnl()
     * margin from MarketMath::margin()
     *
     * @return string
     */
    public static function getRoi(mixed $pnl, mixed $margin, int $scale = 8)
    {
        // ROI% = ( PNL / Initial Margin ) * 100%
        $roi_ratio = bcdivx($pnl, $margin, $scale);

        return bcmulx($roi_ratio, 100, $scale);
    }

    /**
     * Get Target Price For Long Or Short
     *
     * @return string
     */
    public static function targetPrice(mixed $orderType, mixed $entryPrice, mixed $roi, mixed $leverage, int $scale = 8)
    {
        // Long target price  = entry price * ( ROE% / leverage + 1 )
        // Short target price = entry price * ( 1 - ROE% / leverage )
        $finalRoi = null;
        $finalLeverage = null;

        if ($orderType == 'LONG') {
            $finalRoi = $roi;
            $finalLeverage = bcaddx($leverage, 1, $scale);
        } else {
            $finalRoi = bcsubx(1, $roi, $scale);
            $finalLeverage = $leverage;
        }

        $longShortMultiplier = bcdivx($finalRoi, $finalLeverage, $scale);

        return bcmulx($entryPrice, $longShortMultiplier, $scale);
    }

    /**
     * Get New Entry Price For Position
     */
    public static function entryPrice(FuturePositionData $new_position, FuturePosition $current_position): float|string
    {
        $decimal = $new_position?->coinPair?->trade_decimal ?? 8;
        $amount = bcaddx($current_position->amount, $new_position->amount, $decimal);
        if ($amount == 0) {
            return $new_position->price;
        }

        $current_total_price = bcmulx($current_position->price, $current_position->amount, $decimal);
        $new_total_price = bcmulx($new_position->price, $new_position->amount, $decimal);
        $total_price = bcaddx($current_total_price, $new_total_price, $decimal);
        $price = bcdivx($total_price, $amount, $decimal);

        return $price;
    }

    /**
     * Get Close Amount For Position
     */
    public static function positionCloseAmount(FuturePositionData $newPosition, FuturePosition $current_position): string
    {
        $decimal = $newPosition?->coinPair?->trade_decimal ?? 8;
        $position = $current_position->amount;
        $amount = $newPosition->amount;
        $new_position = bcaddx($position, $amount, $decimal);

        if ($newPosition->order_type == PositionType::BUY && $position < 0) {
            if ($new_position < 0) {
                return bcmulx(-1, $amount, $decimal);
            } else {
                return $position;
            }
        }

        if (
            $newPosition->order_type == PositionType::SELL &&
            $position > 0 &&
            $new_position < 0
        ) {
            return $position;
        }

        return $amount;
    }

    /**
     * Get Initial Margin For Position
     * 
     * Attention: (FutureTrade Type) $position required a attribure "user_id"
     */
    public static function initialMargin(FutureTrade|FuturePositionData|FuturePosition $position): string
    {
        $price = $position->price;
        $amount = abs($position->amount);
        $leverage = $position?->leverage ?? LeverageSettingService::getUserFutureSetting(
            user_id: $position?->user_id,
            coin_pair_id: $position?->coin_pair?->id ?? 0
        )?->leverage ?: 1;
        $decimal = $position?->coin_pair?->trade_decimal ?? 8;

        $total_price = bcmulx($price, $amount, $decimal);
        $initialMargin = bcdivx($total_price, $leverage, $decimal);

        return $initialMargin;
    }

    /**
     * Get Maintenance Margin
     *
     * @param  mixed  $scale
     */
    public static function getMaintenanceMargin(
        mixed $price,
        mixed $amount,
        mixed $maintenance_margin_rate,
        mixed $maintenance_amount,
        $scale = 8
    ): string {
        // Maintenance Margin = Amount * Price * Maintenance Margin Rate - Maintenance Amount
        $totalPrice = bcmulx($price, $amount, $scale);
        $maintenance_margin_rate_percent = bcdivx($maintenance_margin_rate, 100, $scale);
        $margin = bcmulx($totalPrice, $maintenance_margin_rate_percent, $scale);

        return bcsubx($margin, $maintenance_amount, $scale);
    }

    /**
     * Get Margin Ratio
     *
     * maintenance_margin from LeverageMath::getMaintenanceMargin()
     *
     * @param  mixed  $scale
     * @return string
     */
    public static function getMarginRation(mixed $maintenance_margin, mixed $margin_balance, $scale = 8)
    {
        // Margin Ratio = ( Maintenance Margin / Margin Balance ) * 100
        if ($margin_balance <= 0) {
            return 100;
        }

        $risk_Ratio = bcdivx($maintenance_margin, $margin_balance, $scale);

        return bcmulx($risk_Ratio, 100, $scale);
    }

    /**
     * Calculate Liquidation Price for Isolated Margin Mode
     *
     * Formula: (margin_balance - dir * amount * entry_price) / (amount * MMR% - dir * amount)
     */
    public static function getLiquidationPriceForIsolated(
        OrderType $order_type,
        mixed $entry_price,
        mixed $amount,
        mixed $margin_balance,
        mixed $maintenance_margin_rate,
        int $scale = 8,
    ): string {
        $amount = abs($amount);
        $dir = $order_type === OrderType::BUY ? '1' : '-1';

        return self::computeLiquidationPrice(
            dir: $dir,
            amount: $amount,
            entry_price: $entry_price,
            total_balance: $margin_balance ?: '0',
            maintenance_margin_rate: $maintenance_margin_rate,
            scale: $scale,
        );
    }

    /**
     * Calculate Liquidation Price for Cross Margin Mode
     *
     * Formula: ((wallet_balance + pnl_exclud_curr - mm_exclud_curr) - dir * amount * entry_price) / (amount * MMR% - dir * amount)
     */
    public static function getLiquidationPriceForCross(
        OrderType $order_type,
        mixed $entry_price,
        mixed $amount,
        mixed $wallet_balance,
        mixed $maintenance_margin_exclud_curr,
        mixed $pnl_exclud_curr,
        mixed $maintenance_margin_rate,
        int $scale = 8,
    ): string {
        $amount = abs($amount);
        $dir = $order_type === OrderType::BUY ? '1' : '-1';

        // total_balance = (wallet_balance + pnl_exclud_curr) - maintenance_margin_exclud_curr
        $total_balance = bcsubx(
            bcaddx($wallet_balance, $pnl_exclud_curr, $scale),
            $maintenance_margin_exclud_curr,
            $scale
        );

        return self::computeLiquidationPrice(
            dir: $dir,
            amount: $amount,
            entry_price: $entry_price,
            total_balance: $total_balance,
            maintenance_margin_rate: $maintenance_margin_rate,
            scale: $scale,
        );
    }

    /**
     * Core liquidation price formula shared by both margin modes.
     *
     * liq_price = (total_balance - dir * amount * entry_price) / (amount * MMR% - dir * amount)
     */
    private static function computeLiquidationPrice(
        string $dir,
        mixed $amount,
        mixed $entry_price,
        mixed $total_balance,
        mixed $maintenance_margin_rate,
        int $scale = 8,
    ): string {
        // MMR% = maintenance_margin_rate / 100
        $mmrPercent = bcdivx($maintenance_margin_rate, '100', $scale);

        // total_amount = dir * amount * entry_price
        $totalAmount = bcmulx($dir, bcmulx($amount, $entry_price, $scale), $scale);

        // total_size = (amount * MMR%) - (dir * amount)
        $totalSize = bcsubx(
            bcmulx($amount, $mmrPercent, $scale),
            bcmulx($dir, $amount, $scale),
            $scale
        );

        if (bccompx($totalSize, '0', $scale) === 0) {
            return '0';
        }

        return bcdivx(
            bcsubx($total_balance, $totalAmount, $scale),
            $totalSize,
            $scale
        );
    }
}
