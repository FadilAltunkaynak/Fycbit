import React from "react";
import useTranslation from "next-translate/useTranslation";
import { useGetFutureUserAssetLists } from "state/actions/future";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
type Column = {
  label: string;
};

export default function FutureAssetsTable() {
  const { t } = useTranslation("common");
  const { futureUserAssets } = useSelector(
    (state: RootState) => state.futureTrade,
  );
  const { loading } = useGetFutureUserAssetLists();

  const FUTURE_ASSESTS_COLUMNS: Column[] = [
    { label: t("Coin") },
    { label: t("Total") },
    { label: t("Available") },
    { label: t("In Order") },
    { label: t("BTC Value") },
  ];
  return (
    <div>
      <div className="tradex-min-w-max">
        <table className=" tradex-w-full">
          <thead>
            <tr className=" tradex-border-b tradex-border-border">
              {FUTURE_ASSESTS_COLUMNS.map((col) => (
                <th
                  key={col.label}
                  className=" tradex-pr-4 tradex-whitespace-nowrap tradex-pb-1.5"
                >
                  <p className="tradex-text-xs tradex-text-body tradex-leading-5 tradex-font-semibold">
                    {col.label}
                  </p>
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            <tr>
              <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
                <p className=" tradex-text-xs tradex-text-title">
                  {futureUserAssets.coin}
                </p>
              </td>
              <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
                <p className=" tradex-text-xs tradex-text-title tradex-font-semibold">
                  {futureUserAssets.total}
                </p>
              </td>

              <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
                <p className=" tradex-text-xs tradex-text-title">
                  {futureUserAssets.available_balance}
                </p>
              </td>

              <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
                <p className=" tradex-text-xs tradex-text-title">
                  {futureUserAssets.in_order_cost}
                </p>
              </td>
              <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
                <div>
                  <p className=" tradex-text-xs tradex-text-title">
                    {futureUserAssets.btc}
                  </p>
                  <p className=" tradex-text-xs tradex-text-title">
                    = {futureUserAssets.btc_to_currency}{" "}
                    {futureUserAssets.total}
                  </p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
}
