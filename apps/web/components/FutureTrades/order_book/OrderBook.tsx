import React, { useState } from "react";

import { BsArrowDown, BsArrowUp } from "react-icons/bs";
import { ORDER_SIDE, TradeSideButton } from "./TradeSideButton";
import { cn, formatAmountDecimal, noExponents } from "helpers/functions";
import { useFutureOrdersMain } from "./useFutureOrdersMain";
import useTranslation from "next-translate/useTranslation";

export default function OrderBook() {
  const { t } = useTranslation("common");

  const {
    activeSide,
    base_currency,
    buyOrdersBook,
    pairDetails,
    sellOrdersBook,
    setActiveSide,
    trade_currency,
    baseDecimal,
    isUpPrice,
    tradeDecimal,
    handleUpdateTradeBasePrice,
  } = useFutureOrdersMain();

  return (
    <div className="tradex-flex-1 tradex-bg-background-main tradex-rounded tradex-p-2">
      <div className=" tradex-flex tradex-flex-col tradex-gap-2">
        <p className=" tradex-text-title tradex-font-bold tradex-text-sm">
          {t("Order Book")}
        </p>
        <div className=" tradex-flex tradex-gap-4 tradex-items-center">
          <TradeSideButton
            side={ORDER_SIDE.BUYSELL}
            activeSide={activeSide}
            onChange={setActiveSide}
          />

          <TradeSideButton
            side={ORDER_SIDE.BUY}
            activeSide={activeSide}
            onChange={setActiveSide}
          />

          <TradeSideButton
            side={ORDER_SIDE.SELL}
            activeSide={activeSide}
            onChange={setActiveSide}
          />
        </div>
      </div>
      <div className=" tradex-flex tradex-flex-col tradex-mt-2">
        <div
          className={cn(
            " tradex-grid tradex-grid-cols-3 tradex-mb-1 tradex-order-3",
            activeSide !== ORDER_SIDE.BUYSELL && "!tradex-order-2",
          )}
        >
          <p className=" tradex-text-body tradex-font-medium tradex-text-xs tradex-leading-5">
            {t("Price")} ({trade_currency})
          </p>
          <p className=" tradex-text-body tradex-font-medium tradex-text-xs tradex-leading-5">
            {t("Size")} ({base_currency})
          </p>
          <p className=" tradex-text-body tradex-font-medium tradex-text-xs tradex-leading-5 tradex-text-end">
            {t("Sum")} ({base_currency})
          </p>
        </div>
        {activeSide !== ORDER_SIDE.BUY && (
          <div
            className={cn(
              " tradex-order-3 tradex-min-h-[141px] tradex-max-h-[141px] tradex-overflow-y-auto",
              activeSide === ORDER_SIDE.SELL &&
                "tradex-min-h-[282px] tradex-max-h-[282px]",
            )}
          >
            {sellOrdersBook.map((item, index) => (
              <div
                key={index}
                className=" tradex-grid tradex-grid-cols-3 tradex-mb-1 last:tradex-mb-0  tradex-relative tradex-cursor-pointer"
                onClick={() => handleUpdateTradeBasePrice(Number(item.price))}
              >
                <p className=" tradex-text-future-trade-red tradex-font-semibold tradex-text-[11px] tradex-leading-4 tradex-z-[1]">
                  {noExponents(formatAmountDecimal(item.price, tradeDecimal))}
                </p>
                <p className=" tradex-text-future-trade-red tradex-font-semibold tradex-text-[11px] tradex-leading-4 tradex-z-[1]">
                  {noExponents(formatAmountDecimal(item.amount, baseDecimal))}
                </p>
                <p className=" tradex-text-future-trade-red tradex-font-semibold tradex-text-[11px] tradex-leading-4 tradex-text-end tradex-z-[1]">
                  {noExponents(formatAmountDecimal(item.total, baseDecimal))}
                </p>
                <span
                  style={{ width: `${item.percent}%` }}
                  className="tradex-bg-future-trade-red tradex-opacity-30 tradex-duration-200 tradex-absolute tradex-right-0 tradex-h-full"
                ></span>
              </div>
            ))}
          </div>
        )}
        <div
          className={cn(
            " tradex-flex tradex-gap-2 tradex-items-center tradex-my-1 tradex-order-3",
            activeSide !== ORDER_SIDE.BUYSELL && "!tradex-order-1",
          )}
        >
          <div className="tradex-flex tradex-gap-0.5 tradex-items-center">
            <p
              className={cn(
                "tradex-text-base tradex-leading-7 tradex-font-semibold ",
                isUpPrice
                  ? "tradex-text-future-trade-green"
                  : "tradex-text-future-trade-red",
              )}
            >
              {formatAmountDecimal(
                Number(pairDetails?.market_price ?? 0),
                tradeDecimal,
              )}
            </p>
            {isUpPrice ? (
              <span className=" tradex-text-future-trade-green">
                <BsArrowUp size={18} />
              </span>
            ) : (
              <span className=" tradex-text-future-trade-red">
                <BsArrowDown size={18} />
              </span>
            )}
          </div>
          <p className=" tradex-text-body tradex-font-medium tradex-text-xs tradex-leading-4">
            {formatAmountDecimal(
              Number(pairDetails?.mark_price ?? 0),
              tradeDecimal,
            )}
          </p>
        </div>
        {activeSide !== ORDER_SIDE.SELL && (
          <div
            className={cn(
              " tradex-order-3 tradex-min-h-[141px] tradex-max-h-[141px] tradex-overflow-y-auto",
              activeSide === ORDER_SIDE.BUY &&
                "tradex-min-h-[282px]  tradex-max-h-[282px]",
            )}
          >
            {buyOrdersBook.map((item, index) => (
              <div
                key={index}
                className=" tradex-grid tradex-grid-cols-3 tradex-mb-1 last:tradex-mb-0 tradex-relative tradex-cursor-pointer"
                onClick={() => handleUpdateTradeBasePrice(Number(item.price))}
              >
                <p className=" tradex-text-future-trade-green tradex-font-semibold tradex-text-[11px] tradex-leading-4 tradex-z-[1]">
                  {noExponents(formatAmountDecimal(item.price, tradeDecimal))}
                </p>
                <p className=" tradex-text-future-trade-green tradex-font-semibold tradex-text-[11px] tradex-leading-4 tradex-z-[1]">
                  {noExponents(formatAmountDecimal(item.amount, baseDecimal))}
                </p>
                <p className=" tradex-text-future-trade-green tradex-font-semibold tradex-text-[11px] tradex-leading-4 tradex-z-[1] tradex-text-end">
                  {noExponents(formatAmountDecimal(item.total, baseDecimal))}
                </p>
                <span
                  style={{ width: `${item.percent}%` }}
                  className="tradex-bg-future-trade-green tradex-opacity-30 tradex-duration-200 tradex-absolute tradex-right-0 tradex-h-full"
                ></span>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
