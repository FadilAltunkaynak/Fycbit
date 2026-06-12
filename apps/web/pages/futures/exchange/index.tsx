import Footer from "components/common/footer";
import { STATUS_ACTIVE } from "helpers/core-constants";
import { GetServerSideProps } from "next";
import React from "react";
import { getModulesStatus } from "service/common";
import { getFutureDefaultCoinPair } from "service/future-trade";

export default function FutureExchange({ message }: { message: string }) {
  return (
    <>
      <div className="container-dashboard container-fluid tradex-pt-3 tradex-space-y-2 tradex-min-h-[600px]">
        <p className=" tradex-m-10 tradex-py-6 tradex-text-center tradex-rounded-lg tradex-bg-red-100 tradex-text-red-700 tradex-text-lg tradex-font-bold">
          {message}
        </p>
      </div>
      <Footer />
    </>
  );
}

export const getServerSideProps: GetServerSideProps = async (ctx: any) => {
  const { data } = await getModulesStatus();

  if (Number(data.future_trade) !== STATUS_ACTIVE) {
    return {
      redirect: {
        destination: "/404",
        permanent: false,
      },
    };
  }

  const { data: coinPairDetails } = await getFutureDefaultCoinPair();

  const code = coinPairDetails.data.code;

  if (coinPairDetails.success && code) {
    return {
      redirect: {
        destination: `/futures/exchange/${code}`,
        permanent: false,
      },
    };
  }

  return {
    props: {
      message: coinPairDetails.message,
    },
  };
};
