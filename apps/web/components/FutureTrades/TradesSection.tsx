import { cn, formatAmountDecimal } from "helpers/functions";
import moment from "moment";
import useTranslation from "next-translate/useTranslation";
import React from "react";
import { useSelector } from "react-redux";
import { FutureTradeItem } from "state/reducer/futureReducer";
import { RootState } from "state/store";

export default function TradesSection() {
  const { t } = useTranslation("common");

  const { pairDetails, futureTrades } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  return (
    <div className="tradex-flex-1 lg:tradex-flex-auto tradex-h-[200px] lg:tradex-max-h-[200px] tradex-bg-background-main tradex-rounded tradex-p-2">
      <p className=" tradex-text-title tradex-font-bold tradex-text-sm">
        {t("Trades")}
      </p>
      <div className=" tradex-flex tradex-flex-col tradex-mt-2">
        <div className=" tradex-grid tradex-grid-cols-3 tradex-mb-1">
          <p className=" tradex-text-body tradex-font-medium tradex-text-xs tradex-leading-5">
            {t("Price")} ({pairDetails?.trade_coin_code})
          </p>
          <p className=" tradex-text-body tradex-font-medium tradex-text-xs tradex-leading-5">
            {t("Amount")} ({pairDetails?.base_coin_code})
          </p>
          <p className=" tradex-text-body tradex-font-medium tradex-text-xs tradex-leading-5 tradex-text-end">
            {t("Time")}
          </p>
        </div>
        <div className=" tradex-max-h-[140px] tradex-overflow-y-auto">
          {futureTrades?.map((item, index) => (
            <TradeItem key={index} item={item} />
          ))}
        </div>
      </div>
    </div>
  );
}

const TradeItem = ({ item }: { item: FutureTradeItem }) => {
  const isUpPrice = Number(item?.price) >= Number(item?.previous_price);

  return (
    <div className=" tradex-grid tradex-grid-cols-3 tradex-mb-1 last:tradex-mb-0">
      <p
        className={cn(
          "tradex-font-semibold tradex-text-[11px] tradex-leading-4",
          isUpPrice
            ? "tradex-text-future-trade-green"
            : "tradex-text-future-trade-red",
        )}
      >
        {item?.price}
      </p>
      <p className=" tradex-text-body tradex-font-semibold tradex-text-[11px] tradex-leading-4">
        {item?.amount}
      </p>
      <p className=" tradex-text-body tradex-font-semibold tradex-text-[11px] tradex-leading-4 tradex-text-end">
        {moment(item.created_at).format("HH:mm:ss")}
      </p>
    </div>
  );
};
