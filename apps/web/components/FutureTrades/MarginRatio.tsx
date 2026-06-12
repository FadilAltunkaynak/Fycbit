import { formatAmountDecimal } from "helpers/functions";
import useTranslation from "next-translate/useTranslation";
import dynamic from "next/dynamic";
import Tooltip from "rc-tooltip";
import React from "react";
import { useSelector } from "react-redux";
import { useGetFutureUserMarginLeverage } from "state/actions/future";
import { RootState } from "state/store";

const ReactSpeedometer = dynamic(() => import("react-d3-speedometer"), {
  ssr: false,
});

export default function MarginRatio() {
  const { t } = useTranslation("common");

  const { pairDetails, futureMargin } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  const { loading } = useGetFutureUserMarginLeverage();

  const tradeDecimal = pairDetails?.trade_decimal || 2;

  const { maintenance_margin, margin_balance, margin_ratio } = futureMargin;
  return (
    <div className="tradex-flex-1 tradex-bg-background-main tradex-rounded tradex-p-2">
      <p className=" tradex-text-title tradex-font-bold tradex-text-sm">
        {t("Margin")}
      </p>
      <div className=" tradex-flex tradex-justify-center tradex-items-center">
        <Tooltip
          placement={"top"}
          overlay={
            <div className="">
              {t(
                "Margin Ratio = Maintenance Margin / Margin Balance. Your positions will be liquidated once Margin Ratio reaches 100%.",
              )}
            </div>
          }
          trigger={["hover"]}
          overlayClassName="tradex-max-w-[250px]"
        >
          <div>
            <ReactSpeedometer
              width={120}
              height={100}
              value={
                Number(margin_ratio) < 0
                  ? 0
                  : Number(margin_ratio) > 100
                  ? 100
                  : Number(margin_ratio)
              }
              maxValue={100}
              minValue={0}
              segments={3}
              segmentColors={["#58BD7D", "#FFD166", "#fa0000"]}
              ringWidth={6}
              needleHeightRatio={0.4}
              currentValueText={Number(margin_ratio).toFixed(4) + "%"}
              textColor={"#01BC8D"}
              valueTextFontSize="12px"
              labelFontSize={"0px"}
              customSegmentLabels={[{ text: "" }, { text: "" }, { text: "" }]}
            />
          </div>
        </Tooltip>
      </div>
      <div className=" tradex-space-y-0.5">
        <div className=" tradex-flex tradex-justify-between tradex-items-center">
          <Tooltip
            placement={"top"}
            overlay={
              <div className="">
                {t(
                  "The minimum amount of margin balance required to keep your open positions.",
                )}
              </div>
            }
            trigger={["hover"]}
            overlayClassName="tradex-max-w-[250px]"
          >
            <p className=" tradex-text-xs tradex-text-body tradex-leading-5">
              {t("Maintenance Margin")}
            </p>
          </Tooltip>

          <p className=" tradex-text-xs tradex-text-title tradex-leading-5 tradex-font-medium">
            {loading ? "---" : maintenance_margin}{" "}
            {pairDetails?.trade_coin_code}
          </p>
        </div>
        <div className=" tradex-flex tradex-justify-between tradex-items-center">
          <Tooltip
            placement={"top"}
            overlay={
              <div className="">
                {t(
                  `Margin Balance = Wallet Balance + Unrealized PNL. Your positions will be liquidated once Margin Balance <= Maintenance Margin.`,
                )}
              </div>
            }
            trigger={["hover"]}
            overlayClassName="tradex-max-w-[250px]"
          >
            <p className=" tradex-text-xs tradex-text-body tradex-leading-5">
              {t("Margin Balance")}
            </p>
          </Tooltip>

          <p className=" tradex-text-xs tradex-text-title tradex-leading-5 tradex-font-medium">
            {loading ? "---" : margin_balance} {pairDetails?.trade_coin_code}
          </p>
        </div>
      </div>
    </div>
  );
}
