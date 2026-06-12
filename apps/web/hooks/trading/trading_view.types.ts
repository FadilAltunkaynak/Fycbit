import {
  Bar,
  ResolutionString,
  SubscribeBarsCallback,
} from "public/static/charting_library/charting_library";

export enum TRADING_VIEW_DEFAULTS {
  LIBRARY_PATH = "/static/charting_library/",
  CHARTS_STORAGE_URL = "https://saveload.tradingview.com",
  CHARTS_STORAGE_API_VERSION = "1.1",
  CLIENT_ID = "tradingview.com",
  USER_ID = "public_user_id",
  CONTAINER_ID_SPOT = "tv_chart_container",
  // INTERVAL = "1D",
  INTERVAL = "15", // 15m
  // CUSTOM_CSS = "/static/themed.css",
  CUSTOM_CSS = "css/style.css",

  TEST_SYMBOL = "BTC/USDT",
  PRESET_WIDTH = "1023",
}

export interface allSymbolType {
  symbol: string;
  full_name: string;
  description: string;
  exchange: string;
  type: string;
  ticker: string;
}

export type FuturesCandleData = {
  time: number;
  low: string;
  high: string;
  open: string;
  close: string;
  volume: string;
};

export interface SubHandler {
  id: string;
  callback: SubscribeBarsCallback;
}

export interface SubscribedPairData {
  subscriberUID: string;
  resolution: ResolutionString;
  lastCandleBar: Bar;
  handlers: SubHandler[];
}
