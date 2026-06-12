import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { FUTURE_MARGIN_MODE } from "helpers/core-constants";

export interface FutureOpenOrder {
  uid: string;
  amount: string;
  price: string;
  base_coin_code: string;
  trade_coin_code: string;
  side: number;
  type: number;
  reduce_only: number;
  tp_price: string;
  sl_price: string;
  trigger_conditions: string;
  filed: string;
  symbol: string | null;
  created_at: string;
}

export interface FuturePosition {
  uid: string;
  coin_pair_uid: string;
  code: string;

  amount: string;
  price: string;
  mark_price: string;
  liquidation_price: string;

  margin: string;
  pnl: string;
  roi: string;
  ratio: string;

  sl_price: string;
  tp_price: string;

  leverage: number;
  margin_mode: number;
  order_type: number;
  base_coin_code: string;
  trade_coin_code: string;
  base_decimal: number;
  trade_decimal: number;
}

export interface FutureOrderHistory {
  uid: string;

  symbol: string | null;

  base_coin_code: string;
  trade_coin_code: string;

  amount: string;
  executed: string;
  price: string;

  tp_price: string;
  sl_price: string;

  trigger_conditions: string;

  reduce_only: number;
  side: number;
  status: number;
  type: number;

  created_at: string;
}

export interface FuturePositionHistory {
  symbol: string;
  price: string;
  amount: string;
  status: number;
  created_at: string;
}

export interface FutureTradeHistory {
  uid: string;

  side: number;

  price: string;
  amount: string;
  total_price: string;

  taker_fees: string;
  maker_fees: string;

  pnl: number;

  role: number;

  code: string;
  base_coin_code: string;
  trade_coin_code: string;

  created_at: string;
}

export interface FUTURES_ORDER_ITEM {
  uid: number;
  price: number;
  amount: number;
  total: number;
  percent: number;
}

export interface FutureCoinPair {
  id: number;
  uid: string;
  code: string;

  base_coin_code: string;
  trade_coin_code: string;

  base_coin_id: number;
  trade_coin_id: number;

  base_decimal: number;
  trade_decimal: number;

  status: number;

  min_amount: string;
  max_amount: string;

  maker_fees_percent: string;
  taker_fees_percent: string;

  min_stop_limit_percent: string;
  max_stop_limit_percent: string;

  floor_ratio: string;
  cap_ratio: string;

  leverage: string;
  max_leverage: string;

  max_open_orders: string;

  funding_rate: string;
  funding_next_time: string;

  slippage_percent: string;

  previous_price: string;
  market_price: string;
  mark_price: string;
  index_price: string;
  change: string;
  high: string;
  low: string;

  base_volume: string;
  trade_volume: string;
}

export interface FutureUserMargin {
  maintenance_margin: string;
  margin_balance: string;
  margin_ratio: string;
  wallet_balance: string;
}

export interface FutureUserAsstesSummary {
  balance: string;
  trade_coin_code: string;
  unrealized_pnl: string;
}

export interface FutureTradeItem {
  created_at: string;
  price: string;
  amount: string;
  previous_price: string;
  code: string;
}

export interface FuturePairItem {
  uid: string;
  code: string;
  market_price: string;
  previous_price: string;
  high: string;
  low: string;
  change: string;
  volume: string;
}

export interface FutureOrderBookItem {
  price: number;
  pending_amount: number;
}

export type FuturesOrderBook = {
  buy: FutureOrderBookItem[];
  sell: FutureOrderBookItem[];
};

export interface FutureOrderTotalAmount {
  open_buy_total_amount: number;
  open_sell_total_amount: number;
  open_positions_total_amount: number;
}

export type FutureWallet = {
  available_balance: number;
  balance: number;
  cost: number;
};

export type FutureLeverageSettings = {
  id: number;
  uid: string;

  coin_pair_uid: string;

  max_leverage: number;

  maintenance_amount: number;
  maintenance_margin_rate: number;

  max_position_amount: number;
  min_position_amount: number;

  created_at: string;
  updated_at: string;
};

export interface FutureUserAsset {
  coin: string;
  available_balance: string;
  in_order_cost: string;
  total: string;
  btc: string;
  btc_to_currency: string;
}

type FutureState = {
  currentPair: string;
  basePrice: number | null;
  pairDetails: FutureCoinPair | null;
  futurePairs: FuturePairItem[];
  leverage: number | null;
  margin_mode: FUTURE_MARGIN_MODE | null;
  future_wallet: FutureWallet;
  leverageSettings: FutureLeverageSettings[];
  future_order_total_amount: FutureOrderTotalAmount;
  futureOrderBook: FuturesOrderBook;
  futureOpenOrders: FutureOpenOrder[];
  futureOrderHistory: FutureOrderHistory[];
  futureTradeHistory: FutureTradeHistory[];
  futureMargin: FutureUserMargin;
  futurePositionHistory: FuturePositionHistory[];
  futureUserAssets: FutureUserAsset;
  futureTrades: FutureTradeItem[];
  futurePositions: FuturePosition[];
  futureUserAsstesSummary: FutureUserAsstesSummary;
};

const initialState: FutureState = {
  currentPair: "",
  pairDetails: null,
  futurePairs: [],
  basePrice: null,
  leverage: 1,
  margin_mode: FUTURE_MARGIN_MODE.CROSS,
  future_wallet: {
    available_balance: 0,
    balance: 0,
    cost: 0,
  },
  leverageSettings: [],
  future_order_total_amount: {
    open_buy_total_amount: 0,
    open_sell_total_amount: 0,
    open_positions_total_amount: 0,
  },
  futureOrderBook: {
    buy: [],
    sell: [],
  },
  futureOpenOrders: [],
  futureOrderHistory: [],
  futureTradeHistory: [],
  futureMargin: {
    maintenance_margin: "0",
    margin_balance: "0",
    margin_ratio: "0",
    wallet_balance: "0",
  },
  futurePositionHistory: [],
  futureUserAssets: {
    coin: "N/A",
    available_balance: "0",
    in_order_cost: "0",
    total: "0",
    btc: "0",
    btc_to_currency: "N/A",
  },
  futureTrades: [],
  futurePositions: [],
  futureUserAsstesSummary: {
    balance: "0.00",
    trade_coin_code: "USDT",
    unrealized_pnl: "0.00",
  },
};

export const futureReducer = createSlice({
  name: "futureTrade",
  initialState,
  reducers: {
    setFutureCurrentPair(state, action: PayloadAction<string>) {
      state.currentPair = action.payload;
    },

    setFuturePairDetails(state, action: PayloadAction<FutureCoinPair>) {
      state.pairDetails = action.payload;
    },

    updateFuturePairDetails(
      state,
      action: PayloadAction<Partial<FutureCoinPair>>,
    ) {
      if (!state.pairDetails) return;
      state.pairDetails = {
        ...state.pairDetails,
        ...action.payload,
      };
    },

    setFuturePairs(state, action: PayloadAction<FuturePairItem[]>) {
      state.futurePairs = action.payload;
    },

    setFuturesBasePrice(state, action: PayloadAction<number | null>) {
      state.basePrice = action.payload;
    },

    updateFuturePairs(
      state,
      action: PayloadAction<Partial<FuturePairItem> & { code: string }>,
    ) {
      const index = state.futurePairs.findIndex(
        (t) => t.code === action.payload.code,
      );

      if (index !== -1) {
        state.futurePairs[index] = {
          ...state.futurePairs[index],
          ...action.payload,
        };
      } else {
        state.futurePairs.push(action.payload as FuturePairItem);
      }
    },
    setFuturePairLeverage(state, action: PayloadAction<number>) {
      state.leverage = action.payload;
    },
    setFuturePairMarginMode(state, action: PayloadAction<FUTURE_MARGIN_MODE>) {
      state.margin_mode = action.payload;
    },
    setFutureWallet(state, action: PayloadAction<FutureWallet>) {
      state.future_wallet = action.payload;
    },
    updateFutureWallet(state, action: PayloadAction<Partial<FutureWallet>>) {
      state.future_wallet = {
        ...state.future_wallet,
        ...action.payload,
      };
    },
    setFutureLeverageSettings(
      state,
      action: PayloadAction<FutureLeverageSettings[]>,
    ) {
      state.leverageSettings = action.payload;
    },

    setFutureOrderTotalAmount(
      state,
      action: PayloadAction<FutureOrderTotalAmount>,
    ) {
      state.future_order_total_amount = action.payload;
    },
    updateFutureOrderTotalAmount(
      state,
      action: PayloadAction<Partial<FutureOrderTotalAmount>>,
    ) {
      state.future_order_total_amount = {
        ...state.future_order_total_amount,
        ...action.payload,
      };
    },
    setFutureOrderBook: (state, action: PayloadAction<FuturesOrderBook>) => {
      state.futureOrderBook = action.payload;
    },

    updateFutureOrderLevel: (
      state,
      action: PayloadAction<{
        side: "buy" | "sell";
        data: FutureOrderBookItem;
      }>,
    ) => {
      const { side, data } = action.payload;

      const levels = state.futureOrderBook[side];
      const price = Number(data.price);
      const amount = Number(data.pending_amount);

      let left = 0;
      let right = levels.length - 1;
      let index = -1;

      while (left <= right) {
        const mid = Math.floor((left + right) / 2);
        const midPrice = Number(levels[mid].price);

        if (midPrice === price) {
          index = mid;
          break;
        }

        if (side === "buy") {
          if (price > midPrice) right = mid - 1;
          else left = mid + 1;
        } else {
          if (price < midPrice) right = mid - 1;
          else left = mid + 1;
        }
      }

      if (amount === 0) {
        if (index !== -1) {
          levels.splice(index, 1);
        }
        return;
      }

      if (index !== -1) {
        levels[index] = data;
        return;
      }

      levels.splice(left, 0, data);
    },

    setFutureOpenOrders(state, action: PayloadAction<FutureOpenOrder[]>) {
      state.futureOpenOrders = action.payload;
    },

    addFutureOpenOrder(state, action: PayloadAction<FutureOpenOrder>) {
      state.futureOpenOrders.unshift(action.payload);
    },

    updateFutureOpenOrder(state, action: PayloadAction<FutureOpenOrder>) {
      const index = state.futureOpenOrders.findIndex(
        (order) => order.uid === action.payload.uid,
      );

      if (index !== -1) {
        state.futureOpenOrders[index] = action.payload;
      }
    },

    removeFutureOpenOrder(state, action: PayloadAction<string>) {
      state.futureOpenOrders = state.futureOpenOrders.filter(
        (order) => order.uid !== action.payload,
      );
    },

    setFutureOrderHistories(
      state,
      action: PayloadAction<FutureOrderHistory[]>,
    ) {
      state.futureOrderHistory = action.payload;
    },

    addFutureOrderHistory(state, action: PayloadAction<FutureOrderHistory>) {
      state.futureOrderHistory.unshift(action.payload);
    },

    updateFutureOrderHistory(state, action: PayloadAction<FutureOrderHistory>) {
      const index = state.futureOrderHistory.findIndex(
        (order) => order.uid === action.payload.uid,
      );

      if (index !== -1) {
        state.futureOrderHistory[index] = action.payload;
      }
    },

    removeFutureOrderHistory(state, action: PayloadAction<string>) {
      state.futureOrderHistory = state.futureOrderHistory.filter(
        (order) => order.uid !== action.payload,
      );
    },

    setFutureTradeHistories(
      state,
      action: PayloadAction<FutureTradeHistory[]>,
    ) {
      state.futureTradeHistory = action.payload;
    },

    addFutureTradeHistory(state, action: PayloadAction<FutureTradeHistory>) {
      state.futureTradeHistory.unshift(action.payload);
    },

    updateFutureTradeHistory(state, action: PayloadAction<FutureTradeHistory>) {
      const index = state.futureTradeHistory.findIndex(
        (order) => order.uid === action.payload.uid,
      );

      if (index !== -1) {
        state.futureTradeHistory[index] = action.payload;
      }
    },

    removeFutureTradeHistory(state, action: PayloadAction<string>) {
      state.futureTradeHistory = state.futureTradeHistory.filter(
        (order) => order.uid !== action.payload,
      );
    },

    setFutureMargin: (state, action: PayloadAction<FutureUserMargin>) => {
      state.futureMargin = action.payload;
    },
    updateFutureMargin: (
      state,
      action: PayloadAction<Partial<FutureUserMargin>>,
    ) => {
      state.futureMargin = { ...state.futureMargin, ...action.payload };
    },

    setFuturePositionHistories(
      state,
      action: PayloadAction<FuturePositionHistory[]>,
    ) {
      state.futurePositionHistory = action.payload;
    },

    addFuturePositionHistory(
      state,
      action: PayloadAction<FuturePositionHistory>,
    ) {
      state.futurePositionHistory.unshift(action.payload);
    },

    setFutureAssets: (state, action: PayloadAction<FutureUserAsset>) => {
      state.futureUserAssets = action.payload;
    },

    updateFutureAssets: (
      state,
      action: PayloadAction<Partial<FutureUserAsset>>,
    ) => {
      state.futureUserAssets = { ...state.futureUserAssets, ...action.payload };
    },

    setFutureTrades(state, action: PayloadAction<FutureTradeItem[]>) {
      state.futureTrades = action.payload;
    },
    addFutureTrade: (state, action: PayloadAction<FutureTradeItem>) => {
      state.futureTrades.unshift(action.payload);
      if (state.futureTrades.length > 100) {
        state.futureTrades.pop();
      }
    },

    setFuturePositions(state, action: PayloadAction<FuturePosition[]>) {
      state.futurePositions = action.payload;
    },

    addFuturePosition(state, action: PayloadAction<FuturePosition>) {
      state.futurePositions.unshift(action.payload);
    },

    updateFuturePosition(state, action: PayloadAction<FuturePosition>) {
      const incoming = action.payload;

      const index = state.futurePositions.findIndex(
        (pos) => pos.uid === incoming.uid,
      );

      const amount = Number(incoming.amount);

      if (amount === 0) {
        if (index !== -1) {
          state.futurePositions.splice(index, 1);
        }
        return;
      }

      if (index !== -1) {
        state.futurePositions[index] = incoming;
      } else {
        state.futurePositions.push(incoming);
      }
    },

    removeFuturePosition(state, action: PayloadAction<string>) {
      state.futurePositions = state.futurePositions.filter(
        (order) => order.uid !== action.payload,
      );
    },

    setFutureUserAsstesSummary: (
      state,
      action: PayloadAction<FutureUserAsstesSummary>,
    ) => {
      state.futureUserAsstesSummary = action.payload;
    },
    updateFutureUserAsstesSummary: (
      state,
      action: PayloadAction<Partial<FutureUserAsstesSummary>>,
    ) => {
      state.futureUserAsstesSummary = {
        ...state.futureUserAsstesSummary,
        ...action.payload,
      };
    },
  },
});

export const {
  setFutureCurrentPair,
  setFuturePairDetails,
  updateFuturePairDetails,
  setFuturePairs,
  updateFuturePairs,
  setFutureLeverageSettings,
  setFuturePairLeverage,
  setFuturePairMarginMode,
  setFutureWallet,
  updateFutureWallet,
  setFutureOrderTotalAmount,
  updateFutureOrderTotalAmount,
  setFutureOrderBook,
  updateFutureOrderLevel,

  setFutureOpenOrders,
  addFutureOpenOrder,
  updateFutureOpenOrder,
  removeFutureOpenOrder,

  setFutureOrderHistories,
  addFutureOrderHistory,
  updateFutureOrderHistory,
  removeFutureOrderHistory,

  setFutureTradeHistories,
  addFutureTradeHistory,
  updateFutureTradeHistory,
  removeFutureTradeHistory,

  setFutureMargin,
  updateFutureMargin,

  setFuturePositionHistories,
  addFuturePositionHistory,

  setFutureAssets,
  updateFutureAssets,

  setFutureTrades,
  addFutureTrade,

  setFuturesBasePrice,

  setFuturePositions,
  addFuturePosition,
  updateFuturePosition,
  removeFuturePosition,

  setFutureUserAsstesSummary,
  updateFutureUserAsstesSummary,
} = futureReducer.actions;

export default futureReducer.reducer;
