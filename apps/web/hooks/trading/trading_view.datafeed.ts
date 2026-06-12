import {
  Bar,
  HistoryCallback,
  IBasicDataFeed,
  LibrarySymbolInfo,
  OnReadyCallback,
  PeriodParams,
  ResolutionString,
  ResolveCallback,
  SearchSymbolResultItem,
  SearchSymbolsCallback,
  SubscribeBarsCallback,
  SymbolResolveExtension,
} from "public/static/charting_library/charting_library";
import {
  configurationData,
  getAllSymbols,
  getLastBar,
  prepareDataFromPrimaryServer,
  updateLastBarMap,
} from "./trading_view.helper";
import {
  subscribeOnStream,
  unsubscribeFromStream,
} from "./trading_view.streaming";

let prevInterval: string = "";
let shouldFetchFromPrmServerNext = true;
let shouldFetchFromCryptoCompare = true;
let firstData = true;

export const TradingViewDatafeedWithDecimalForFutures = (
  coin_pair_uid: string,
  customSymbolInfo?: Partial<LibrarySymbolInfo>,
): IBasicDataFeed => {
  return {
    onReady: (callback: OnReadyCallback) => {
      callback(configurationData);
    },

    searchSymbols: async (
      userInput: string,
      exchange: string,
      symbolType: string,
      onResult: SearchSymbolsCallback,
    ) => {
      const symbols = await getAllSymbols();

      const newSymbols: SearchSymbolResultItem[] = symbols.filter((symbol) => {
        const isExchangeValid = exchange === "" || symbol.exchange === exchange;
        const isFullSymbolContainsInput =
          symbol.full_name.toLowerCase().indexOf(userInput.toLowerCase()) !==
          -1;
        return isExchangeValid && isFullSymbolContainsInput;
      });

      onResult(newSymbols);
    },

    // @ts-ignore
    resolveSymbol: async (
      symbolName: string,
      onResolve: ResolveCallback,
      onError: ErrorCallback,
      extension?: SymbolResolveExtension | undefined,
    ) => {
      const symbols = await getAllSymbols();

      const base_code = symbolName.split("/")[0];
      const trade_code = symbolName.split("/")[1];

      const symbolInfo: LibrarySymbolInfo = {
        name: symbolName,
        full_name: symbolName,
        base_name: [`${base_code}${trade_code}`],
        ticker: "",
        description: "",
        type: "",
        session: "24x7",
        exchange: process.env.NEXT_PUBLIC_APP_TITLE || " ",
        listed_exchange: "",
        timezone: "Etc/UTC",
        format: "price",
        pricescale: 100, // char length of 0 are the length of the decimals
        minmov: 1,
        has_intraday: true,
        has_no_volume: false, // whether to show volume bars or not
        has_weekly_and_monthly: true,
        supported_resolutions:
          configurationData.supported_resolutions as ResolutionString[],
        volume_precision: 4,
        data_status: "streaming",
        ...customSymbolInfo,
      };

      onResolve(symbolInfo);
      shouldFetchFromPrmServerNext = true;
      shouldFetchFromCryptoCompare = true;
      firstData = true;
    },

    // @ts-ignore
    getBars: async (
      symbolInfo: LibrarySymbolInfo,
      resolution: ResolutionString,
      periodParams: PeriodParams,
      onResult: HistoryCallback,
      onError: ErrorCallback,
    ) => {
      let { from, to, firstDataRequest, countBack } = periodParams;
      if (!prevInterval || prevInterval != resolution) {
        prevInterval = resolution;
        shouldFetchFromPrmServerNext = true;

        firstData = true;
      }

      try {
        let bars: Bar[] = [];

        if (shouldFetchFromPrmServerNext) {
          bars = await prepareDataFromPrimaryServer(
            bars,
            symbolInfo,
            resolution,
            countBack,
            from,
            to,
            coin_pair_uid,
            true,
          );
        }

        if (firstDataRequest) {
          updateLastBarMap(symbolInfo, resolution, bars[bars.length - 1]);
        }

        onResult(bars, {
          noData: bars?.length ? false : true,
        });
      } catch (error: any) {
        console.error("tv: [getBars]: error", error);
        onError(new DOMException("tv: [getBars]: error", error));
      }
    },

    subscribeBars: (
      symbolInfo: LibrarySymbolInfo,
      resolution: ResolutionString,
      onTick: SubscribeBarsCallback,
      listenerGuid: string,
      onResetCacheNeededCallback: () => void,
    ) => {
      const last_candle_bar = getLastBar(symbolInfo, resolution);

      subscribeOnStream(
        symbolInfo,
        resolution,
        onTick,
        listenerGuid,
        onResetCacheNeededCallback,
        last_candle_bar,
      );
    },

    unsubscribeBars: (subscriberUID: string) => {
      shouldFetchFromPrmServerNext = true;
      firstData = true;
      unsubscribeFromStream(subscriberUID);
    },
  };
};
