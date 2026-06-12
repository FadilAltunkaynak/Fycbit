import { FUTURE_ORDER_TYPE, TPSL_TYPE } from "helpers/core-constants";
import { cn, noExponents } from "helpers/functions";
import useTranslation from "next-translate/useTranslation";
import React from "react";
import { IoMdClose } from "react-icons/io";
import { usePositionsTPSLForm } from "state/actions/future";
import { FuturePosition } from "state/reducer/futureReducer";

export default function PositionsTPSLForm({
  item,
  close,
}: {
  item: FuturePosition;
  close: () => void;
}) {
  const { t } = useTranslation("common");

  const {
    errors,
    form,
    onSubmit,
    setField,
    loading,
    isValidForm,
    pnl,
    onSlChange,
    onTpChange,
    onCancel,
  } = usePositionsTPSLForm(item, close);

  return (
    <>
      <div className=" tradex-flex tradex-justify-between tradex-items-center">
        <h3 className=" tradex-text-3xl tradex-font-bold !tradex-text-title">
          {t("TP/SL for entire position")}
        </h3>
        <span
          onClick={close}
          className=" tradex-cursor-pointer tradex-h-9 tradex-w-9 tradex-rounded-full tradex-flex tradex-justify-center tradex-items-center tradex-border tradex-border-border"
        >
          <IoMdClose size={20} className=" tradex-text-body" />
        </span>
      </div>
      <div className=" tradex-mt-6">
        <div className=" tradex-space-y-1.5">
          <div className=" tradex-flex tradex-justify-between tradex-items-center tradex-gap-4">
            <p className=" tradex-uppercase tradex-text-xs tradex-text-body tradex-font-bold">
              {t("Symbol")}
            </p>
            <p
              className={cn(
                "tradex-text-sm",
                item.order_type === FUTURE_ORDER_TYPE.BUY
                  ? " tradex-text-future-trade-green"
                  : "tradex-text-future-trade-red",
              )}
            >
              {item.code} {item.leverage}x
            </p>
          </div>
          <div className=" tradex-flex tradex-justify-between tradex-items-center tradex-gap-4">
            <p className=" tradex-uppercase tradex-text-xs tradex-text-body tradex-font-bold">
              {t("Entry Price")}
            </p>
            <p className="tradex-text-sm tradex-text-title">
              {item.price} {item.trade_coin_code}
            </p>
          </div>
          <div className=" tradex-flex tradex-justify-between tradex-items-center tradex-gap-4">
            <p className=" tradex-uppercase tradex-text-xs tradex-text-body tradex-font-bold">
              {t("Mark Price")}
            </p>
            <p className="tradex-text-sm tradex-text-title">
              {item.mark_price} {item.trade_coin_code}
            </p>
          </div>
        </div>
      </div>

      <div className=" tradex-mt-4">
        <div className="tradex-pb-4 tradex-border-b tradex-border-border tradex-mb-4">
          <div className=" tradex-space-y-1 ">
            <div className="tradex-flex tradex-gap-2 tradex-items-center">
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
                  value={form.take_profit_price || ""}
                  onChange={(e) => {
                    setField("take_profit_price", e.target.value);
                    onTpChange(Number(e.target.value));
                  }}
                  className=" !tradex-border-0 tradex-text-title tradex-bg-transparent tradex-h-full tradex-w-full tradex-text-sm  tradex-px-2"
                />
                <div className=" tradex-flex tradex-gap-1 tradex-items-center tradex-min-w-fit">
                  <span className="tradex-text-body tradex-font-medium tradex-text-sm">
                    {item?.trade_coin_code}
                  </span>
                </div>
              </div>
              {Boolean(Number(item.tp_price)) && (
                <span
                  onClick={() => {
                    if (loading) return;
                    onCancel(TPSL_TYPE.TAKE_PROFIT);
                  }}
                  className=" tradex-cursor-pointer tradex-h-6 tradex-w-6 tradex-rounded-full tradex-flex tradex-justify-center tradex-items-center tradex-border tradex-border-border"
                >
                  <IoMdClose size={14} className=" tradex-text-body" />
                </span>
              )}
            </div>
            {errors.take_profit_price && (
              <p className=" tradex-text-xs tradex-text-red-600">
                {errors.take_profit_price}
              </p>
            )}
          </div>

          <small className="tradex-block tradex-mt-1 tradex-leading-5 tradex-text-xs tradex-text-title">
            {`${t("When Mark Price reaches")} ${
              isNaN(Number(form.take_profit_price))
                ? "0.00"
                : noExponents(form.take_profit_price)
            }, ${t(
              "it will trigger Take Profit Market order to close this position. Estimated PNL will be ",
            )}`}{" "}
            <span
              className={cn(
                Number(pnl.tp) >= 0
                  ? " tradex-text-future-trade-green"
                  : "tradex-text-future-trade-red",
              )}
            >
              {noExponents(pnl.tp, item.trade_decimal) +
                " " +
                item.trade_coin_code}
            </span>
          </small>
        </div>
        <div>
          <div className=" tradex-space-y-1">
            <div className=" tradex-flex tradex-gap-2 tradex-items-center">
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
                  value={form.stop_loss_price || ""}
                  onChange={(e) => {
                    setField("stop_loss_price", e.target.value);
                    onSlChange(Number(e.target.value));
                  }}
                  className=" !tradex-border-0 tradex-text-title tradex-bg-transparent tradex-h-full tradex-text-sm tradex-w-full tradex-px-2"
                />
                <div className=" tradex-flex tradex-gap-1 tradex-items-center tradex-min-w-fit">
                  <span className="tradex-text-body tradex-font-medium tradex-text-sm">
                    {item?.trade_coin_code}
                  </span>
                </div>
              </div>
              {Boolean(Number(item.sl_price)) && (
                <span
                  onClick={() => {
                    if (loading) return;
                    onCancel(TPSL_TYPE.STOP_LOSS);
                  }}
                  className=" tradex-cursor-pointer tradex-h-6 tradex-w-6 tradex-rounded-full tradex-flex tradex-justify-center tradex-items-center tradex-border tradex-border-border"
                >
                  <IoMdClose size={14} className=" tradex-text-body" />
                </span>
              )}
            </div>
            {errors.stop_loss_price && (
              <p className=" tradex-text-xs tradex-text-red-600">
                {errors.stop_loss_price}
              </p>
            )}
          </div>

          <small className=" tradex-block tradex-mt-1 tradex-leading-5 tradex-text-xs tradex-text-title">
            {`${t("When Mark Price reaches")} ${
              isNaN(Number(form.stop_loss_price))
                ? "0.00"
                : noExponents(form.stop_loss_price)
            }, ${t(
              "it will trigger Stop Market order to close this position. Estimated PNL will be ",
            )}`}{" "}
            <span
              className={cn(
                Number(pnl.sl) >= 0
                  ? " tradex-text-future-trade-green"
                  : "tradex-text-future-trade-red",
              )}
            >
              {noExponents(pnl.sl, item.trade_decimal) +
                " " +
                item.trade_coin_code}
            </span>
          </small>
        </div>

        <small className="tradex-block tradex-leading-5 tradex-text-xs tradex-text-title tradex-mt-4">
          {t(
            "- This setting applies to the entire position. Take-profit and stop-loss automatically cancel after closing the position. A market order is triggered when the stop price is reached.",
          )}
        </small>
      </div>
      <button
        onClick={() => onSubmit()}
        disabled={loading || !isValidForm}
        className={cn(
          " tradex-mt-8 tradex-bg-primary tradex-text-white tradex-border-primary  tradex-w-full tradex-h-12 tradex-rounded-[20px] tradex-border tradex-flex tradex-justify-center tradex-items-center px-4 tradex-text-sm tradex-font-semibold",
          (loading || !isValidForm) &&
            " tradex-opacity-50 tradex-cursor-not-allowed",
        )}
      >
        {loading ? "Loading.." : t("Confirm")}
      </button>
    </>
  );
}
