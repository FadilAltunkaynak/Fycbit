import {
  FUTURE_DISABLED_FEATURES,
  FUTURE_ENABLED_FEATURES,
} from "components/exchange/api/chartConfig";
import { generateNumberOfZeros, nullChecker } from "helpers/functions";
import { useRouter } from "next/router";
import {
  ChartingLibraryWidgetOptions,
  LanguageCode,
  ResolutionString,
} from "public/static/charting_library/charting_library";
import { useRef } from "react";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
import { useTradingView } from "./useTradingView";
import { TRADING_VIEW_DEFAULTS } from "./trading_view.types";
import { APP_DEFAULT } from "helpers/core-constants";
import {
  trading_view_languages,
  widgetOptionsDefault,
} from "./trading_view.helper";
import { TradingViewDatafeedWithDecimalForFutures } from "./trading_view.datafeed";

export const useTradingViewChartFutures = () => {
  const container_id = TRADING_VIEW_DEFAULTS.CONTAINER_ID_SPOT;
  const containerRef = useRef<HTMLElement>(null);

  const router = useRouter();

  const { pairDetails } = useSelector((state: RootState) => state.futureTrade);

  const code = nullChecker(pairDetails?.code);
  const baseCurrency = nullChecker(pairDetails?.base_coin_code);
  const tradeCurrency = nullChecker(pairDetails?.trade_coin_code);
  const trade_decimal = Number(pairDetails?.trade_decimal);

  const { theme } = useSelector((state: RootState) => state.common);

  const getCssVar = (variable: string) =>
    getComputedStyle(document.documentElement)
      .getPropertyValue(variable)
      .trim();

  const darkModeStatus = theme === "dark";

  const tradeUpColor = getCssVar("--future-trade-green");
  const tradeDownColor = getCssVar("--future-trade-red");

  // const bgColor = getCssVar("--main-background-color");

  const bgColor = theme === "dark" ? "rgb(11, 14, 17)" : "#fff";

  const applyOverrides = {
    "paneProperties.background": bgColor,
    "paneProperties.backgroundType": "solid",

    "paneProperties.vertGridProperties.color": bgColor,
    "paneProperties.horzGridProperties.color": bgColor,

    // "paneProperties.legendProperties.showStudyArguments": true,
    // "paneProperties.legendProperties.showStudyTitles": true,
    // "paneProperties.legendProperties.showStudyValues": true,
    "paneProperties.legendProperties.showSeriesTitle": false,
    // "paneProperties.legendProperties.showSeriesOHLC": true,
    // "paneProperties.legendProperties.showLegend": true,

    "scalesProperties.textColor": getCssVar("--title-color"), // symbol text color
    "scalesProperties.backgroundColor": bgColor,

    // "scalesProperties.showLeftScale": false,
    // "scalesProperties.showRightScale": true,
    // "scalesProperties.scaleSeriesOnly ": false,
    // "scalesProperties.showSeriesLastValue": true,
    // "scalesProperties.showSeriesPrevCloseValue": false,
    // "scalesProperties.showStudyLastValue": false,
    // "scalesProperties.showStudyPlotLabels": false,
    // "scalesProperties.showSymbolLabels": false,

    //up and down
    "mainSeriesProperties.candleStyle.upColor": tradeUpColor,
    "mainSeriesProperties.candleStyle.downColor": tradeDownColor,
    "mainSeriesProperties.candleStyle.drawBorder": true,
    "mainSeriesProperties.candleStyle.borderUpColor": tradeUpColor,
    "mainSeriesProperties.candleStyle.borderDownColor": tradeDownColor,
    "mainSeriesProperties.candleStyle.wickUpColor": tradeUpColor,
    "mainSeriesProperties.candleStyle.wickDownColor": tradeDownColor,
    "mainSeriesProperties.candleStyle.barColorsOnPrevClose": false,
    "mainSeriesProperties.hollowCandleStyle.upColor": tradeUpColor,
    "mainSeriesProperties.hollowCandleStyle.downColor": tradeDownColor,
    "mainSeriesProperties.hollowCandleStyle.drawWick": true,
    "mainSeriesProperties.hollowCandleStyle.drawBorder": true,
    "mainSeriesProperties.hollowCandleStyle.borderUpColor": tradeUpColor,
    "mainSeriesProperties.hollowCandleStyle.borderDownColor": tradeDownColor,
    "mainSeriesProperties.hollowCandleStyle.wickUpColor": tradeUpColor,
    "mainSeriesProperties.hollowCandleStyle.wickDownColor": tradeDownColor,
    "mainSeriesProperties.haStyle.upColor": tradeUpColor,
    "mainSeriesProperties.haStyle.downColor": tradeDownColor,
    "mainSeriesProperties.haStyle.drawWick": true,
    "mainSeriesProperties.haStyle.drawBorder": true,
    "mainSeriesProperties.haStyle.borderUpColor": tradeUpColor,
    "mainSeriesProperties.haStyle.borderDownColor": tradeDownColor,
    "mainSeriesProperties.haStyle.wickUpColor": tradeUpColor,
    "mainSeriesProperties.haStyle.wickDownColor": tradeDownColor,
    "mainSeriesProperties.barStyle.upColor": tradeUpColor,
    "mainSeriesProperties.barStyle.downColor": tradeDownColor,
    "mainSeriesProperties.barStyle.barColorsOnPrevClose": false,
    "mainSeriesProperties.barStyle.dontDrawOpen": false,
    "mainSeriesProperties.lineStyle.color": tradeDownColor,
  };

  const studyOverrides = {
    "volume.volume.color.0": tradeDownColor,
    "volume.volume.color.1": tradeUpColor,
    // "volume.volume.transparency": 0,
    // "volume.volume ma.color": "blue",
    // "volume.volume ma.transparency": 0,
    // "volume.options.showStudyArguments": false,
    // "volume.options.showStudyTitles": false,
    // "volume.options.showStudyValues": false,
    // "volume.options.showLegend": false,
    // "volume.options.showStudyOutput": false,
    // "volume.options.showStudyOverlay": false,
    // "volume.options.showSeriesTitle": false,
    // "volume.options.showSeriesOHLC": false,
    // "volume.options.showBarChange": false,
    // "volume.options.showCountdown": false,
  };

  const timeFrames = [
    { text: "5m", resolution: "5" as ResolutionString },
    { text: "15m", resolution: "15" as ResolutionString },
    { text: "30m", resolution: "30" as ResolutionString },
    { text: "2h", resolution: "120" as ResolutionString },
    { text: "4h", resolution: "240" as ResolutionString },
    {
      text: "1d",
      resolution: "D" as ResolutionString,
      description: "1 day",
    },
    // {
    //   text: "3d",
    //   resolution: "D" as ResolutionString,
    //   description: "3 days",
    // },
    // {
    //   text: "1w",
    //   resolution: "W" as ResolutionString,
    //   description: "1 week",
    // },
    // {
    //   text: "1M",
    //   resolution: "M" as ResolutionString,
    //   description: "1 month",
    // },
  ];

  const pricescale = generateNumberOfZeros(trade_decimal);

  const widgetOptions: ChartingLibraryWidgetOptions = {
    symbol: `${baseCurrency}/${tradeCurrency}`,
    datafeed: TradingViewDatafeedWithDecimalForFutures(pairDetails?.uid || "", {
      pricescale: pricescale,
    }),
    fullscreen: false,
    autosize: true,
    width: 1400,
    height: 608,
    container: containerRef.current ? containerRef.current : "",
    container_id: container_id,

    // from defaults
    ...widgetOptionsDefault,

    custom_font_family: `'Roboto', 'Poppins', sans-serif`,

    // features
    enabled_features: FUTURE_ENABLED_FEATURES,
    disabled_features: FUTURE_DISABLED_FEATURES,

    // dependent
    theme: darkModeStatus ? "Dark" : "Light",
    locale: (trading_view_languages.includes(router.locale as LanguageCode)
      ? router.locale
      : APP_DEFAULT.LANG) as LanguageCode,
    // preset: isMobile ? "mobile" : undefined,

    // overrides
    studies_overrides: studyOverrides,
    overrides: {
      "scalesProperties.textColor": "red",
    },
    time_frames: timeFrames, // timeFrames,
  };

  const movingAverages = [
    {
      length: 5,
      color: "#349ff4",
    },
    {
      length: 10,
      color: "#58d3e2",
    },
    {
      length: 30,
      color: "#fbc746",
    },
  ];

  const depsArr = [
    pairDetails?.code,
    router.locale,
    // isTablet,
    // isMobile,
    // s,
    // p,
    trade_decimal,
  ];
  const depsArrDynamic = [darkModeStatus];

  const { depsArr: d } = useTradingView({
    // needs update
    widgetOptions: widgetOptions,
    depsArr: depsArr,
    applyOverrides: applyOverrides,
    depsArrDynamic: depsArrDynamic,
    movingAverages: movingAverages,
  });

  return {
    container_id,
    containerRef,
  };
};
