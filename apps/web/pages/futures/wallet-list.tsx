import { SSRAuthCheck } from "middlewares/ssr-authentication-check";
import type { GetServerSideProps, NextPage } from "next";
import React from "react";
import useTranslation from "next-translate/useTranslation";
import Footer from "components/common/footer";
import WalletLayout from "components/wallet/WalletLayout";
import ReactDataTable from "components/ReactDataTable";

import {
  useFutureWalletDetailsBalance,
  useUserAllFutureWallet,
} from "state/actions/future";
import { cn } from "helpers/functions";
const WalletList: NextPage = () => {
  const { t } = useTranslation("common");

  const columns = [
    {
      Header: t("Coin"),

      Cell: ({ row }: any) => (
        <div className="tradex-flex tradex-items-center tradex-gap-2">
          <div className=" tradex-space-y-0.5">
            <p className=" tradex-text-sm tradex-leading-4 tradex-font-medium !tradex-text-title">
              {row.original?.coin_type}
            </p>
          </div>
        </div>
      ),
    },
    {
      Header: t("Total"),
      accessor: "total_balance",
      Cell: ({ cell }: any) => (
        <div>
          <span className="tradex-text-sm tradex-leading-4 tradex-font-medium !tradex-text-body">
            {Number(cell?.value)}
          </span>
        </div>
      ),
    },

    {
      Header: t("Available"),
      accessor: "available_balance",
      Cell: ({ cell }: any) => (
        <div>
          <span className="tradex-text-sm tradex-leading-4 tradex-font-medium !tradex-text-body">
            {Number(cell?.value)}
          </span>
        </div>
      ),
    },
    {
      Header: t("In Order"),
      accessor: "in_order_balance",
      Cell: ({ cell }: any) => (
        <div>
          <span className="tradex-text-sm tradex-leading-4 tradex-font-medium !tradex-text-body">
            {Number(cell?.value)}
          </span>
        </div>
      ),
    },
    {
      Header: t("BTC Value"),
      accessor: "total_balance_btc",
      Cell: ({ row }: any) => (
        <div>
          <p className="tradex-text-sm tradex-leading-4 tradex-font-medium !tradex-text-body">
            {Number(row.original?.total_balance_btc)}
          </p>
          <p className="tradex-text-sm tradex-leading-4 tradex-font-medium !tradex-text-body">
            = USD {Number(row.original?.total_balance)}
          </p>
        </div>
      ),
    },
    {
      Header: t("Today's PNL (USDT)"),
      accessor: "today_pnl",
      Cell: ({ cell }: any) => (
        <div>
          <span
            className={cn(
              "tradex-text-sm tradex-leading-4 tradex-font-medium ",
              Number(cell?.value) >= 0
                ? "!tradex-text-trade-green"
                : "!tradex-text-trade-red",
            )}
          >
            {Number(cell?.value)}
          </span>
        </div>
      ),
    },
  ];

  const { loading: isWalletDetailsBalanceLoading, walletBalance } =
    useFutureWalletDetailsBalance();

  const { loading: isUserAllFutureWalletLoading, userAllFutureWallet } =
    useUserAllFutureWallet();

  return (
    <div>
      <WalletLayout>
        <div className="tradex-bg-background-main tradex-rounded-lg tradex-border tradex-border-background-primary tradex-shadow-[2px_2px_23px_0px_#6C6C6C0D] tradex-px-4 tradex-pt-6 tradex-pb-12 tradex-space-y-6">
          <div className=" tradex-pb-4 tradex-border-b tradex-border-background-primary tradex-space-y-4">
            <h2 className=" tradex-text-[32px] tradex-leading-[38px] md:tradex-text-[40px] md:tradex-leading-[48px] tradex-font-bold !tradex-text-title">
              {t("Futures Wallet")}
            </h2>
            <div className=" tradex-flex tradex-flex-col md:tradex-flex-row tradex-gap-4 tradex-justify-between md:tradex-items-end">
              <div className=" tradex-space-y-2">
                <div className=" tradex-flex tradex-items-center tradex-gap-2">
                  <div className=" tradex-w-[11px] tradex-h-[11px] tradex-rounded-full tradex-bg-primary"></div>
                  <p className=" tradex-text-sm tradex-leading-4 tradex-font-bold tradex-text-primary">
                    {t("Futures balance")}
                  </p>
                </div>
                <h3 className=" tradex-text-xl tradex-leading-6 !tradex-text-title tradex-font-bold">
                  {isWalletDetailsBalanceLoading
                    ? "----"
                    : Number(walletBalance?.available_balance)}
                  {""}{" "}
                  {isWalletDetailsBalanceLoading
                    ? "----"
                    : walletBalance.coin_type}
                </h3>
                <p className=" tradex-text-sm tradex-text-body ">
                  {`Today's PNL`}:{" "}
                  <span
                    className={cn(
                      " tradex-font-semibold",
                      Number(walletBalance.today_pnl) >= 0
                        ? " tradex-text-trade-green"
                        : "tradex-text-trade-red",
                    )}
                  >
                    {" "}
                    {isWalletDetailsBalanceLoading
                      ? "---"
                      : `${walletBalance.today_pnl} ${walletBalance.coin_type}`}
                  </span>
                </p>
              </div>
            </div>
          </div>
          <div className=" tradex-space-y-6">
            <ReactDataTable
              columns={columns}
              data={userAllFutureWallet}
              processing={isUserAllFutureWalletLoading}
              isSearchable={false}
              isSortEnable={false}
            />
          </div>
        </div>
      </WalletLayout>

      <Footer />
    </div>
  );
};
export const getServerSideProps: GetServerSideProps = async (ctx: any) => {
  await SSRAuthCheck(ctx, "/wallet-list");

  return {
    props: {},
  };
};

export default WalletList;
