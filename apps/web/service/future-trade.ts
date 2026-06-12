import { FUTURE_MARGIN_MODE } from "helpers/core-constants";
import { toLocalISOString } from "helpers/functions";
import request from "lib/futureTradeRequest";
import { FuturesOrderPayloadRaw } from "state/actions/future";

export const getFutureDefaultCoinPair = async () => {
  const data = await request.get(`/default-coin-pair`);
  return data;
};

export const getFutureCoinPairDetails = async (code: string) => {
  try {
    const data = await request.get(`/coin-pair-details?code=${code}`);
    return data;
  } catch (error) {
    return {
      data: {
        success: false,
        message: "Something Went Wrong",
        data: [],
      },
    };
  }
};

export const getFutureCoinPairs = async () => {
  const data = await request.get(`/coin-pairs`);
  return data;
};

export const futureOrderApi = async (value: FuturesOrderPayloadRaw) => {
  const { data } = await request.post(`/order`, {
    ...value,
  });
  return data;
};

export const getFutureUserMarginLeverage = async (coinPairUid: string) => {
  const data = await request.get(
    `/user-margin-leverage?coin_pair_uid=${coinPairUid}`,
  );
  return data;
};

export type FutureMarginSettingPayloadRaw = {
  coin_pair_uid: string;
  margin_mode: FUTURE_MARGIN_MODE;
};

export const futureMarginSettingApi = async (
  value: FutureMarginSettingPayloadRaw,
) => {
  const data = await request.post(`/margin-setting-update`, {
    ...value,
  });
  return data;
};

export const getFutureAllLeverageSettingsApi = async (coinPairUid: string) => {
  const data = await request.get(
    `/get-all-leverage-settings?coin_pair_uid=${coinPairUid}`,
  );
  return data;
};

export const getFutureLeverageSettingDataApi = async (
  coinPairUid: string,
  leverage: number = 1,
) => {
  const data = await request.get(
    `/leverage-setting-data?coin_pair_uid=${coinPairUid}&leverage=${leverage}`,
  );
  return data;
};

export type FutureLeverageSettingUpdatePayloadRaw = {
  coin_pair_uid: string;
  leverage: number;
};

export const futureLeverageSettingUpdateApi = async (
  value: FutureLeverageSettingUpdatePayloadRaw,
) => {
  const data = await request.post(`/leverage-setting-update`, {
    ...value,
  });
  return data;
};

export const getFutureWalletDetailsApi = async () => {
  const data = await request.get(`/wallet-details`);
  return data;
};

export const getFutureOpenOrderAndPositionAmountApi = async (
  coin_pair_uid: string,
) => {
  const data = await request.get(
    `/open-order-and-position-amount?coin_pair_uid=${coin_pair_uid}`,
  );
  return data;
};

export const getFutureOrderbook = async (coin_pair_uid: string) => {
  try {
    const data = await request.get(`/orderbook?coin_pair_uid=${coin_pair_uid}`);
    return data;
  } catch (error) {
    return {
      data: {
        success: false,
        message: "Something Went Wrong",
        data: [],
      },
    };
  }
};

export const getFutureMarketTradeList = async (coin_pair_uid: string) => {
  try {
    const data = await request.get(`/market-trade-list/${coin_pair_uid}`);
    return data;
  } catch (error) {
    return {
      data: {
        success: false,
        message: "Something Went Wrong",
        data: [],
      },
    };
  }
};

export const getFutureOpenOrdersApi = async (
  page: number = 1,
  limit: number = 10,
  side?: number,
  order_method?: number,
  symbol?: string,
) => {
  const params: Record<string, string> = {
    page: page.toString(),
    limit: limit.toString(),
    ...(side !== undefined && { side: side.toString() }),
    ...(order_method !== undefined && {
      order_method: order_method.toString(),
    }),
    ...(symbol && { symbol }),
  };

  const query = new URLSearchParams(params).toString();
  const data = await request.get(`/open-order-list?${query}`);
  return data;
};

export const getFutureOrderHistoryListsApi = async (
  page: number = 1,
  limit: number = 10,
  side?: number,
  order_method?: number,
  symbol?: string,
  time_from?: Date,
  time_to?: Date,
) => {
  const params: Record<string, string> = {
    page: page.toString(),
    limit: limit.toString(),
    ...(side !== undefined && { side: side.toString() }),
    ...(order_method !== undefined && {
      order_method: order_method.toString(),
    }),
    ...(symbol && { symbol }),
    ...(time_from && { time_from: toLocalISOString(time_from) }),
    ...(time_to && { time_to: toLocalISOString(time_to) }),
  };

  const query = new URLSearchParams(params).toString();
  const data = await request.get(`/order/history?${query}`);
  return data;
};

export const getFutureTradeHistoryListsApi = async (
  page: number = 1,
  limit: number = 10,
  side?: number,
  symbol?: string,
  time_from?: Date,
  time_to?: Date,
) => {
  const params: Record<string, string> = {
    page: page.toString(),
    limit: limit.toString(),
    ...(side !== undefined && { side: side.toString() }),

    ...(symbol && { symbol }),
    ...(time_from && { time_from: toLocalISOString(time_from) }),
    ...(time_to && { time_to: toLocalISOString(time_to) }),
  };

  const query = new URLSearchParams(params).toString();
  const data = await request.get(`/trade/history?${query}`);
  return data;
};

export const getFuturePositionHistoryListsApi = async (
  page: number = 1,
  limit: number = 10,
  symbol?: string,
  time_from?: Date,
  time_to?: Date,
) => {
  const params: Record<string, string> = {
    page: page.toString(),
    limit: limit.toString(),

    ...(symbol && { symbol }),
    ...(time_from && { time_from: toLocalISOString(time_from) }),
    ...(time_to && { time_to: toLocalISOString(time_to) }),
  };

  const query = new URLSearchParams(params).toString();
  const data = await request.get(`/position/my-positions-history?${query}`);
  return data;
};

export const getFutureUserAssetListsApi = async () => {
  const data = await request.get(`/user-assets`);
  return data;
};

export const getFutureUserMarginLeverageApi = async (coinPairUid: string) => {
  const data = await request.get(
    `/position/user-margin-summary?coin_pair_uid=${coinPairUid}`,
  );
  return data;
};

export const getFutureUserPositionListsApi = async () => {
  const data = await request.get(`/position/my-positions`);
  return data;
};

type GetFuturesTradeCandlesParams = {
  coin_pair_uid: string;
  from: number;
  to: number;
  interval: string;
};
export const getFuturesTradeCandles = async ({
  coin_pair_uid,
  from,
  to,
  interval,
}: GetFuturesTradeCandlesParams) => {
  try {
    const params: Record<string, string> = {
      ...(coin_pair_uid && { coin_pair_uid }),
      ...(interval && { interval }),
      ...(from && { from: from.toString() }),
      ...(to && { to: to.toString() }),
    };

    const query = new URLSearchParams(params).toString();

    const { data } = await request.get(`/get-candles?${query}`);

    return data?.data;
  } catch (error) {
    return {
      data: {
        success: false,
        message: "Something Went Wrong",
        data: [],
      },
    };
  }
};

export interface FutureUserBalance {
  available_balance: string;
  coin_type: string;
  in_order_balance: string;
  today_pnl: string;
  total_balance: string;
}

export const getFutureWalletDetailsBalanceApi = async () => {
  const data = await request.get(`/wallet-details-balance`);
  return data;
};

export interface FutureWalletBalance {
  wallet_id: number;
  coin_type: string;
  available_balance: string;
  available_balance_btc: string;
  in_order_balance: string;
  today_pnl: string;
  total_balance: string;
  total_balance_btc: string;
}

export const getUserAllFutureWalletApi = async () => {
  const data = await request.get(`/user-all-future-wallet`);
  return data;
};

export type PositionsTPSLFormPayloadRaw = {
  position_uid: string;
  tp_price?: number;
  sl_price?: number;
};

export const futurePositionsTPSLApi = async (
  value: PositionsTPSLFormPayloadRaw,
) => {
  const { data } = await request.post(`/position/margin-tpsl-update`, {
    ...value,
  });
  return data;
};

export type PositionsTPSLCancelPayloadRaw = {
  position_uid: string;
  type: number;
};

export const futurePositionsTPSLCancelApi = async (
  value: PositionsTPSLCancelPayloadRaw,
) => {
  const { data } = await request.post(`/position/cancel-position-tpsl`, {
    ...value,
  });
  return data;
};

export type CancelOpenOrderPayloadRaw = {
  order_uid: string;
  order_type: number;
};

export const futureCancelOpenOrderApi = async (
  value: CancelOpenOrderPayloadRaw,
) => {
  const { data } = await request.post(`/order-cancel`, {
    ...value,
  });
  return data;
};

export type FuturesPositionClosePayloadRaw = {
  coin_pair_uid: string;
  amount?: number;
  price?: number;
  order_type: number;
  margin_mode: number;
  is_reduce: number;
  order_method: number;
};

export const futurePositionCloseApi = async (
  value: FuturesPositionClosePayloadRaw,
) => {
  const { data } = await request.post(`/order`, {
    ...value,
  });
  return data;
};

export const getFutureUserAsstesSummaryApi = async (coinPairUid: string) => {
  const data = await request.get(
    `/position/user-assets-summary?coin_pair_uid=${coinPairUid}`,
  );
  return data;
};

export type PositionsAdjustMarginFormPayloadRaw = {
  coin_pair_uid: string;
  amount: number;
  action: number;
};

export const futurePositionsAdjustMarginApi = async (
  value: PositionsAdjustMarginFormPayloadRaw,
) => {
  const { data } = await request.post(`/position/margin-update`, {
    ...value,
  });
  return data;
};

export const getFutureMaxLeverageApi = async (coinPairUid: string) => {
  const data = await request.get(`/max-leverage?coin_pair_uid=${coinPairUid}`);
  return data;
};
