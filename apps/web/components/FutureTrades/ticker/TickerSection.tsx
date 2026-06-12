import React from "react";
import { TickerItem } from "./TickerItem";
import { BsArrowDown, BsArrowUp } from "react-icons/bs";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
import { formatCurrency } from "common";
import { cn } from "helpers/functions";
import FuturePairSection from "./FuturePairSection";
import useTranslation from "next-translate/useTranslation";

export default function TickerSection() {
  const { t } = useTranslation("common");

  const { pairDetails } = useSelector((state: RootState) => state.futureTrade);

  const isUpPrice =
    Number(pairDetails?.market_price) >= Number(pairDetails?.previous_price);

  return (
    <div className="tradex-flex tradex-flex-wrap tradex-items-center tradex-gap-4 tradex-px-4 tradex-py-4 tradex-rounded tradex-bg-background-main tradex-min-h-[60px]">
      <FuturePairSection />
      <TickerItem>
        <TickerItem.Value
          className={cn(
            isUpPrice
              ? " tradex-text-future-trade-green"
              : "tradex-text-future-trade-red",
          )}
          iconClassName={cn(
            isUpPrice
              ? " tradex-text-future-trade-green"
              : "tradex-text-future-trade-red",
          )}
          icon={isUpPrice ? <BsArrowUp size={18} /> : <BsArrowDown size={18} />}
        >
          {formatCurrency(
            Number(pairDetails?.market_price),
            pairDetails?.trade_decimal,
          )}
        </TickerItem.Value>

        <TickerItem.SubValue>
          USD{" "}
          {formatCurrency(
            Number(pairDetails?.market_price),
            pairDetails?.trade_decimal,
          )}
        </TickerItem.SubValue>
      </TickerItem>

      <TickerItem>
        <TickerItem.Title className="tradex-border-b tradex-border-dashed tradex-border-body">
          {t("Mark")}
        </TickerItem.Title>
        <TickerItem.SubValue>
          {formatCurrency(
            Number(pairDetails?.mark_price),
            pairDetails?.trade_decimal,
          )}
        </TickerItem.SubValue>
      </TickerItem>
      <TickerItem>
        <TickerItem.Title>{t("Index")}</TickerItem.Title>
        <TickerItem.SubValue>
          {formatCurrency(
            Number(pairDetails?.index_price),
            pairDetails?.trade_decimal,
          )}
        </TickerItem.SubValue>
      </TickerItem>

      <TickerItem>
        <TickerItem.Title>24h {t("Change")}</TickerItem.Title>
        <TickerItem.SubValue
          className={cn(
            Number(pairDetails?.change || 0) >= 0
              ? " tradex-text-future-trade-green"
              : "tradex-text-future-trade-red",
          )}
        >
          {Number(pairDetails?.change)}%
        </TickerItem.SubValue>
      </TickerItem>
      <TickerItem>
        <TickerItem.Title>24h {t("High")}</TickerItem.Title>
        <TickerItem.SubValue>
          {formatCurrency(
            Number(pairDetails?.high),
            pairDetails?.trade_decimal,
          )}
        </TickerItem.SubValue>
      </TickerItem>
      <TickerItem>
        <TickerItem.Title>24h {t("Low")}</TickerItem.Title>
        <TickerItem.SubValue>
          {formatCurrency(Number(pairDetails?.low), pairDetails?.trade_decimal)}
        </TickerItem.SubValue>
      </TickerItem>
      <TickerItem>
        <TickerItem.Title>
          24h {t("Volume")}({pairDetails?.base_coin_code})
        </TickerItem.Title>
        <TickerItem.SubValue>
          {formatCurrency(
            Number(pairDetails?.base_volume),
            pairDetails?.base_decimal,
          )}
        </TickerItem.SubValue>
      </TickerItem>
      <TickerItem>
        <TickerItem.Title>
          24h {t("Volume")}({pairDetails?.trade_coin_code})
        </TickerItem.Title>
        <TickerItem.SubValue>
          {formatCurrency(
            Number(pairDetails?.trade_volume),
            pairDetails?.trade_decimal,
          )}
        </TickerItem.SubValue>
      </TickerItem>
    </div>
  );
}
