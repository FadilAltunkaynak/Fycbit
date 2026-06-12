import {
  FUTURE_ORDER_TYPE,
  FUTURE_TRANSACTION_TYPE,
  TPSL_TYPE,
} from "helpers/core-constants";
import {
  calculateInitialMargin,
  cn,
  formatAmountDecimal,
  minusNumbers,
  noExponents,
  nullChecker,
} from "helpers/functions";
import useTranslation from "next-translate/useTranslation";
import React, { useState } from "react";
import { IoMdClose } from "react-icons/io";
import { IoChevronDownOutline } from "react-icons/io5";
import { useSelector } from "react-redux";
import {
  usePositionsAdjustMarginForm,
  usePositionsTPSLForm,
} from "state/actions/future";
import { FuturePosition } from "state/reducer/futureReducer";
import { RootState } from "state/store";

export default function PositionsAdjustMarginForm({
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
    margin_balance_calculated,
    trade_currency,
    max_balance,
    liq_price,
  } = usePositionsAdjustMarginForm(item, close);

  return (
    <>
      <div className=" tradex-flex tradex-justify-between tradex-items-center">
        <h3 className=" tradex-text-3xl tradex-font-bold !tradex-text-title">
          {t("Adjust Margin")}
        </h3>
        <span
          onClick={close}
          className=" tradex-cursor-pointer tradex-h-9 tradex-w-9 tradex-rounded-full tradex-flex tradex-justify-center tradex-items-center tradex-border tradex-border-border"
        >
          <IoMdClose size={20} className=" tradex-text-body" />
        </span>
      </div>
      <div className=" tradex-mt-6">
        <div className=" tradex-space-y-1 ">
          <div className="tradex-flex tradex-gap-2 tradex-items-center">
            <div className=" tradex-h-12 tradex-rounded-xl tradex-border-2 tradex-border-border tradex-w-full tradex-flex tradex-items-center tradex-px-4">
              <label
                htmlFor="amount"
                className=" tradex-text-body tradex-font-medium tradex-text-sm tradex-block tradex-whitespace-nowrap tradex-mb-0"
              >
                {t("Amount")}
              </label>
              <input
                type="number"
                id="amount"
                step={"any"}
                min={0}
                value={form.amount || ""}
                onChange={(e) => {
                  setField("amount", e.target.value);
                }}
                className=" !tradex-border-0 tradex-text-title tradex-bg-transparent tradex-h-full tradex-w-full tradex-text-sm  tradex-px-2"
              />
              <div className=" tradex-relative tradex-group">
                <div className=" tradex-flex tradex-gap-2 tradex-items-center tradex-cursor-pointer">
                  <span className="tradex-text-body tradex-font-medium tradex-text-sm">
                    {form.action === FUTURE_TRANSACTION_TYPE.CREDIT
                      ? t("Add")
                      : t("Remove")}
                  </span>
                  <span>
                    <IoChevronDownOutline />
                  </span>
                </div>
                <div className=" tradex-hidden group-hover:tradex-flex tradex-p-3 tradex-absolute tradex-top-full tradex-right-0 tradex-bg-background-main tradex-shadow-xl tradex-min-w-[150px] tradex-rounded-md tradex-flex-col tradex-gap-2">
                  <span
                    onClick={() =>
                      setField("action", FUTURE_TRANSACTION_TYPE.CREDIT)
                    }
                    className={cn(
                      " tradex-font-medium tradex-text-sm tradex-cursor-pointer",
                      form.action === FUTURE_TRANSACTION_TYPE.CREDIT
                        ? "tradex-text-primary"
                        : "tradex-text-body",
                    )}
                  >
                    {t("Add")}
                  </span>
                  <span
                    onClick={() =>
                      setField("action", FUTURE_TRANSACTION_TYPE.DEBIT)
                    }
                    className={cn(
                      " tradex-font-medium tradex-text-sm tradex-cursor-pointer",
                      form.action === FUTURE_TRANSACTION_TYPE.DEBIT
                        ? "tradex-text-primary"
                        : "tradex-text-body",
                    )}
                  >
                    {t("Remove")}
                  </span>
                </div>
              </div>
            </div>
          </div>
          {errors.amount && (
            <p className=" tradex-text-xs tradex-text-red-600">
              {errors.amount}
            </p>
          )}
        </div>
      </div>
      <div className=" tradex-mt-6">
        <div className=" tradex-space-y-2.5">
          <div className=" tradex-flex tradex-justify-between tradex-items-center tradex-gap-4">
            <p className=" tradex-uppercase tradex-text-xs tradex-text-body tradex-font-bold">
              {t("Currently Assigned Margin")}
            </p>
            <p className={cn("tradex-text-sm tradex-text-title")}>
              {noExponents(margin_balance_calculated)} {trade_currency}
            </p>
          </div>
          <div className=" tradex-flex tradex-justify-between tradex-items-center tradex-gap-4">
            <p className=" tradex-uppercase tradex-text-xs tradex-text-body tradex-font-bold">
              {t("Max ") +
                (form.action == FUTURE_TRANSACTION_TYPE.CREDIT
                  ? t("Addable ")
                  : form.action == FUTURE_TRANSACTION_TYPE.DEBIT
                  ? t("removable ")
                  : "")}
            </p>
            <p className="tradex-text-sm tradex-text-title">
              {noExponents(max_balance)} {trade_currency}
            </p>
          </div>
          <div className=" tradex-flex tradex-justify-between tradex-items-center tradex-gap-4">
            <p className=" tradex-uppercase tradex-text-xs tradex-text-body tradex-font-bold">
              {t("Est.Liq. Price after ") +
                (form.action == FUTURE_TRANSACTION_TYPE.CREDIT
                  ? t("increase")
                  : form.action == FUTURE_TRANSACTION_TYPE.DEBIT
                  ? t("reduction")
                  : "")}
            </p>
            <p className="tradex-text-sm tradex-text-title">
              {noExponents(liq_price)} {trade_currency}
            </p>
          </div>
        </div>
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
        {loading ? "Loading.." : "Confirm"}
      </button>
    </>
  );
}
