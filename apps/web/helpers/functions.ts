import { setThemeColor } from "state/reducer/common";
import {
  DEFAULT_PRIMARY_COLOR,
  FUTURE_ORDER_METHOD,
  FUTURE_ORDER_TYPE,
  FUTURES_DEFAULT_CALCULATION,
} from "./core-constants";
import { formatCurrency } from "common";
import { twMerge } from "tailwind-merge";
import { type ClassValue } from "clsx";
import clsx from "clsx";
import { FutureLeverageSettings } from "state/reducer/futureReducer";
import moment from "moment";

export const changeThemeSettingsDashboard = (
  tradeGreen: string,
  tradeRed: string,
  setThemeColor: any,
  ThemeColor: any,
  chooseColor: number,
) => {
  const checkRedGreen = localStorage.getItem("chart-up-down");
  if (checkRedGreen === "1") {
    document.documentElement.style.setProperty("--trade-green", tradeGreen);
    document.documentElement.style.setProperty("--trade-red", tradeRed);
  } else {
    document.documentElement.style.setProperty("--trade-green", tradeRed);
    document.documentElement.style.setProperty("--trade-red", tradeGreen);
  }
  localStorage.setItem("tradeGreen", tradeGreen);
  localStorage.setItem("tradeRed", tradeRed);
  localStorage.setItem("chooseColor", chooseColor.toString());
  setThemeColor((prevThemeColor: any) => ({
    ...prevThemeColor,
    green: tradeGreen,
    red: tradeRed,
    chooseColor: chooseColor,
  }));
};

export const changeLayout = (layoutNumber: number, setLayout: any) => {
  localStorage.setItem("layout-trade", layoutNumber.toString());
  setLayout(layoutNumber);
};
export const swapGreenToRedAndRedToGeen = (
  setThemeColor: any,
  ThemeColor: any,
  redGreenUpDown: number,
) => {
  const tradeGreen = localStorage.getItem("tradeGreen");
  const tradeRed = localStorage.getItem("tradeRed");
  if (redGreenUpDown === 2) {
    localStorage.setItem("chart-up-down", "2");
    setThemeColor((prevThemeColor: any) => ({
      ...prevThemeColor,
      green: tradeRed ? tradeRed : "#d64141",
      red: tradeGreen ? tradeGreen : "#2e8f6b",
      redGreenUpDown: redGreenUpDown,
    }));
    document.documentElement.style.setProperty(
      "--trade-green",
      tradeRed ? tradeRed : "#d64141",
    );
    document.documentElement.style.setProperty(
      "--trade-red",
      tradeGreen ? tradeGreen : "#2e8f6b",
    );
  } else {
    localStorage.setItem("chart-up-down", "1");
    setThemeColor((prevThemeColor: any) => ({
      ...prevThemeColor,
      green: tradeGreen ? tradeGreen : "#2e8f6b",
      red: tradeRed ? tradeRed : "#d64141",
      redGreenUpDown: redGreenUpDown,
    }));
    document.documentElement.style.setProperty(
      "--trade-green",
      tradeGreen ? tradeGreen : "#2e8f6b",
    );
    document.documentElement.style.setProperty(
      "--trade-red",
      tradeRed ? tradeRed : "#d64141",
    );
  }
};
export const checkDashboardThemeSettings = (
  setThemeColor: any,
  ThemeColor: any,
  setLayout: any,
) => {
  const tradeGreen = localStorage.getItem("tradeGreen");
  const tradeRed = localStorage.getItem("tradeRed");
  const layoutTrade = localStorage.getItem("layout-trade");
  const getColorNumber = localStorage.getItem("chooseColor");

  const checkRedGreen = localStorage.getItem("chart-up-down");
  setLayout(layoutTrade ? Number(layoutTrade) : 1);
  if (checkRedGreen === null) {
    localStorage.setItem("chart-up-down", "1");
  }

  if (Number(checkRedGreen) === 2) {
    document.documentElement.style.setProperty(
      "--trade-green",
      tradeRed ? tradeRed : "#d64141",
    );
    document.documentElement.style.setProperty(
      "--trade-red",
      tradeGreen ? tradeGreen : "#2e8f6b",
    );

    setThemeColor({
      redGreenUpDown: checkRedGreen ? Number(checkRedGreen) : 2,
      red: tradeGreen ? tradeGreen : "#2e8f6b",
      green: tradeRed ? tradeRed : "#d64141",
      chooseColor: getColorNumber ? Number(getColorNumber) : 1,
    });
  } else {
    document.documentElement.style.setProperty(
      "--trade-green",
      tradeGreen ? tradeGreen : "#2e8f6b",
    );
    document.documentElement.style.setProperty(
      "--trade-red",
      tradeRed ? tradeRed : "#d64141",
    );

    setThemeColor({
      redGreenUpDown: checkRedGreen ? Number(checkRedGreen) : 1,
      green: tradeGreen ? tradeGreen : "#2e8f6b",
      red: tradeRed ? tradeRed : "#d64141",
      chooseColor: getColorNumber ? Number(getColorNumber) : 1,
    });
  }
};

export function hexToRgb(hex: any) {
  if (!hex) return "";
  // Remove the hash sign if it's included
  hex = hex.replace(/^#/, "");

  // Parse the hex value into individual color components
  const bigint = parseInt(hex, 16);
  const r = (bigint >> 16) & 255;
  const g = (bigint >> 8) & 255;
  const b = bigint & 255;

  return `${r}, ${g}, ${b}`;
}

export const checkDarkMode = (settings: any, dispatch: any) => {
  const theme = localStorage.getItem("theme");
  if (theme === "light") {
    localStorage.setItem("theme", "light");
    dispatch(setThemeColor("light"));
    document.documentElement.setAttribute("data-theme", "light");
    settings.theme_color.map((themeColors: any) => {
      if (themeColors.name == "--primary-color") {
        localStorage.setItem("--primary-color", themeColors.value);

        document.documentElement.style.setProperty(
          themeColors.name,
          themeColors.value
            ? hexToRgb(themeColors.value)
            : hexToRgb(DEFAULT_PRIMARY_COLOR[settings.default_theme_color]),
        );
      } else if (!themeColors.value) {
        return;
      } else {
        document.documentElement.style.setProperty(
          themeColors.name,
          themeColors.value,
        );
      }
    });
  } else {
    localStorage.setItem("theme", "dark");
    dispatch(setThemeColor("dark"));
    document.documentElement.setAttribute("data-theme", "dark");
    settings.dark_theme_color.map((themeColors: any) => {
      if (themeColors.name == "--primary-color") {
        localStorage.setItem("--primary-color", themeColors.value);
        document.documentElement.style.setProperty(
          themeColors.name,
          themeColors.value
            ? hexToRgb(themeColors.value)
            : hexToRgb(DEFAULT_PRIMARY_COLOR[settings.default_theme_color]),
        );
      } else if (!themeColors.value) {
        return;
      } else {
        document.documentElement.style.setProperty(
          themeColors.name,
          themeColors.value,
        );
      }
    });
  }
};
export const checkDarkModeWithoutSettings = () => {
  const theme = localStorage.getItem("theme");
  if (theme === "light") {
    localStorage.setItem("theme", "light");
    // dispatch(setTheme("light"));

    document.documentElement.setAttribute("data-theme", "dark");
  } else {
    localStorage.setItem("theme", "dark");
    // dispatch(setTheme("dark"));
    document.documentElement.setAttribute("data-theme", "dark");
  }
};
export const darkModeToggle = (settings: any, setTheme: any, dispatch: any) => {
  const theme = localStorage.getItem("theme");
  if (theme === "dark") {
    setTheme(1);
    dispatch(setThemeColor("light"));
    document.documentElement.setAttribute("data-theme", "light");
    localStorage.setItem("theme", "light");
    settings.theme_color.map((theme_color: any) => {
      if (theme_color.name == "--primary-color") {
        document.documentElement.style.setProperty(
          theme_color.name,
          theme_color.value
            ? hexToRgb(theme_color.value)
            : hexToRgb(DEFAULT_PRIMARY_COLOR[settings.default_theme_color]),
        );
      } else if (!theme_color.value) {
        return;
      } else {
        document.documentElement.style.setProperty(
          theme_color.name,
          theme_color.value,
        );
      }
    });
  } else {
    setTheme(0);
    dispatch(setThemeColor("dark"));

    document.documentElement.setAttribute("data-theme", "dark");
    settings.dark_theme_color.map((theme_color: any) => {
      if (theme_color.name == "--primary-color") {
        document.documentElement.style.setProperty(
          theme_color.name,
          theme_color.value
            ? hexToRgb(theme_color.value)
            : hexToRgb(DEFAULT_PRIMARY_COLOR[settings.default_theme_color]),
        );
      } else if (!theme_color.value) {
        return;
      } else {
        document.documentElement.style.setProperty(
          theme_color.name,
          theme_color.value,
        );
      }
    });
    localStorage.setItem("theme", "dark");
  }
};

export const darkModeToggleDashboard = (dispatch: any) => {
  const theme = localStorage.getItem("theme");
  if (theme === "dark") {
    document.documentElement.setAttribute("data-theme", "light");
    dispatch(setThemeColor("light"));
    localStorage.setItem("theme", "light");
  } else {
    dispatch(setThemeColor("dark"));
    document.documentElement.setAttribute("data-theme", "dark");
    localStorage.setItem("theme", "dark");
  }
};
export const checkThemeState = (setTheme: any, dispatch: any) => {
  const theme = localStorage.getItem("theme");
  if (theme === "light") {
    setTheme(1);
    // dispatch(setTheme("light"));
  } else {
    setTheme(0);
    // dispatch(setTheme("light"));
  }
};
export const rootThemeCheck = (default_theme_mode: string) => {
  const theme = localStorage.getItem("theme");
  if (!theme) {
    localStorage.setItem("theme", default_theme_mode);
    document.documentElement.setAttribute("data-theme", default_theme_mode);
    return;
  }
  if (theme === "light") {
    localStorage.setItem("theme", "light");
    document.documentElement.setAttribute("data-theme", "light");
  } else {
    localStorage.setItem("theme", "dark");
    document.documentElement.setAttribute("data-theme", "dark");
  }
};

export function isApiLocalhost(): boolean {
  const api_url = process.env.NEXT_PUBLIC_BASE_URL || "";
  if (!api_url) {
    throw new Error(`env NEXT_PUBLIC_BASE_URL value not found`);
  }
  if (api_url.includes("localhost") || api_url.includes("127.0.0.1")) {
    return true;
  } else return false;
}

export function numberToKMB(num: number, decimal: number) {
  if (num === null || num === undefined) return "0";

  if (num >= 1e9) {
    return (num / 1e9).toFixed(2).replace(/\.0$/, "") + "B";
  }
  if (num >= 1e6) {
    return (num / 1e6).toFixed(2).replace(/\.0$/, "") + "M";
  }
  if (num >= 1e3) {
    return (num / 1e3).toFixed(2).replace(/\.0$/, "") + "K";
  }
  return formatCurrency(num, decimal).toString();
}

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function numberToKMBFunc(num: number, decimal: number) {
  if (num === null || num === undefined) return "0";

  if (num >= 1e9) {
    return (num / 1e9).toFixed(2).replace(/\.0$/, "") + "B";
  }
  if (num >= 1e6) {
    return (num / 1e6).toFixed(2).replace(/\.0$/, "") + "M";
  }
  if (num >= 1e3) {
    return (num / 1e3).toFixed(2).replace(/\.0$/, "") + "K";
  }
  return formatAmountDecimal(num, decimal).toString();
}

export function formatAmountDecimal(
  amount: number,
  decimal: number,
  abs = false,
): string {
  amount = Number(amount);
  if (!amount) return "0";

  const sign = String(amount)[0];
  const split = noExponents(amount).split(".");
  let value = "";

  if (Number(split[1]?.length) <= decimal) {
    value = amount.toFixed(decimal > 100 ? 100 : decimal);
  } else {
    const numOfZeros = generateNumberOfZeros(decimal);
    amount = multiplyNumbers(amount, numOfZeros);
    amount = Number(noExponents(amount).split(".")[0]);
    value = String(divideNumbers(amount, numOfZeros));
  }
  if (abs) value = String(Math.abs(Number(value)));
  if (abs && sign == "-") value = `${sign}${value}`;
  return value;
}

export function noExponents(num: any, decimal?: number): string {
  num = Number(num);
  let result;
  const data = String(num).split(/[eE]/);
  if (data.length == 1) {
    result = data[0];
    if (decimal) {
      result = Number(result).toFixed(decimal > 100 ? 100 : decimal);
    }
    return result;
  }

  let z = "";
  const sign = num < 0 ? "-" : "";
  const str = data[0].replace(".", "");
  let mag = Number(data[1]) + 1;

  if (mag < 0) {
    z = sign + "0.";
    while (mag++) z += "0";
    result = z + str.replace(/^\-/, "");
    if (decimal) {
      result = Number(result).toFixed(decimal > 100 ? 100 : decimal);
    }
    return result;
  }
  mag -= str.length;
  while (mag--) z += "0";
  result = str + z;
  if (decimal) {
    result = Number(result).toFixed(decimal > 100 ? 100 : decimal);
  }
  return result;
}

export const generateNumberOfZeros = (
  len_of_zeros: number,
  start_num = 1,
): number => {
  let res = start_num.toString();

  for (let i = 0; i < len_of_zeros; i++) {
    res = res + "0";
  }

  return Number(res);
};

export function addNumbers(...numbers: number[]): number {
  if (numbers.length < 2) {
    throw new Error(`Send Min 2 numbers for summation`);
  }
  let res = numbers[0];
  for (let i = 1; i < numbers.length; i++) {
    res = res + numbers[i];
  }
  return res;
}

export function minusNumbers(...numbers: number[]): number {
  if (numbers.length < 2) {
    throw new Error(`Send Min 2 numbers for subtraction`);
  }
  let res = numbers[0];
  for (let i = 1; i < numbers.length; i++) {
    res = res - numbers[i];
  }
  return res;
}

export function multiplyNumbers(...numbers: number[]): number {
  if (numbers.length < 2) {
    throw new Error(`Send Min 2 numbers for multiplication`);
  }
  let res = numbers[0];
  for (let i = 1; i < numbers?.length; i++) {
    res = res * numbers[i];
  }
  return res;
}

export function divideNumbers(...numbers: number[]): number {
  if (numbers.length < 2) {
    throw new Error(`Send Min 2 numbers for division`);
  }
  let res = numbers[0];
  for (let i = 1; i < numbers?.length; i++) {
    res = res / numbers[i];
  }
  return res;
}

export const isOnlyNumber = (v: string) => {
  v = v.trim();
  if (!v) return false;
  return /^[0-9]+(.[0-9]+)?$/.test(v);
};

export const decimalCheckForm = (
  v: number | string,
  decimal: number,
  t: any,
): string | true | undefined => {
  if (decimal === 0 && String(v).includes(".")) return t("No decimal allowed");

  const numStr = String(noExponents(v));
  const decimalPart = numStr.split(".")[1];

  if (!decimalPart) return true;

  return decimalPart?.length <= decimal
    ? true
    : t("Max decimal point is ") + decimal;
};

export function getPercantageValue(
  value: number,
  percentage: number,
  decimal?: number,
) {
  let percentValue = 0;
  if (percentage == 100) {
    percentValue = value;
  } else {
    percentValue = divideNumbers(multiplyNumbers(value, percentage), 100);
  }
  if (decimal) {
    percentValue = Number(formatAmountDecimal(percentValue, decimal, true));
  }
  return percentValue;
}

export const futuresGetSlippagePriceBuy = (
  price: number,
  slippagePercent: number,
): number => {
  const slippageAmount = multiplyNumbers(
    price,
    divideNumbers(Number(slippagePercent), 100),
  );

  return slippageAmount > 0 ? addNumbers(price, slippageAmount) : price;
};

export interface FuturesMaxAmountCalculationPayload {
  available_balance: number;
  order_price: number;
  leverage: number;
  max_position_amount: number;
  maintenance_margin_rate: number;
  maintenance_amount: number;
  fee_percent: number;
}

export const calculateMaxAmount = (
  payload: FuturesMaxAmountCalculationPayload,
) => {
  let {
    order_price,
    available_balance,
    leverage,
    max_position_amount,

    fee_percent,
  } = payload;

  order_price = Number(order_price);
  max_position_amount = Number(max_position_amount);
  available_balance = Number(available_balance);
  leverage = Number(leverage);

  let max_trade_amount = divideNumbers(
    available_balance,
    addNumbers(1 / leverage, fee_percent / 100),
  );

  if (max_position_amount && max_trade_amount > max_position_amount) {
    max_trade_amount = max_position_amount;
  }

  const max_base_amount = divideNumbers(max_trade_amount, order_price);

  return max_base_amount;
};

export const getLeverageSettingsByLeverage = (
  leverage: number,
  list: Partial<FutureLeverageSettings>[],
): Partial<FutureLeverageSettings> => {
  //
  let result_leverage: Partial<FutureLeverageSettings> = {
    maintenance_amount: FUTURES_DEFAULT_CALCULATION.MAINTENANCE_AMOUNT,
    maintenance_margin_rate:
      FUTURES_DEFAULT_CALCULATION.MAINTENANCE_MARGIN_RATE,
    max_leverage: leverage,
    max_position_amount: FUTURES_DEFAULT_CALCULATION.MAX_POSITION,
  };

  if (list && list.length == 0) return result_leverage;

  const sortedList = [...list].sort((a, b) => {
    return Number(a.max_leverage) < Number(b?.max_leverage) ? -1 : 1;
  });

  for (let i = 0; i < sortedList.length; i++) {
    if (Number(sortedList[i].max_leverage) >= leverage) {
      return sortedList[i];
    }
  }

  return result_leverage;
};

export interface FuturesInitialMarginCalPayload {
  price: number;
  amount: number;
  leverage: number;
}

export const calculateInitialMargin = (
  payload: FuturesInitialMarginCalPayload,
) => {
  const { price, leverage, amount } = payload;
  const initialMargin = divideNumbers(
    multiplyNumbers(Number(price), Math.abs(Number(amount))),
    Number(leverage),
  );
  return initialMargin;
};

export interface FuturesCostCalculationPayload {
  order_type: FUTURE_ORDER_TYPE;
  order_price: number;
  mark_price: number;
  position_size: number;
  leverage: number;
  fee_percent: number;
}

export const calculateCost = (payload: FuturesCostCalculationPayload) => {
  const { order_price, leverage, fee_percent } = payload;

  const assuming_price = Number(order_price);
  const position_size = Math.abs(Number(payload.position_size));
  const notional_size = multiplyNumbers(assuming_price, position_size);
  const fee = multiplyNumbers(notional_size, fee_percent / 100);


  const initial_margin = calculateInitialMargin({
    price: assuming_price,
    amount: position_size,
    leverage,
  });

  const cost = addNumbers(initial_margin, fee);

  return cost;
};

export const calcCost = ({
  order_method,
  price,
  sizeInput,
  priceToCalcBuy,
  priceToCalcSell,
  mark_price,
  leverage,
  trade_decimal,
  sizeBuy,
  sizeSell,
  fee_percent,
}: {
  order_method: FUTURE_ORDER_METHOD;
  price: number;
  sizeInput: number;
  priceToCalcBuy: number;
  priceToCalcSell: number;
  mark_price: number;
  leverage: number;
  trade_decimal: number;
  sizeBuy: number;
  sizeSell: number;
  fee_percent: number;
}) => {
  if (order_method != FUTURE_ORDER_METHOD.MARKET && (!price || !sizeInput)) {
    return { buy: "0", sell: "0" };
  } else if (order_method == FUTURE_ORDER_METHOD.MARKET && !sizeInput) {
    return { buy: "0", sell: "0" };
  } else {
    const data = {
      mark_price: mark_price,
      leverage: leverage,
    };

    const costBuy = formatAmountDecimal(
      calculateCost({
        ...data,
        order_price: priceToCalcBuy,
        position_size: sizeBuy <= 0 ? 0 : sizeBuy,
        order_type: FUTURE_ORDER_TYPE.BUY,
        fee_percent,
      }),
      trade_decimal,
      true,
    );

    const costSell = formatAmountDecimal(
      calculateCost({
        ...data,
        order_price: priceToCalcSell,
        position_size: sizeSell <= 0 ? 0 : sizeSell,
        order_type: FUTURE_ORDER_TYPE.SELL,
        fee_percent,
      }),
      trade_decimal,
      true,
    );

    return { buy: costBuy, sell: costSell };
  }
};

export const calcSizeForCostBuy = (
  position: number,
  order_amount: number,
  input: number,
): number => {
  let size = Math.abs(position);
  size = minusNumbers(size, order_amount);
  size = size < 0 ? 0 : size;
  size = position < 0 ? minusNumbers(input, size) : input;

  return size;
};

export const calcSizeForCostSell = (
  position: number,
  order_amount: number,
  input: number,
): number => {
  let size = Math.abs(position);
  size = minusNumbers(size, order_amount);
  size = size < 0 ? 0 : size;
  size = position >= 0 ? minusNumbers(input, size) : input;

  return size;
};

export const nullChecker = (value: any, fallbackText = "") => {
  return value ?? fallbackText;
};

export const getDateRange = (days: number, endDate: Date = new Date()) => {
  const startDate = new Date(endDate);
  startDate.setDate(endDate.getDate() - days);

  return {
    start: startDate,
    end: endDate,
    days: days,
  };
};

export function nullOrUndefined(value: any): boolean {
  if (value == undefined || value == null) return true;
  else return false;
}

export const dateTimeDisplayer = (
  date: string | number | Date,
  format = "DD MMM Y h:mm a z",
) => {
  return nullOrUndefined(date) ? "N/A" : moment(date).format(format);
};

export const dateTimeDisplayerForLocal = (
  date: string | number | Date,
  format = "DD MMM Y h:mm a z",
) => {
  return nullOrUndefined(date)
    ? "N/A"
    : moment.utc(date).local().format(format);
};

export interface FuturesPNLCalPayload {
  order_type: FUTURE_ORDER_TYPE;
  position_size: number;
  order_price: number;
  mark_price: number;
}

export const calculatePNL = (payload: FuturesPNLCalPayload) => {
  const { order_price, order_type, mark_price } = payload;
  const dir = order_type == FUTURE_ORDER_TYPE.BUY ? 1 : -1;

  const position_size = Math.abs(Number(payload.position_size));
  const PNL = multiplyNumbers(
    Number(position_size),
    dir,
    minusNumbers(Number(mark_price), Number(order_price)),
  );

  return PNL;
};

export function toLocalISOString(date: Date) {
  const pad = (n: number, z = 2) => String(n).padStart(z, "0");
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(
    date.getDate(),
  )}T${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(
    date.getSeconds(),
  )}.${pad(date.getMilliseconds(), 3)}Z`;
}
