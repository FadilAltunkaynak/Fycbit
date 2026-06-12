import { FUTURE_MARGIN_MODE } from "helpers/core-constants";
import { cn } from "helpers/functions";
import useTranslation from "next-translate/useTranslation";
import React from "react";
import { IoMdClose } from "react-icons/io";
import { useSelector } from "react-redux";
import { useFutureMarginSettingUpdate } from "state/actions/future";
import { RootState } from "state/store";

export default function MarginModeForm({
  closeModal,
}: {
  closeModal: () => void;
}) {
  const { t } = useTranslation("common");

  const { margin_mode } = useSelector((state: RootState) => state.futureTrade);
  const { error, loading, mode, onChangeMode, onUpdateFutureMarginSetting } =
    useFutureMarginSettingUpdate();
  return (
    <>
      <div className=" tradex-flex tradex-justify-between tradex-items-center">
        <h3 className=" tradex-text-3xl tradex-font-bold !tradex-text-title">
          {t("Margin Mode")}
        </h3>
        <span
          onClick={closeModal}
          className=" tradex-cursor-pointer tradex-h-9 tradex-w-9 tradex-rounded-full tradex-flex tradex-justify-center tradex-items-center tradex-border tradex-border-border"
        >
          <IoMdClose size={20} className=" tradex-text-body" />
        </span>
      </div>
      <div className=" tradex-mt-6 tradex-space-y-4">
        <div className=" tradex-flex tradex-gap-4 tradex-items-center">
          <button
            onClick={() => onChangeMode(FUTURE_MARGIN_MODE.CROSS)}
            className={cn(
              "tradex-w-full tradex-h-[40px] tradex-rounded-[20px] tradex-border-2 tradex-flex tradex-justify-center tradex-items-center px-4 tradex-text-sm tradex-font-semibold",
              mode === FUTURE_MARGIN_MODE.CROSS
                ? "tradex-text-primary tradex-border-primary  "
                : "tradex-border-border tradex-text-body",
            )}
          >
            {t("Cross")}
          </button>
          <button
            onClick={() => onChangeMode(FUTURE_MARGIN_MODE.ISOLATED)}
            className={cn(
              "tradex-w-full tradex-h-[40px] tradex-rounded-[20px] tradex-border-2 tradex-flex tradex-justify-center tradex-items-center px-4 tradex-text-sm tradex-font-semibold",
              mode === FUTURE_MARGIN_MODE.ISOLATED
                ? "tradex-text-primary tradex-border-primary  "
                : "tradex-border-border tradex-text-body",
            )}
          >
            {t("Isolated")}
          </button>
        </div>
        <p className=" tradex-text-sm tradex-text-body tradex-pb-3 tradex-border-b tradex-border-border">
          -{" "}
          {t(
            "Switching the margin mode will only apply it to the selected contract",
          )}
          .
        </p>

        <div className=" tradex-text-sm tradex-text-body">
          <span className=" tradex-font-bold">{t("Cross Margin Mode")}: </span>
          <span>
            {t(
              "All cross positions under the same margin asset share the same asset cross margin balance. In the event of liquidation, your assets full margin balance along with any remaining open positions under the asset may be forfeited.",
            )}
          </span>
        </div>
        <div className=" tradex-text-sm tradex-text-body">
          <span className=" tradex-font-bold">
            {t("Isolated Margin Mode")}:{" "}
          </span>
          <span>
            {t(
              "Manage your risk on individual positions by restricting the amount of margin allocated to each. If the margin ratio of a position reached 100%, the position will be liquidated. Margin can be added or removed to positions using this mode.",
            )}
          </span>
        </div>
      </div>
      <button
        disabled={loading || mode === margin_mode}
        onClick={onUpdateFutureMarginSetting}
        className={cn(
          " tradex-mt-8 tradex-bg-primary tradex-text-white tradex-border-primary  tradex-w-full tradex-h-12 tradex-rounded-[20px] tradex-border tradex-flex tradex-justify-center tradex-items-center px-4 tradex-text-sm tradex-font-semibold tradex-cursor-pointer",
          (loading || mode === margin_mode) &&
            "tradex-opacity-50 !tradex-cursor-not-allowed",
        )}
      >
        {t("Confirm")}
      </button>
    </>
  );
}
