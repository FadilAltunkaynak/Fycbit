import { useEffect } from "react";
import {
  widget,
  ChartingLibraryWidgetOptions,
  StudyOverrides,
} from "../../public/static/charting_library";

declare global {
  interface Window {
    tvWidget: any;
  }
}

type useTradingViewType = {
  widgetOptions: ChartingLibraryWidgetOptions;
  depsArr: any[];
  depsArrDynamic?: any[];
  applyOverrides?: StudyOverrides;
  movingAverages?: {
    length: number;
    color: string;
  }[];
};

export const useTradingView = ({
  widgetOptions,
  depsArr,
  depsArrDynamic,
  applyOverrides,
  movingAverages,
}: useTradingViewType) => {
  const chartInit = (config: ChartingLibraryWidgetOptions) => {
    const tvWidget = new widget(config);

    window.tvWidget = tvWidget;

    window.tvWidget.onChartReady(() => {
      movingAverages?.forEach((el) =>
        window.tvWidget?.activeChart().createStudy(
          "Moving Average",
          false,
          false,
          {
            length: el.length,
          },
          { "plot.color.0": el.color },
        ),
      );

      window.tvWidget?.applyOverrides(applyOverrides || {});
    });
  };

  // on mount
  useEffect(() => {
    window.tvWidget = null;

    chartInit(widgetOptions);

    return () => {
      if (window.tvWidget !== null) {
        window.tvWidget?.remove();
        window.tvWidget = null;
      }
    };
  }, depsArr);

  useEffect(() => {
    const isDarkMode = depsArrDynamic && depsArrDynamic[0];

    window.tvWidget?.onChartReady(() => {
      window.tvWidget?.applyOverrides(applyOverrides || {});

      window.tvWidget?.changeTheme(isDarkMode ? "Dark" : "Light").then(() => {
        window.tvWidget?.applyOverrides(applyOverrides || {});
      });
    });
  }, depsArrDynamic);

  return { depsArr };
};
