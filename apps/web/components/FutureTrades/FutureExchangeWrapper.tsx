import React, { useState } from "react";
import OrderBook from "./order_book/OrderBook";
import TradeOrderFormBox from "./trade_order/TradeOrderFormBox";
import MarginRatio from "./MarginRatio";
import FuturesAssets from "./FuturesAssets";
import HistorySection from "./HistorySection";
import TradesSection from "./TradesSection";
import TickerSection from "./ticker/TickerSection";
import {
  useFutureUserMarginLeverage,
  useGetFutureAllLeverageSettings,
  useGetFutureOpenOrderAndPositionAmount,
  useGetFutureWalletDetails,
} from "state/actions/future";
import dynamic from "next/dynamic";
import { useDispatch, useSelector } from "react-redux";
import { RootState } from "state/store";
import { useFutureSocket } from "hooks/useFutureSocket";
import {
  addFutureTrade,
  updateFutureMargin,
  updateFutureOrderLevel,
  updateFutureOrderTotalAmount,
  updateFuturePairDetails,
  updateFuturePosition,
  updateFutureUserAsstesSummary,
  updateFutureWallet,
} from "state/reducer/futureReducer";
import { FUTURE_ORDER_TYPE } from "helpers/core-constants";
import { useFuturePrivateSocket } from "hooks/useFuturePrivateSocket";
import { updateRealTimeChartData } from "hooks/trading/trading_view.streaming";

const TradingViewChart = dynamic(() => import("./FutureTradingChart"), {
  ssr: false,
});

export default function FutureExchangeWrapper() {
  const dispatch = useDispatch();

  const { loading } = useFutureUserMarginLeverage();
  const { loading: isWalletDetailsLoading } = useGetFutureWalletDetails();
  const { loading: isGettingAllLeverage } = useGetFutureAllLeverageSettings();

  const { pairDetails } = useSelector((state: RootState) => state.futureTrade);

  const { loading: isGettingOrderTotalAmount } =
    useGetFutureOpenOrderAndPositionAmount();

  useFutureSocket({
    coinPairUid: String(pairDetails?.uid),

    onTrade: (data) => {
      dispatch(addFutureTrade(data));
      updateRealTimeChartData(data);
    },

    onOrderbook: (data) => {
      const orderSide: "buy" | "sell" =
        data.order_type === FUTURE_ORDER_TYPE.BUY ? "buy" : "sell";

      dispatch(
        updateFutureOrderLevel({
          side: orderSide,
          data: data.order,
        }),
      );
    },

    on24hChange: (data) => {
      dispatch(
        updateFuturePairDetails({
          base_decimal: data.base_decimal,
          trade_decimal: data.trade_decimal,
          min_amount: data.min_amount,
          max_amount: data.max_amount,
          maker_fees_percent: data.maker_fees_percent,
          taker_fees_percent: data.taker_fees_percent,
          min_stop_limit_percent: data.min_stop_limit_percent,
          max_stop_limit_percent: data.max_stop_limit_percent,
          floor_ratio: data.floor_ratio,
          cap_ratio: data.cap_ratio,
          leverage: data.leverage,
          max_leverage: data.max_leverage,
          max_open_orders: data.max_open_orders,
          funding_rate: data.funding_rate,
          funding_next_time: data.funding_next_time,
          slippage_percent: data.slippage_percent,
          previous_price: data.previous_price,
          market_price: data.market_price,
          mark_price: data.mark_price,
          index_price: data.index_price,
          change: data.change,
          high: data.high,
          low: data.low,
          base_volume: data.base_volume,
          trade_volume: data.trade_volume,
        }),
      );
    },
  });

  useFuturePrivateSocket({
    onAssetSummary: (data) => {
      dispatch(updateFutureUserAsstesSummary(data));
    },

    onMarginSummary: (data) => {
      dispatch(updateFutureMargin(data));
    },

    onPosition: (data) => {
      dispatch(updateFuturePosition(data));
    },
    onFutureWallet: (data) => {
      dispatch(updateFutureWallet(data));
    },

    onFutureOrderSummary: (data) => {
      dispatch(updateFutureOrderTotalAmount(data));
    },
  });

  return (
    <div className="container-dashboard">
      {/* Top Bar */}
      <div className=" tradex-border tradex-border-b-0 tradex-border-background-primary">
        <TickerSection />
      </div>

      {/* Main Trading Section */}
      <div className="tradex-flex tradex-flex-col md:tradex-flex-row tradex-min-h-[608px]">
        {/* Chart */}
        <div className=" tradex-flex tradex-flex-col lg:tradex-flex-row tradex-flex-1">
          <div className="tradex-flex-1 tradex-bg-background-main tradex-rounded tradex-border tradex-border-background-primary">
            <TradingViewChart className=" tradex-h-[608px]" />
          </div>

          {/* Order Book + Trade */}
          <div className=" tradex-w-full lg:tradex-w-[265px] tradex-flex tradex-flex-col  sm:tradex-flex-row lg:tradex-flex-col">
            <div className=" tradex-border-y tradex-border-background-primary">
              <OrderBook />
            </div>
            <TradesSection />
          </div>
        </div>
        {/* Right Panel */}
        <div className=" tradex-border tradex-border-background-primary">
          <TradeOrderFormBox />
        </div>
      </div>

      {/* Bottom Section */}
      <div className="tradex-flex tradex-flex-col md:tradex-flex-row tradex-border tradex-border-background-primary tradex-min-h-[408px]">
        {/* Table */}
        <HistorySection />

        {/* Margin + Asset */}
        <div className=" tradex-w-full tradex-min-w-[265px] md:tradex-w-[265px] tradex-flex tradex-flex-col tradex-border-l tradex-border-background-primary">
          <div className=" tradex-border-b tradex-border-background-primary">
            <MarginRatio />
          </div>
          <div className=" ">
            <FuturesAssets />
          </div>
        </div>
      </div>
    </div>
  );
}
