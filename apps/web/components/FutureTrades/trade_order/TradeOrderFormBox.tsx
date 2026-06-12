import { Modal } from "components/ui/modal";
import { cn } from "helpers/functions";
import React, { useState } from "react";
import TradeOrderForm from "./TradeOrderForm";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
import { FUTURE_MARGIN_MODE } from "helpers/core-constants";
import MarginModeForm from "./MarginModeForm";
import LeverageSettingsForm from "./LeverageSettingsForm";

export default function TradeOrderFormBox() {
  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const { leverage: defaultLeverage, margin_mode } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  const [isMarginModalOpen, setIsMarginModalOpen] = useState(false);
  const [isLeverageModalOpen, setIsLeverageModalOpen] = useState(false);

  return (
    <>
      <div className=" tradex-w-full md:tradex-w-[265px] tradex-bg-background-main tradex-rounded tradex-p-2">
        <div className=" tradex-flex tradex-gap-2 tradex-items-center tradex-mb-4">
          <button
            disabled={!isLoggedIn}
            onClick={() => setIsMarginModalOpen(true)}
            className={cn(
              " tradex-max-w-20 tradex-w-full tradex-h-[30px] tradex-rounded-[20px] tradex-border-2 tradex-font-semibold tradex-border-border tradex-flex tradex-justify-center tradex-items-center px-4 tradex-text-xs tradex-text-body",
              !isLoggedIn && "tradex-cursor-not-allowed",
            )}
          >
            {margin_mode === FUTURE_MARGIN_MODE.CROSS ? "Cross" : "Isolated"}
          </button>
          <button
            disabled={!isLoggedIn}
            onClick={() => setIsLeverageModalOpen(true)}
            className={cn(
              " tradex-max-w-20 tradex-w-full tradex-h-[30px] tradex-rounded-[20px] tradex-border-2 tradex-font-semibold tradex-border-border tradex-flex tradex-justify-center tradex-items-center px-4 tradex-text-xs tradex-text-body",
              !isLoggedIn && "tradex-cursor-not-allowed",
            )}
          >
            {defaultLeverage}x
          </button>
        </div>
        <TradeOrderForm />
      </div>
      <Modal
        open={isMarginModalOpen}
        onOpenChange={setIsMarginModalOpen}
        className=" tradex-max-w-[450px] tradex-p-6 !tradex-rounded-2xl"
      >
        <MarginModeForm closeModal={() => setIsMarginModalOpen(false)} />
      </Modal>
      <Modal
        open={isLeverageModalOpen}
        onOpenChange={setIsLeverageModalOpen}
        className=" tradex-max-w-[450px] tradex-p-6 !tradex-rounded-2xl"
      >
        <LeverageSettingsForm
          closeModal={() => setIsLeverageModalOpen(false)}
        />
      </Modal>
    </>
  );
}
