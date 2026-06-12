import {
  LibrarySymbolInfo,
  ResolutionString,
  SubscribeBarsCallback,
} from "public/static/charting_library/charting_library";
import {
  getNextBarTime,
  getRoundedTime,
  getSubCurrencyPairData,
  lastBarCache,
  MultipleChartPage,
  subscribedCurrencyPairs,
  updateSubCurrencyPairsMap,
} from "./trading_view.helper";

export interface FuturesTradeSubsRes {
  created_at: string;
  price: string;
  amount: string;
  previous_price: string;
  code: string;
}

export function updateRealTimeChartData(trade: FuturesTradeSubsRes) {
  const tradePrice = Number(trade.price);
  const tradeTime = new Date(trade.created_at).getTime();
  const currencyPair = trade.code;

  const subs_pair_data = subscribedCurrencyPairs.get(currencyPair);

  for (const key in subs_pair_data) {
    const subscriptionItem = subs_pair_data[key];

    if (subscriptionItem === undefined) {
      return;
    }
    const lastCandleBar = subscriptionItem.lastCandleBar;
    const nextBarTime = lastCandleBar?.time
      ? getNextBarTime(lastCandleBar.time, subscriptionItem.resolution)
      : 0;
    const roundedTradeTime = getRoundedTime(
      tradeTime,
      subscriptionItem.resolution,
    );

    let bar: any;
    if (roundedTradeTime >= nextBarTime) {
      bar = {
        time: roundedTradeTime,
        open: tradePrice,
        high: tradePrice,
        low: tradePrice,
        close: tradePrice,
        // volume: tradePrice * Number(trade.amount),  //volume of trade crypto
        volume: Number(trade.amount), //volume of base crypto
      };
    } else {
      bar = {
        ...lastCandleBar,
        high: Math.max(lastCandleBar.high, tradePrice),
        low: Math.min(lastCandleBar.low, tradePrice),
        close: tradePrice,
        // volume:
        //   Number(lastCandleBar.volume) + tradePrice * Number(trade.amount),  //volume of trade crypto
        volume: Number(lastCandleBar.volume) + Number(trade.amount), //volume of base crypto
      };
    }

    subscriptionItem.lastCandleBar = bar;
    // set realtime data
    subscriptionItem.handlers.map((handler) => {
      handler.callback(bar);
    });
  }
}

export function subscribeOnStream(
  symbolInfo: LibrarySymbolInfo,
  resolution: ResolutionString,
  onRealtimeCallback: SubscribeBarsCallback,
  subscriberUID: string,
  onResetCacheNeededCallback: () => void,
  lastCandleBar: any,
) {

  const currencyPair = symbolInfo.base_name?.toString() || "";

  let subscriptionItem = getSubCurrencyPairData(currencyPair, subscriberUID);
  if (subscriptionItem) {
    subscriptionItem.handlers.push({
      id: subscriberUID,
      callback: onRealtimeCallback,
    });
  } else {
    subscriptionItem = {
      subscriberUID,
      resolution,
      lastCandleBar,
      handlers: [
        {
          id: subscriberUID,
          callback: onRealtimeCallback,
        },
      ],
    };
  }

  updateSubCurrencyPairsMap(currencyPair, subscriptionItem);
}

export function unsubscribeFromStream(subscriberUID: string) {
  // find a subscription with id === subscriberUID
  for (const currencyPair of <any>subscribedCurrencyPairs.keys()) {
    const subs_pair_data = subscribedCurrencyPairs.get(currencyPair);
    const subscriptionItem = subs_pair_data
      ? subs_pair_data[subscriberUID]
      : null;

    if (!MultipleChartPage.is && subscriptionItem != null) {
      subs_pair_data && delete subs_pair_data[subscriberUID];

      const last_bar_pair_data = lastBarCache.get(currencyPair);
      last_bar_pair_data && delete last_bar_pair_data[subscriberUID];
      break;
    }
  }
}
