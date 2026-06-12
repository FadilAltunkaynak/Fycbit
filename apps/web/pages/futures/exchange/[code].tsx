import FutureExchangeWrapper from "components/FutureTrades/FutureExchangeWrapper";
import { STATUS_ACTIVE } from "helpers/core-constants";
import { GetServerSideProps } from "next";
import React, { useEffect } from "react";
import { useDispatch } from "react-redux";
import { getModulesStatus } from "service/common";
import {
  getFutureCoinPairDetails,
  getFutureMarketTradeList,
  getFutureOrderbook,
} from "service/future-trade";
import {
  FutureCoinPair,
  FuturesOrderBook,
  FutureTradeItem,
  setFutureCurrentPair,
  setFutureOrderBook,
  setFuturePairDetails,
  setFutureTrades,
} from "state/reducer/futureReducer";

export default function FutureExchange({
  message,
  isServerError,
  details,
  orderbookData,
  trades,
}: {
  message?: string;
  isServerError?: boolean;
  details: FutureCoinPair;
  orderbookData: FuturesOrderBook;
  trades: FutureTradeItem[];
}) {
  const dispatch = useDispatch();

  useEffect(() => {
    if (details) {
      dispatch(setFutureCurrentPair(details?.code));
      dispatch(setFuturePairDetails(details));
      dispatch(setFutureOrderBook(orderbookData));
      dispatch(setFutureTrades(trades));
    }
  }, [details]);

  if (isServerError)
    return (
      <div className="container-dashboard container-fluid tradex-pt-3 tradex-space-y-2 tradex-min-h-[600px]">
        <p className=" tradex-m-10 tradex-py-6 tradex-text-center tradex-rounded-lg tradex-bg-red-100 tradex-text-red-700 tradex-text-lg tradex-font-bold">
          {message}
        </p>
      </div>
    );
  return <FutureExchangeWrapper />;
}

export const getServerSideProps: GetServerSideProps = async (ctx: any) => {
  const { data } = await getModulesStatus();

  const params = ctx.params;
  const code = String(params?.code ?? "");

  if (Number(data.future_trade) !== STATUS_ACTIVE || !code) {
    return {
      redirect: {
        destination: "/404",
        permanent: false,
      },
    };
  }

  const { data: coinPairDetails } = await getFutureCoinPairDetails(code);

  if (!coinPairDetails.success) {
    return {
      props: {
        message: coinPairDetails.message,
        isServerError: true,
        details: null,
      },
    };
  }

  const { data: orderbookData } = await getFutureOrderbook(
    coinPairDetails.data.uid,
  );

  const { data: marketTradeList } = await getFutureMarketTradeList(
    coinPairDetails.data.uid,
  );

  return {
    props: {
      details: coinPairDetails.data,
      orderbookData: orderbookData.data || { buy: [], sell: [] },
      trades: marketTradeList.data || [],
    },
  };
};
