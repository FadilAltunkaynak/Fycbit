import { SliderRange } from "components/ui/slider";
import React, { useState } from "react";
import { BsInfoCircleFill } from "react-icons/bs";
import { FaCalculator } from "react-icons/fa";
import { OrderTypeButton } from "./OrderTypeButton";
import Tooltip from "rc-tooltip";
import "rc-tooltip/assets/bootstrap.css";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
import Link from "next/link";
import { useFuturesTradeForm } from "state/actions/future";
import { FUTURE_ORDER_METHOD, FUTURE_ORDER_TYPE } from "helpers/core-constants";
import { cn, formatAmountDecimal, noExponents } from "helpers/functions";
import useTranslation from "next-translate/useTranslation";

export default function TradeOrderForm() {
  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const { t } = useTranslation("common");

  const { pairDetails, future_wallet } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  const tradeDecimal = pairDetails?.trade_decimal || 2;

  const baseDecimal = pairDetails?.base_decimal || 2;

  const {
    errors,
    form,
    onSubmit,
    setField,
    toggleReduceOnly,
    toggleTpSl,
    onRangeUpdate,
    range,
    marketPrice,
    loading,
    max_amount_value,
    cost,
    isCostBuyError,
    isCostSellError,
  } = useFuturesTradeForm();

  return (
    <>
      <div className=" tradex-flex tradex-items-center tradex-justify-between">
        <div className=" tradex-flex tradex-gap-1.5 tradex-items-center">
          <OrderTypeButton
            label="Limit"
            value={FUTURE_ORDER_METHOD.LIMIT}
            active={form.order_method}
            onChange={(value) => setField("order_method", value)}
          />

          <OrderTypeButton
            label="Market"
            value={FUTURE_ORDER_METHOD.MARKET}
            active={form.order_method}
            onChange={(value) => setField("order_method", value)}
          />

          <OrderTypeButton
            label="Stop Limit"
            value={FUTURE_ORDER_METHOD.STOP_LIMIT}
            active={form.order_method}
            onChange={(value) => setField("order_method", value)}
          />
        </div>

        <Tooltip
          placement={"right"}
          overlay={
            <div className="">
              {t(
                "Market order is immediately matched to the best available market price",
              )}
              .
            </div>
          }
          trigger={["hover"]}
          overlayClassName="tradex-max-w-[250px]"
        >
          <span>
            <BsInfoCircleFill size={16} className=" tradex-text-body" />
          </span>
        </Tooltip>
      </div>
      <div className=" tradex-my-2 tradex-flex tradex-justify-between tradex-items-center">
        <div className=" tradex-flex tradex-gap-1 tradex-items-center">
          <p className=" tradex-text-xs tradex-text-body">Avbl</p>
          <p className=" tradex-text-xs tradex-text-title tradex-font-medium">
            {formatAmountDecimal(
              future_wallet?.available_balance,
              tradeDecimal,
            )}{" "}
            {pairDetails?.trade_coin_code}
          </p>
        </div>
        {/* <span>
          <FaCalculator size={12} className=" tradex-text-body" />
        </span> */}
      </div>
      <div className=" tradex-max-h-[490px] tradex-overflow-y-auto tradex-pr-1  tradex-overflow-x-hidden">
        <div className=" tradex-space-y-2">
          <div className=" tradex-space-y-2">
            {form.order_method === FUTURE_ORDER_METHOD.STOP_LIMIT && (
              <div className=" tradex-space-y-1">
                <div className=" tradex-h-12 tradex-rounded-xl tradex-border-2 tradex-border-border tradex-w-full tradex-flex tradex-items-center tradex-px-4">
                  <label
                    htmlFor="stop_price"
                    className=" tradex-text-body tradex-font-medium tradex-text-sm tradex-block tradex-whitespace-nowrap tradex-mb-0"
                  >
                    {t("Stop Price")}
                  </label>
                  <input
                    type="number"
                    id="stop_price"
                    step={"any"}
                    min={0}
                    value={form.stop_price}
                    onChange={(e) => setField("stop_price", e.target.value)}
                    className=" !tradex-border-0 tradex-text-title tradex-bg-transparent tradex-h-full tradex-text-sm tradex-max-w-[108px] tradex-px-2"
                  />
                  <div className=" tradex-flex tradex-gap-1 tradex-items-center tradex-min-w-fit">
                    <span className="tradex-text-body tradex-font-medium tradex-text-sm">
                      {pairDetails?.trade_coin_code}
                    </span>
                  </div>
                </div>
                {errors.stop_price && (
                  <p className=" tradex-text-xs tradex-text-red-600">
                    {errors.stop_price}
                  </p>
                )}
              </div>
            )}
            {form.order_method !== FUTURE_ORDER_METHOD.MARKET && (
              <div className=" tradex-space-y-1">
                <div className=" tradex-h-12 tradex-rounded-xl tradex-border-2 tradex-border-border tradex-w-full tradex-flex tradex-items-center tradex-px-4">
                  <label
                    htmlFor="price"
                    className=" tradex-text-body tradex-font-medium tradex-text-sm tradex-block tradex-whitespace-nowrap tradex-mb-0"
                  >
                    {t("Price")}
                  </label>
                  <input
                    type="number"
                    id="price"
                    step={"any"}
                    min={0}
                    value={form.price}
                    onChange={(e) => setField("price", e.target.value)}
                    className=" !tradex-border-0 tradex-text-title tradex-bg-transparent tradex-h-full tradex-text-sm tradex-max-w-[108px] tradex-px-2"
                  />
                  <div className=" tradex-flex tradex-gap-1 tradex-items-center tradex-min-w-fit">
                    <button
                      onClick={() => setField("price", marketPrice)}
                      className=" tradex-text-primary tradex-font-medium tradex-text-sm"
                    >
                      {t("Last")}
                    </button>
                    <span className="tradex-text-body tradex-font-medium tradex-text-sm">
                      {pairDetails?.trade_coin_code}
                    </span>
                  </div>
                </div>
                {errors.price && (
                  <p className=" tradex-text-xs tradex-text-red-600">
                    {errors.price}
                  </p>
                )}
              </div>
            )}
            <div className=" tradex-space-y-1">
              <div className=" tradex-h-12 tradex-rounded-xl tradex-border-2 tradex-border-border tradex-w-full tradex-flex tradex-items-center tradex-px-4">
                <label
                  htmlFor="amount"
                  className=" tradex-text-body tradex-font-medium tradex-text-sm tradex-block tradex-whitespace-nowrap tradex-mb-0"
                >
                  {t("Size")}
                </label>
                <input
                  type="number"
                  id="amount"
                  step={"any"}
                  value={form.amount}
                  onChange={(e) => setField("amount", e.target.value)}
                  min={0}
                  className=" !tradex-border-0 tradex-text-title tradex-bg-transparent tradex-h-full tradex-text-sm tradex-max-w-[120px] tradex-px-2"
                />
                <div className=" tradex-flex tradex-gap-1 tradex-items-center tradex-justify-end tradex-min-w-fit tradex-w-full">
                  <span className="tradex-text-body tradex-font-medium tradex-text-sm">
                    {pairDetails?.base_coin_code}
                  </span>
                </div>
              </div>
              {errors.amount && (
                <p className=" tradex-text-xs tradex-text-red-600">
                  {errors.amount}
                </p>
              )}
            </div>
          </div>
          <SliderRange
            max={100}
            range={range}
            step={1}
            onChange={onRangeUpdate}
            className="tradex-max-w-[250px]"
          />
        </div>

        {isLoggedIn && (
          <div className=" tradex-py-3 tradex-border-y tradex-border-border tradex-space-y-2 tradex-mt-3">
            <div>
              <label className=" tradex-flex tradex-gap-2 tradex-items-center tradex-mb-0">
                <input
                  type="checkbox"
                  className="tradex-accent-primary tradex-w-4 tradex-h-4"
                  checked={Boolean(form.is_tpsl)}
                  onChange={(e) => toggleTpSl(e.target.checked)}
                />

                <Tooltip
                  placement={"top"}
                  overlay={
                    <div className="">
                      {`Set take-profit/stop-loss prices in advance. Depending on
                      your trading strategy, you can choose to set limit or
                      market take-profit or stop-loss orders. You can also set
                      your order to trigger based on the "Last Price" or "Mark
                      Price".`}
                    </div>
                  }
                  trigger={["hover"]}
                  overlayClassName="tradex-max-w-[250px]"
                >
                  <span className=" tradex-text-sm tradex-font-medium tradex-text-title tradex-leading-7">
                    TP/SL
                  </span>
                </Tooltip>
              </label>
              {Boolean(form.is_tpsl) && (
                <div className=" tradex-space-y-2 tradex-mt-1">
                  <div className=" tradex-space-y-1">
                    <div className=" tradex-h-12 tradex-rounded-xl tradex-border-2 tradex-border-border tradex-w-full tradex-flex tradex-items-center tradex-px-4">
                      <label
                        htmlFor="take_profit_price"
                        className=" tradex-text-body tradex-font-medium tradex-text-sm tradex-block tradex-whitespace-nowrap tradex-mb-0"
                      >
                        {t("Take Profit")}
                      </label>
                      <input
                        type="number"
                        id="take_profit_price"
                        step={"any"}
                        min={0}
                        value={form.take_profit_price}
                        onChange={(e) =>
                          setField("take_profit_price", e.target.value)
                        }
                        className=" !tradex-border-0 tradex-text-title tradex-bg-transparent tradex-h-full tradex-text-sm tradex-max-w-[104px] tradex-px-2"
                      />
                      <div className=" tradex-flex tradex-gap-1 tradex-items-center tradex-min-w-fit">
                        <span className="tradex-text-body tradex-font-medium tradex-text-sm">
                          {pairDetails?.trade_coin_code}
                        </span>
                      </div>
                    </div>
                    {errors.take_profit_price && (
                      <p className=" tradex-text-xs tradex-text-red-600">
                        {errors.take_profit_price}
                      </p>
                    )}
                  </div>
                  <div className=" tradex-space-y-1">
                    <div className=" tradex-h-12 tradex-rounded-xl tradex-border-2 tradex-border-border tradex-w-full tradex-flex tradex-items-center tradex-px-4">
                      <label
                        htmlFor="stop_loss_price"
                        className=" tradex-text-body tradex-font-medium tradex-text-sm tradex-block tradex-whitespace-nowrap tradex-mb-0"
                      >
                        {t("Stop Loss")}
                      </label>
                      <input
                        type="number"
                        id="stop_loss_price"
                        step={"any"}
                        min={0}
                        value={form.stop_loss_price}
                        onChange={(e) =>
                          setField("stop_loss_price", e.target.value)
                        }
                        className=" !tradex-border-0 tradex-text-title tradex-bg-transparent tradex-h-full tradex-text-sm tradex-max-w-[108px] tradex-px-2"
                      />
                      <div className=" tradex-flex tradex-gap-1 tradex-items-center tradex-min-w-fit">
                        <span className="tradex-text-body tradex-font-medium tradex-text-sm">
                          {pairDetails?.trade_coin_code}
                        </span>
                      </div>
                    </div>
                    {errors.stop_loss_price && (
                      <p className=" tradex-text-xs tradex-text-red-600">
                        {errors.stop_loss_price}
                      </p>
                    )}
                  </div>
                </div>
              )}
            </div>

            <div>
              <label className=" tradex-flex tradex-gap-2 tradex-items-center tradex-mb-0 ">
                <input
                  type="checkbox"
                  checked={Boolean(form.is_reduce)}
                  onChange={(e) => toggleReduceOnly(e.target.checked)}
                  className="tradex-accent-primary tradex-w-4 tradex-h-4"
                />
                <Tooltip
                  placement={"top"}
                  overlay={
                    <div className="">
                      {t(
                        "Reduce-Only order serves to strictly reduce your open position",
                      )}
                      .
                    </div>
                  }
                  trigger={["hover"]}
                  overlayClassName="tradex-max-w-[250px]"
                >
                  <span className=" tradex-text-sm tradex-font-medium tradex-text-title tradex-leading-7">
                    {t("Reduce-Only")}
                  </span>
                </Tooltip>
              </label>
            </div>
          </div>
        )}

        <div className=" tradex-space-y-3  tradex-mt-4">
          {isLoggedIn ? (
            <>
              <div className=" tradex-flex tradex-justify-between tradex-gap-3 tradex-items-center">
                <button
                  onClick={() => onSubmit(FUTURE_ORDER_TYPE.BUY)}
                  disabled={loading}
                  className={cn(
                    " tradex-rounded-full tradex-flex tradex-justify-center tradex-items-center tradex-w-full tradex-h-10 tradex-bg-future-trade-green tradex-text-white tradex-text-base tradex-font-semibold",
                    loading && "tradex-cursor-not-allowed",
                  )}
                >
                  {loading ? "Loading.." : t("Buy/Long")}
                </button>
                <button
                  onClick={() => onSubmit(FUTURE_ORDER_TYPE.SELL)}
                  disabled={loading}
                  className={cn(
                    " tradex-rounded-full tradex-flex tradex-justify-center tradex-items-center tradex-w-full tradex-h-10 tradex-bg-future-trade-red tradex-text-white tradex-text-base tradex-font-semibold",
                    loading && "tradex-cursor-not-allowed",
                  )}
                >
                  {loading ? "Loading.." : t("Sell/Short")}
                </button>
              </div>
              <div className=" tradex-flex tradex-justify-between tradex-gap-3 tradex-items-center">
                <div>
                  <Tooltip
                    placement={"top"}
                    overlay={
                      <div className="">
                        {t(
                          "Cost cannot be more than available balance. Please adjust",
                        )}
                        .
                      </div>
                    }
                    trigger={["hover"]}
                    overlayClassName="tradex-max-w-[250px]"
                  >
                    <p className=" tradex-text-xs tradex-leading-6 tradex-text-body">
                      {t("Cost")}{" "}
                      <span
                        className={cn(
                          " tradex-font-semibold ",
                          isCostBuyError
                            ? " tradex-text-future-trade-red"
                            : "tradex-text-title",
                        )}
                      >
                        {noExponents(cost.buy)} {pairDetails?.trade_coin_code}
                      </span>
                    </p>
                  </Tooltip>
                  <p className=" tradex-text-xs tradex-leading-6 tradex-text-body">
                    {t("Max")}{" "}
                    <span className="tradex-font-semibold tradex-text-title">
                      {noExponents(
                        formatAmountDecimal(
                          max_amount_value,
                          pairDetails?.base_decimal || 2,
                        ),
                      )}{" "}
                      {pairDetails?.base_coin_code}
                    </span>
                  </p>
                </div>
                <div className=" tradex-text-right">
                  <Tooltip
                    placement={"left"}
                    overlay={
                      <div className="">
                        {t("Cost cannot be more than available balance. Please adjust")}.
                      </div>
                    }
                    trigger={["hover"]}
                    overlayClassName="tradex-max-w-[250px]"
                  >
                    <p className=" tradex-text-xs tradex-leading-6 tradex-text-body">
                      {t("Cost")}{" "}
                      <span
                        className={cn(
                          "tradex-font-semibold",
                          isCostSellError
                            ? " tradex-text-future-trade-red"
                            : "tradex-text-title",
                        )}
                      >
                        {noExponents(cost.sell)} {pairDetails?.trade_coin_code}
                      </span>
                    </p>
                  </Tooltip>
                  <p className=" tradex-text-xs tradex-leading-6 tradex-text-body">
                    {t("Max")}{" "}
                    <span className="tradex-font-semibold tradex-text-title">
                      {noExponents(
                        formatAmountDecimal(
                          max_amount_value,
                          pairDetails?.base_decimal || 2,
                        ),
                      )}{" "}
                      {pairDetails?.base_coin_code}
                    </span>
                  </p>
                </div>
              </div>
            </>
          ) : (
            <>
              <Link href={"/signup"}>
                <button className=" tradex-rounded-lg tradex-flex tradex-justify-center tradex-items-center tradex-w-full tradex-h-10 tradex-bg-primary tradex-text-white tradex-text-base tradex-font-semibold">
                  {t("Sign Up")}
                </button>
              </Link>
              <Link href={"/signin"}>
                <button className=" tradex-rounded-lg tradex-flex tradex-justify-center tradex-border tradex-border-primary tradex-items-center tradex-w-full tradex-h-10 hover:tradex-bg-primary tradex-text-primary hover:tradex-text-white tradex-text-base tradex-font-semibold">
                  {t("Login")}
                </button>
              </Link>
            </>
          )}
        </div>
      </div>
    </>
  );
}
