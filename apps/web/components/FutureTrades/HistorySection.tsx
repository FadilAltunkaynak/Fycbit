import { cn } from "helpers/functions";
import useTranslation from "next-translate/useTranslation";
import Link from "next/link";
import React, { useState } from "react";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
import FuturePositions from "./FuturePositions";
import FutureOpenOrders from "./FutureOpenOrders";
import FutureOrderHistory from "./FutureOrderHistory";
import FutureTradeHistory from "./FutureTradeHistory";
import FuturePositionHistory from "./FuturePositionHistory";
import FutureAssetsTable from "./FutureAssetsTable";

export default function HistorySection() {
  const { t } = useTranslation("common");
  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const FUTURES_TABS = [
    { label: t("Positions"), value: "positions" },
    { label: t("Open Orders"), value: "open_orders" },
    { label: t("Order History"), value: "order_history" },
    { label: t("Trade History"), value: "trade_history" },
    { label: t("Position History"), value: "position_history" },
    { label: t("Assets"), value: "assets" },
  ];

  const [activeTab, setActiveTab] = useState(FUTURES_TABS[0].value);

  const { futurePositions } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  const TAB_COMPONENT: Record<string, React.ReactNode> = {
    positions: <FuturePositions />,
    open_orders: <FutureOpenOrders />,
    order_history: <FutureOrderHistory />,
    trade_history: <FutureTradeHistory />,
    position_history: <FuturePositionHistory />,
    assets: <FutureAssetsTable />,
  };

  const postionLength = isLoggedIn ? futurePositions?.length : 0;

  return (
    <div className=" tradex-flex-1 tradex-bg-background-main tradex-rounded tradex-p-2 tradex-overflow-x-auto scroll-container-x">
      <div className=" tradex-flex tradex-justify-between tradex-items-center tradex-overflow-x-auto scroll-container-x tradex-gap-6">
        <div className=" tradex-flex tradex-gap-3 tradex-items-center tradex-overflow-x-auto scroll-container-x tradex-flex-nowrap">
          {FUTURES_TABS.map((item, index) => (
            <button
              key={index}
              onClick={() => setActiveTab(item.value)}
              className={cn(
                " tradex-text-sm tradex-leading-7 tradex-font-bold  tradex-whitespace-nowrap",
                item.value === activeTab
                  ? "tradex-text-primary"
                  : "tradex-text-title",
              )}
            >
              {item.label} {item.value === "positions" && `(${postionLength})`}
            </button>
          ))}
        </div>
      </div>

      <div className=" tradex-mt-3 tradex-w-full tradex-overflow-x-auto scroll-container-x">
        {!isLoggedIn ? (
          <div className=" tradex-my-10 tradex-flex tradex-justify-center tradex-items-center">
            <p>
              {" "}
              <Link href={"/login"}>
                <span className=" tradex-text-primary">{t("Login")}</span>
              </Link>{" "}
              {t("Or")}{" "}
              <Link href={"/signup"}>
                <span className=" tradex-text-primary">{t("Sign Up")}</span>
              </Link>{" "}
              {t("to trade")}
            </p>
          </div>
        ) : (
          TAB_COMPONENT[activeTab]
        )}
      </div>
    </div>
  );
}
