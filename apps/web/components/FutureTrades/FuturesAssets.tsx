import { formatAmountDecimal } from "helpers/functions";
import useTranslation from "next-translate/useTranslation";
import Tooltip from "rc-tooltip";
import React from "react";
import { useSelector } from "react-redux";
import { useGetFutureUserAsstesSummary } from "state/actions/future";
import { RootState } from "state/store";

export default function FuturesAssets() {
  const { t } = useTranslation("common");

  const { futureUserAsstesSummary } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  const { loading } = useGetFutureUserAsstesSummary();

  return (
    <div className="tradex-flex-1 tradex-bg-background-main tradex-rounded tradex-p-2">
      <p className=" tradex-text-title tradex-font-bold tradex-text-sm">
        {t("Asset")}
      </p>
      <div className=" tradex-space-y-0.5 tradex-mt-3">
        <div className=" tradex-flex tradex-justify-between tradex-items-center">
          <Tooltip
            placement={"top"}
            overlay={
              <div className="">{t("Balance in your futures wallet.")}</div>
            }
            trigger={["hover"]}
            overlayClassName="tradex-max-w-[250px]"
          >
            <p className=" tradex-text-xs tradex-text-body tradex-leading-5">
              {t("Balance")}
            </p>
          </Tooltip>

          <p className=" tradex-text-xs tradex-text-title tradex-leading-5 tradex-font-medium">
            {loading ? (
              "----"
            ) : (
              <>
                {futureUserAsstesSummary?.balance}{" "}
                {futureUserAsstesSummary?.trade_coin_code}
              </>
            )}
          </p>
        </div>
        <div className=" tradex-flex tradex-justify-between tradex-items-center">
          <Tooltip
            placement={"top"}
            overlay={
              <div className="">
                {t(
                  "Unrealized profit & loss and Return on Equity (%) are calculated based on Mark Price.",
                )}
              </div>
            }
            trigger={["hover"]}
            overlayClassName="tradex-max-w-[250px]"
          >
            <p className=" tradex-text-xs tradex-text-body tradex-leading-5">
              {t("Unrealized PNL")}
            </p>
          </Tooltip>

          <p className=" tradex-text-xs tradex-text-title tradex-leading-5 tradex-font-medium">
            {loading ? (
              "----"
            ) : (
              <>
                {futureUserAsstesSummary?.unrealized_pnl}{" "}
                {futureUserAsstesSummary?.trade_coin_code}
              </>
            )}
          </p>
        </div>
      </div>
    </div>
  );
}
