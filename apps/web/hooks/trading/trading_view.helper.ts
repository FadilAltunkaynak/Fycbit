import {
  AvailableSaveloadVersions,
  Bar,
  DatafeedConfiguration,
  LanguageCode,
  LibrarySymbolInfo,
  ResolutionString,
} from "public/static/charting_library/charting_library";
import {
  allSymbolType,
  FuturesCandleData,
  SubscribedPairData,
  TRADING_VIEW_DEFAULTS,
} from "./trading_view.types";
import { getFuturesTradeCandles } from "service/future-trade";

export const MultipleChartPage = { is: false };

export const lastBarCache = new Map<string, { [subscriberUID: string]: Bar }>();

export const subscribedCurrencyPairs = new Map<
  string,
  { [subscriberUID: string]: SubscribedPairData }
>();

export const trading_view_languages: LanguageCode[] = [
  "ar",
  "zh",
  "cs",
  "da_DK",
  "nl_NL",
  "en",
  "et_EE",
  "fr",
  "de",
  "el",
  "he_IL",
  "hu_HU",
  "id_ID",
  "it",
  "ja",
  "ko",
  "fa",
  "pl",
  "pt",
  "ro",
  "ru",
  "sk_SK",
  "es",
  "sv",
  "th",
  "tr",
  "vi",
  "no",
  "ms_MY",
  "zh_TW",
];

export const widgetOptionsDefault = {
  interval: TRADING_VIEW_DEFAULTS.INTERVAL as ResolutionString,
  library_path: TRADING_VIEW_DEFAULTS.LIBRARY_PATH as string,
  charts_storage_url: TRADING_VIEW_DEFAULTS.CHARTS_STORAGE_URL as string,
  charts_storage_api_version:
    TRADING_VIEW_DEFAULTS.CHARTS_STORAGE_API_VERSION as AvailableSaveloadVersions,
  client_id: TRADING_VIEW_DEFAULTS.CLIENT_ID as string,
  user_id: TRADING_VIEW_DEFAULTS.USER_ID as string,
  custom_css_url: TRADING_VIEW_DEFAULTS.CUSTOM_CSS as string,
};

export const configurationData: DatafeedConfiguration = {
  supported_resolutions: [
    "1",
    "3",
    "5",
    "15",
    "30",
    "60",
    "120",
    "240",
    "360",
    "480",
    "720",
    "1D",
    "1W",
    "1M",
  ] as ResolutionString[],
  exchanges: [
    {
      value: "Tradex",
      name: "Tradex",
      desc: "Tradex",
    },
  ],
  symbols_types: [
    {
      name: "crypto",
      value: "crypto",
    },
  ],
};

export async function getAllSymbols() {
  let allSymbols: allSymbolType[] = [];
  allSymbols.push({
    symbol: "BTC/USDT",
    full_name: "BTC/USDT",
    description: "BTC/USDT",
    exchange: "Tradex",
    type: "crypto",
    ticker: "",
  });
  return allSymbols;
}

export async function prepareDataFromPrimaryServer(
  bars: Bar[],
  symbolInfo: LibrarySymbolInfo,
  resolution: ResolutionString,
  countBack: number,
  from: number,
  to: number,
  coin_pair_uid: string,
  isFuture?: boolean,
): Promise<Bar[]> {
  const fromMili = from * 1000;
  const toMili = to * 1000;

  let interval = resolution === "1D" ? "1440" : resolution;

  // api call
  let candles: FuturesCandleData[] = [];

  candles = await getFuturesTradeCandles({
    coin_pair_uid,
    from,
    to,
    interval: interval,
  });

  const candlesLen = candles?.length;

  for (let i = 0; i < candlesLen; i++) {
    const bar: FuturesCandleData = candles[i];

    const barTime = Number(bar.time) * 1000;

    if (barTime >= fromMili && barTime < toMili) {
      bars = [
        ...bars,
        {
          time: Number(barTime),
          low: Number(bar.low),
          high: Number(bar.high),
          open: Number(bar.open),
          close: Number(bar.close),
          volume: Number(bar.volume),
        },
      ];
    }
  }
  return bars;
}

export function updateLastBarMap(
  symbolInfo: LibrarySymbolInfo,
  resolution: ResolutionString,
  bar: Bar,
) {
  const last_bar_pair_data = lastBarCache.get(
    symbolInfo.base_name?.toString() || "",
  );
  if (!last_bar_pair_data) {
    lastBarCache.set(symbolInfo.base_name?.toString() || "", {
      [`${symbolInfo.name}_#_${resolution}`]: { ...bar },
    });
  } else {
    last_bar_pair_data[`${symbolInfo.name}_#_${resolution}`] = { ...bar };
  }
}

export function getLastBar(
  symbolInfo: LibrarySymbolInfo,
  resolution: ResolutionString,
) {
  const last_bar_pair_data = lastBarCache.get(
    symbolInfo.base_name?.toString() || "",
  );
  const last_bar = last_bar_pair_data
    ? last_bar_pair_data[`${symbolInfo.name}_#_${resolution}`]
    : null;
  return last_bar;
}

export function updateSubCurrencyPairsMap(
  currencyPair: string,
  subscriptionItem: SubscribedPairData,
) {
  const subs_pair_data = subscribedCurrencyPairs.get(currencyPair);
  if (!subs_pair_data) {
    subscribedCurrencyPairs.set(currencyPair, {
      [`${subscriptionItem.subscriberUID}`]: subscriptionItem,
    });
  } else {
    subs_pair_data[`${subscriptionItem.subscriberUID}`] = subscriptionItem;
  }
}

export function getSubCurrencyPairData(
  currencyPair: string,
  subscriberUID: string,
): SubscribedPairData | null {
  const subs_pair_data = subscribedCurrencyPairs.get(currencyPair);
  const pair_data = subs_pair_data ? subs_pair_data[`${subscriberUID}`] : null;
  return pair_data;
}

export function getRoundedTime(
  actualTime: number,
  resolution: ResolutionString,
) {
  let intervalMin: number;
  let intervalStr: string;

  if (resolution.includes("D")) {
    intervalStr = resolution.replace("D", "");
    intervalMin = Number(intervalStr) * 24 * 60;
  } else if (resolution.includes("W")) {
    intervalStr = resolution.replace("W", "");
    intervalMin = Number(intervalStr) * 7 * 24 * 60;
  } else if (resolution.includes("M")) {
    intervalStr = resolution.replace("M", "");
    intervalMin = Number(intervalStr) * 30 * 24 * 60;
  } else {
    intervalMin = Number(resolution);
  }

  return (
    Math.floor(actualTime / (1000 * 60 * intervalMin)) *
    (1000 * 60 * intervalMin)
  );
}

export function getNextBarTime(barTime: number, resolution: ResolutionString) {
  let intervalMin: number;
  let intervalStr: string;

  if (resolution.includes("D")) {
    intervalStr = resolution.replace("D", "");
    intervalMin = Number(intervalStr) * 24 * 60;
  } else if (resolution.includes("W")) {
    intervalStr = resolution.replace("W", "");
    intervalMin = Number(intervalStr) * 7 * 24 * 60;
  } else if (resolution.includes("M")) {
    intervalStr = resolution.replace("M", "");
    intervalMin = Number(intervalStr) * 30 * 24 * 60;
  } else {
    intervalMin = Number(resolution);
  }

  return barTime + intervalMin * 60 * 1000;
}
