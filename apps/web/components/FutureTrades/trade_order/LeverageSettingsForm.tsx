import { cn, formatAmountDecimal } from "helpers/functions";
import useTranslation from "next-translate/useTranslation";
import React from "react";
import { AiOutlineMinus, AiOutlinePlus } from "react-icons/ai";
import { BsInfoCircle } from "react-icons/bs";
import { IoMdClose } from "react-icons/io";
import { useSelector } from "react-redux";
import { useFuturesLeverageSetting } from "state/actions/future";
import { RootState } from "state/store";

export default function LeverageSettingsForm({
  closeModal,
}: {
  closeModal: () => void;
}) {
  const { t } = useTranslation("common");

  const { pairDetails } = useSelector((state: RootState) => state.futureTrade);

  const {
    errors,
    form,
    onSubmit,
    setField,
    loading,
    isGettingValue,
    maxPositionAmount,
    maxLeverage,
  } = useFuturesLeverageSetting();

  const onChangeLeverage = (value: number) => {
    if (value < 1 || value > maxLeverage) return;
    setField("leverage", value);
  };
  return (
    <>
      <div className=" tradex-flex tradex-justify-between tradex-items-center">
        <h3 className=" tradex-text-3xl tradex-font-bold !tradex-text-title">
          {t("Adjust Leverage")}
        </h3>
        <span
          onClick={closeModal}
          className=" tradex-cursor-pointer tradex-h-9 tradex-w-9 tradex-rounded-full tradex-flex tradex-justify-center tradex-items-center tradex-border tradex-border-border"
        >
          <IoMdClose size={20} className=" tradex-text-body" />
        </span>
      </div>
      <div className=" tradex-mt-6 tradex-space-y-4">
        <div className=" tradex-space-y-1">
          <div className=" tradex-flex tradex-items-center tradex-border tradex-border-border tradex-rounded-xl tradex-h-12 tradex-w-full tradex-justify-between tradex-px-1">
            <span
              onClick={(e) => onChangeLeverage(form.leverage + 1)}
              className=" tradex-cursor-pointer  tradex-flex tradex-w-10 tradex-h-10 tradex-rounded-lg tradex-bg-background-primary tradex-items-center tradex-justify-center tradex-text-title"
            >
              <AiOutlinePlus size={20} />
            </span>
            <input
              type="number"
              max={maxLeverage}
              min={0}
              value={form.leverage}
              onChange={(e) => onChangeLeverage(Number(e.target.value))}
              className="tradex-h-full tradex-w-full tradex-bg-transparent !tradex-border-0 tradex-max-w-[200px] tradex-text-sm tradex-font-medium tradex-text-title tradex-text-center"
            />
            <span
              onClick={(e) => onChangeLeverage(form.leverage - 1)}
              className=" tradex-cursor-pointer  tradex-flex tradex-w-10 tradex-h-10 tradex-rounded-lg tradex-bg-background-primary tradex-items-center tradex-justify-center tradex-text-title"
            >
              <AiOutlineMinus size={20} />
            </span>
          </div>
          {errors.leverage && (
            <p className=" tradex-text-xs tradex-text-red-600">
              {errors.leverage}
            </p>
          )}
        </div>
        <div>
          <input
            type="range"
            min={1}
            max={maxLeverage}
            step={1}
            value={form.leverage}
            onChange={(e) => onChangeLeverage(Number(e.target.value))}
            className={cn(
              " tradex-appearance-none tradex-w-full tradex-h-2 !tradex-border-0 tradex-bg-background-primary tradex-rounded-full tradex-opacity-70 hover:tradex-opacity-100 tradex-transition-all tradex-duration-300 leverageRange",
            )}
          />
          <div className=" tradex-flex tradex-justify-between tradex-gap-3 tradex-items-center tradex-text-sm tradex-font-medium tradex-text-body tradex-px-2">
            <span>1x</span>
            <span>{maxLeverage}x</span>
          </div>
        </div>

        <ul className=" tradex-ml-5 tradex-list-outside tradex-list-disc tradex-text-sm tradex-leading-5 tradex-text-body tradex-space-y-1">
          <li>
            {t("Maximum position at current leverage:")}{" "}
            {isGettingValue
              ? "_ _ _ _"
              : formatAmountDecimal(
                  maxPositionAmount,
                  pairDetails?.trade_decimal || 2,
                )}{" "}
            {pairDetails?.trade_coin_code}
          </li>
          <li>
            {t(
              "Please note that leverage changing will also apply for open positions and open orders.",
            )}
          </li>
        </ul>
        <div className=" tradex-text-sm tradex-flex tradex-gap-1.5 tradex-text-red-600">
          <span className=" tradex-inline-block tradex-mt-1">
            <BsInfoCircle />
          </span>
          <p className=" tradex-text-sm tradex-text-red-600">
            {t(
              "Selecting higher leverage such as [10x] increases your liquidation risk. Always manage your risk levels.",
            )}
          </p>
        </div>
      </div>
      <button
        onClick={() => onSubmit(closeModal)}
        disabled={loading}
        className=" tradex-mt-8 tradex-bg-primary tradex-text-white tradex-border-primary  tradex-w-full tradex-h-12 tradex-rounded-[20px] tradex-border tradex-flex tradex-justify-center tradex-items-center px-4 tradex-text-sm tradex-font-semibold"
      >
        {loading ? t("Loading..") : t("Confirm")}
      </button>
    </>
  );
}
