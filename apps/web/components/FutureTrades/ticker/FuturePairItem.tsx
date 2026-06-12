import { useDropdown } from "components/ui/dropdown";
import { cn, formatAmountDecimal, numberToKMBFunc } from "helpers/functions";
import { useRouter } from "next/router";
import React from "react";
import { useSelector } from "react-redux";
import { FuturePairItem } from "state/reducer/futureReducer";
import { RootState } from "state/store";

export default function PairItem({ item }: { item: FuturePairItem }) {
  const { close } = useDropdown();
  const { pairDetails } = useSelector((state: RootState) => state.futureTrade);
  const isUpPrice = Number(item?.market_price) >= Number(item?.previous_price);

  const router = useRouter();
  return (
    <div
      className=" tradex-grid tradex-grid-cols-4 tradex-mb-2 last:tradex-mb-0 tradex-cursor-pointer"
      onClick={() => {
        close();
        router.push(`/futures/exchange/${item.code}`);
      }}
    >
      <p className=" tradex-text-title tradex-font-medium tradex-text-xs tradex-leading-4">
        {item.code}
      </p>
      <p
        className={cn(
          " tradex-font-medium tradex-text-xs tradex-leading-4 tradex-text-end",
          isUpPrice ? " tradex-text-future-trade-green" : "tradex-text-future-trade-red",
        )}
      >
        {formatAmountDecimal(
          Number(item.market_price),
          pairDetails?.trade_decimal || 2,
        )}
      </p>
      <p
        className={cn(
          " tradex-font-medium tradex-text-xs tradex-leading-4 tradex-text-end",
          Number(item?.change || 0) >= 0 ? " tradex-text-future-trade-green" : "tradex-text-future-trade-red",
        )}
      >
        {item.change}%
      </p>
      <p className=" tradex-text-body tradex-font-medium tradex-text-xs tradex-leading-4 tradex-text-end">
        {numberToKMBFunc(Number(item.volume), pairDetails?.base_decimal || 2)}
      </p>
    </div>
  );
}
