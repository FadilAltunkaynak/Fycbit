import {
  FUTURE_MARGIN_MODE,
  FUTURE_ORDER_METHOD,
  FUTURE_ORDER_TYPE,
  FUTURE_TRANSACTION_TYPE,
  TPSL_TYPE,
} from "helpers/core-constants";
import {
  addNumbers,
  calcCost,
  calcSizeForCostBuy,
  calcSizeForCostSell,
  calculateInitialMargin,
  calculateMaxAmount,
  calculatePNL,
  decimalCheckForm,
  formatAmountDecimal,
  futuresGetSlippagePriceBuy,
  getDateRange,
  getLeverageSettingsByLeverage,
  getPercantageValue,
  isOnlyNumber,
  minusNumbers,
  noExponents,
  nullChecker,
} from "helpers/functions";
import { useDebounce } from "hooks/useDebounce";
import useTranslation from "next-translate/useTranslation";
import { useCallback, useEffect, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { toast } from "react-toastify";
import {
  futureCancelOpenOrderApi,
  futureLeverageSettingUpdateApi,
  futureMarginSettingApi,
  futureOrderApi,
  futurePositionCloseApi,
  futurePositionsAdjustMarginApi,
  futurePositionsTPSLApi,
  futurePositionsTPSLCancelApi,
  FutureUserBalance,
  FutureWalletBalance,
  getFutureAllLeverageSettingsApi,
  getFutureCoinPairs,
  getFutureLeverageSettingDataApi,
  getFutureMaxLeverageApi,
  getFutureOpenOrderAndPositionAmountApi,
  getFutureOpenOrdersApi,
  getFutureOrderHistoryListsApi,
  getFuturePositionHistoryListsApi,
  getFutureTradeHistoryListsApi,
  getFutureUserAssetListsApi,
  getFutureUserAsstesSummaryApi,
  getFutureUserMarginLeverage,
  getFutureUserMarginLeverageApi,
  getFutureUserPositionListsApi,
  getFutureWalletDetailsApi,
  getFutureWalletDetailsBalanceApi,
  getUserAllFutureWalletApi,
} from "service/future-trade";
import {
  FutureCoinPair,
  FutureOpenOrder,
  FuturePosition,
  removeFutureOpenOrder,
  setFutureAssets,
  setFutureLeverageSettings,
  setFutureMargin,
  setFutureOpenOrders,
  setFutureOrderHistories,
  setFutureOrderTotalAmount,
  setFuturePairLeverage,
  setFuturePairMarginMode,
  setFuturePairs,
  setFuturePositionHistories,
  setFuturePositions,
  setFuturesBasePrice,
  setFutureTradeHistories,
  setFutureUserAsstesSummary,
  setFutureWallet,
} from "state/reducer/futureReducer";
import { RootState } from "state/store";

import * as yup from "yup";

export type FuturesOrderPayloadRaw = {
  coin_pair_uid: string;
  amount: number;
  price?: number;
  order_type: number;
  margin_mode: number;
  is_reduce: number;
  order_method: number;
  stop_price?: number;
  take_profit_price?: number;
  stop_loss_price?: number;
};

export const useFuturePairs = () => {
  const dispatch = useDispatch();

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchPairs = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureCoinPairs();

        if (!data?.success) {
          throw new Error(data?.message || "Failed to load pairs");
        }

        dispatch(setFuturePairs(data.data || []));
      } catch (err: any) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchPairs();

    const interval = setInterval(fetchPairs, 2000);

    return () => {
      clearInterval(interval);
    };
  }, []);

  return {
    loading,
    error,
  };
};

export const useProtectedFuturePairs = () => {
  const dispatch = useDispatch();

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  useEffect(() => {
    const fetchPairs = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureCoinPairs();

        if (!data?.success) {
          throw new Error(data?.message || "Failed to load pairs");
        }

        dispatch(setFuturePairs(data.data || []));
      } catch (err: any) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn) {
      fetchPairs();
    }
  }, [isLoggedIn]);

  return {
    loading,
    error,
  };
};

const futuresSchema = ({
  symbolDetails,
  orderMethod,
  tpsl,
  reduceOnly,
  t,
  stop_price,
  max_amount_value,
}: {
  symbolDetails: FutureCoinPair;
  orderMethod: FUTURE_ORDER_METHOD;
  tpsl: boolean;
  reduceOnly: boolean;
  t: any;
  stop_price: number;
  max_amount_value: number;
}) => {
  const tradeDecimal = Number(symbolDetails?.trade_decimal);
  const baseDecimal = Number(symbolDetails?.base_decimal);

  const marketPrice = Number(symbolDetails.market_price);

  const min_price_percent = Number(symbolDetails?.floor_ratio);
  const max_price_percent = Number(symbolDetails?.cap_ratio);

  const min_stop_price = formatAmountDecimal(
    minusNumbers(
      marketPrice,
      getPercantageValue(
        marketPrice,
        Number(symbolDetails?.min_stop_limit_percent),
      ),
    ),
    Number(symbolDetails?.trade_decimal),
    true,
  );

  const max_stop_price = formatAmountDecimal(
    addNumbers(
      marketPrice,
      getPercantageValue(
        marketPrice,
        Number(symbolDetails?.max_stop_limit_percent),
      ),
    ),
    Number(symbolDetails?.trade_decimal),
    true,
  );

  const min_price = formatAmountDecimal(
    minusNumbers(
      orderMethod == FUTURE_ORDER_METHOD.STOP_LIMIT ? stop_price : marketPrice,
      getPercantageValue(
        orderMethod == FUTURE_ORDER_METHOD.STOP_LIMIT
          ? stop_price
          : marketPrice,
        min_price_percent,
      ),
    ),
    Number(symbolDetails?.trade_decimal),
    true,
  );

  const max_price = formatAmountDecimal(
    addNumbers(
      orderMethod == FUTURE_ORDER_METHOD.STOP_LIMIT ? stop_price : marketPrice,
      getPercantageValue(
        orderMethod == FUTURE_ORDER_METHOD.STOP_LIMIT
          ? stop_price
          : marketPrice,
        max_price_percent,
      ),
    ),
    Number(symbolDetails?.trade_decimal),
    true,
  );

  const min_size = Number(symbolDetails?.min_amount);
  const max_size = Number(symbolDetails?.max_amount);

  return yup.object({
    stop_price: yup
      .number()
      .when([], {
        is: () => orderMethod === FUTURE_ORDER_METHOD.STOP_LIMIT,
        then: (s) =>
          s
            .required(t("Stop price is required"))
            .min(
              Number(min_stop_price),
              t("Minimum stop price is ") + min_stop_price,
            )
            .max(
              Number(max_stop_price),
              t("Maximum stop price is ") + max_stop_price,
            ),
        otherwise: (s) => s.notRequired(),
      })
      .test(
        "num",
        t("Not a valid number"),
        (v) => !v || isOnlyNumber(String(v)),
      )
      .test("zero", t("Enter a valid amount"), (v) => !v || Number(v) !== 0)
      .test("decimal", (v, ctx) => {
        if (!v) return true;
        const msg = decimalCheckForm(String(v), tradeDecimal, t);
        return msg === true || ctx.createError({ message: msg });
      }),

    price: yup
      .number()
      .when([], {
        is: () => orderMethod !== FUTURE_ORDER_METHOD.MARKET,
        then: (s) =>
          s
            .required(t("Price is required"))
            .min(Number(min_price), t("Minimum price is ") + min_price)
            .max(Number(max_price), t("Maximum price is ") + max_price),
        otherwise: (s) => s.notRequired(),
      })
      .test(
        "num",
        t("Not a valid number"),
        (v) => !v || isOnlyNumber(String(v)),
      )
      .test("zero", t("Enter a valid amount"), (v) => !v || Number(v) !== 0)
      .test("decimal", (v, ctx) => {
        if (!v) return true;
        const msg = decimalCheckForm(String(v), tradeDecimal, t);
        return msg === true || ctx.createError({ message: msg });
      }),

    amount: yup
      .number()
      .required(t("Size is required"))
      .min(min_size, t("Minimum size is ") + min_size)
      .max(max_size, t("Maximum size is ") + max_size)
      .test(
        "num",
        t("Not a valid number"),
        (v) => !v || isOnlyNumber(String(v)),
      )
      .test("zero", t("Enter a valid amount"), (v) => !v || Number(v) !== 0)
      .test("decimal", (v, ctx) => {
        if (!v) return true;
        const d = baseDecimal;
        const msg = decimalCheckForm(String(v), d, t);
        return msg === true || ctx.createError({ message: msg });
      })
      .test(
        "max-amount",
        t("Size cannot be more than max ") + max_amount_value,
        (v) => {
          if (!v || isNaN(v) || !Number(max_amount_value)) return true;
          return Number(v) <= Number(max_amount_value);
        },
      ),
    take_profit_price: yup
      .number()
      .notRequired()
      .test(
        "num",
        t("Not a valid number"),
        (v) => !v || isOnlyNumber(String(v)),
      )
      .test("zero", t("Enter a valid amount"), (v) => !v || Number(v) !== 0)
      .test("decimal", (v, ctx) => {
        if (!v) return true;
        const msg = decimalCheckForm(String(v), tradeDecimal, t);
        return msg === true || ctx.createError({ message: msg });
      }),

    stop_loss_price: yup
      .number()
      .notRequired()
      .test(
        "num",
        t("Not a valid number"),
        (v) => !v || isOnlyNumber(String(v)),
      )
      .test("zero", t("Enter a valid amount"), (v) => !v || Number(v) !== 0)
      .test("decimal", (v, ctx) => {
        if (!v) return true;
        const msg = decimalCheckForm(String(v), tradeDecimal, t);
        return msg === true || ctx.createError({ message: msg });
      }),
  });
};

export const useFuturesTradeForm = () => {
  const { t } = useTranslation("common");
  const { isLoggedIn } = useSelector((state: RootState) => state.user);
  const dispatch = useDispatch();

  const [range, setRange] = useState(0);

  const [loading, setLoading] = useState(false);

  const {
    pairDetails,
    leverageSettings,
    leverage,
    future_wallet,
    future_order_total_amount,
    basePrice,
  } = useSelector((state: RootState) => state.futureTrade);

  const avbl = future_wallet.available_balance;

  const market_price = pairDetails?.market_price
    ? Number(pairDetails?.market_price)
    : 0;
  const slippage_percent = pairDetails?.slippage_percent
    ? Number(pairDetails?.slippage_percent)
    : 0;

  const slippagePrice = futuresGetSlippagePriceBuy(
    market_price,
    slippage_percent,
  );

  const [form, setForm] = useState({
    coin_pair_uid: pairDetails?.uid || "",
    amount: undefined,
    price: undefined,
    order_method: FUTURE_ORDER_METHOD.LIMIT,
    margin_mode: 1,
    is_reduce: 0,
    stop_price: undefined,
    take_profit_price: undefined,
    stop_loss_price: undefined,
    is_tpsl: 0,
  });

  const [errors, setErrors] = useState<any>({});

  const setField = (key: string, value: any) => {
    setForm((p) => ({ ...p, [key]: value }));
  };

  useEffect(() => {
    if (basePrice) setField("price", basePrice);
  }, [basePrice]);

  const toggleTpSl = (value: boolean) => {
    setForm((prev) => ({
      ...prev,
      is_tpsl: value ? 1 : 0,
      take_profit_price: value ? prev.take_profit_price : undefined,
      stop_loss_price: value ? prev.stop_loss_price : undefined,
      is_reduce: value ? 0 : prev.is_reduce,
    }));
  };

  const toggleReduceOnly = (value: boolean) => {
    setForm((prev) => ({
      ...prev,
      is_reduce: value ? 1 : 0,
      is_tpsl: value ? 0 : prev.is_tpsl,
      take_profit_price: value ? undefined : prev.take_profit_price,
      stop_loss_price: value ? undefined : prev.stop_loss_price,
    }));
  };
  const [maxAmount, setMaxAmount] = useState(0);
  const max_amount_value = maxAmount;

  const handleSetMaxAmount = useCallback(
    (price: any) => {
      if (!isLoggedIn) return;

      if (!price || isNaN(price) || price <= 0) {
        setMaxAmount(0);
      } else {
        const levData = getLeverageSettingsByLeverage(
          Number(leverage ?? 1),
          leverageSettings ?? [],
        );

        const max_amount_main = calculateMaxAmount({
          order_price: Number(price),
          available_balance: Number(avbl),
          leverage: Number(leverage ?? 1),
          max_position_amount: Number(levData?.max_position_amount ?? 0),
          maintenance_margin_rate: Number(
            levData?.maintenance_margin_rate ?? 0,
          ),
          maintenance_amount: Number(levData?.maintenance_amount ?? 0),
          fee_percent: Number(pairDetails?.taker_fees_percent ?? 0),
        });

        setMaxAmount(max_amount_main < 0 ? 0 : Number(max_amount_main));
      }
    },
    [form.price, avbl, leverage],
  );

  const isNotMarket = form.order_method != FUTURE_ORDER_METHOD.MARKET;

  const priceToCalcBuy = isNotMarket ? Number(form.price ?? 0) : slippagePrice;

  const priceToCalcSell = isNotMarket
    ? Number(form.price ?? 0)
    : Number(market_price);

  let sizeBuy = calcSizeForCostBuy(
    future_order_total_amount.open_positions_total_amount,
    future_order_total_amount.open_buy_total_amount,
    Number(form.amount || 0),
  );
  sizeBuy = Number(
    formatAmountDecimal(sizeBuy, pairDetails?.base_decimal || 2),
  );

  let sizeSell = calcSizeForCostSell(
    future_order_total_amount.open_positions_total_amount,
    future_order_total_amount.open_sell_total_amount,
    Number(form.amount || 0),
  );
  sizeSell = Number(
    formatAmountDecimal(sizeSell, pairDetails?.base_decimal || 2),
  );

  const [cost, setCost] = useState({ buy: "0", sell: "0" });

  useEffect(() => {
    const { buy, sell } = calcCost({
      order_method: form.order_method,
      price: Number(form.price || 0),
      sizeInput: Number(form.amount || 0),
      priceToCalcBuy,
      priceToCalcSell,
      mark_price: Number(pairDetails?.mark_price),
      leverage: Number(leverage || 1),
      trade_decimal: pairDetails?.trade_decimal || 2,
      sizeBuy,
      sizeSell,
      fee_percent: Number(pairDetails?.taker_fees_percent) || 0,
    });

    setCost({ buy: buy, sell: sell });
  }, [
    form.order_method,
    form.price,
    form.amount,
    priceToCalcBuy,
    priceToCalcSell,
    pairDetails?.mark_price,
    market_price,
    leverage,
    pairDetails?.trade_decimal,
    sizeBuy,
    sizeSell,
  ]);

  useEffect(() => {
    if (form.order_method == FUTURE_ORDER_METHOD.MARKET) {
      handleSetMaxAmount(slippagePrice);
    } else {
      handleSetMaxAmount(form.price);
    }
  }, [slippagePrice, form.order_method, form.price, avbl, leverage]);

  const onRangeUpdate = (v: number) => {
    setRange(v);

    let size: number | string = getPercantageValue(Number(max_amount_value), v);

    size = formatAmountDecimal(
      size,
      Number(pairDetails?.base_decimal || 2),
      true,
    );

    setField("amount", Number(size));
  };

  const validate = async () => {
    try {
      if (!pairDetails?.uid) return;
      await futuresSchema({
        orderMethod: form.order_method,
        reduceOnly: Boolean(form.is_reduce),
        tpsl: Boolean(form.is_tpsl),
        t: t,
        symbolDetails: pairDetails,
        stop_price: form.stop_price ? Number(form.stop_price) : 0,
        max_amount_value: max_amount_value,
      }).validate(form, { abortEarly: false });

      setErrors({});

      return true;
    } catch (e: any) {
      const err: any = {};

      e.inner.forEach((x: any) => {
        err[x.path] = x.message;
      });

      setErrors(err);

      return false;
    }
  };

  const isCostBuyError = Number(cost.buy) > Number(avbl);
  const isCostSellError = Number(cost.sell) > Number(avbl);

  const onSubmit = async (order_type: FUTURE_ORDER_TYPE) => {
    setLoading(true);
    const ok = await validate();
    if (!ok) {
      setLoading(false);

      return false;
    }
    const { is_tpsl, ...rest } = form;

    const payload = {
      ...rest,
      coin_pair_uid: pairDetails?.uid || "",
      amount: Number(form.amount),
      price: form.price ? Number(form.price) : undefined,
      order_method: form.order_method,
      margin_mode: form.margin_mode,
      is_reduce: form.is_reduce,
      stop_price: form.stop_price ? Number(form.stop_price) : undefined,
      take_profit_price: form.take_profit_price
        ? Number(form.take_profit_price)
        : undefined,
      stop_loss_price: form.stop_loss_price
        ? Number(form.stop_loss_price)
        : undefined,

      order_type: order_type,
    };

    const tp = Number(payload.take_profit_price || 0);
    const sl = Number(payload.stop_loss_price || 0);

    if (Number(leverage) > Number(pairDetails?.max_leverage || 0)) {
      toast.error(t("Invalid leverage. Please adjust leverage."));
      setLoading(false);

      return;
    }

    if (
      Number(cost.buy) > Number(avbl) &&
      order_type == FUTURE_ORDER_TYPE.BUY
    ) {
      toast.error(
        t("Cost cannot be more than available balance. Please adjust."),
      );
      setLoading(false);

      return;
    }

    if (
      Number(cost.sell) > Number(avbl) &&
      order_type == FUTURE_ORDER_TYPE.SELL
    ) {
      toast.error(
        t("Cost cannot be more than available balance. Please adjust."),
      );
      setLoading(false);

      return;
    }

    const priceToCompare = Number(pairDetails?.mark_price);

    const priceRendered = noExponents(
      formatAmountDecimal(
        priceToCompare,
        Number(pairDetails?.trade_decimal),
        true,
      ),
    );

    // check tpsl
    if (is_tpsl && tp) {
      if (order_type == FUTURE_ORDER_TYPE.BUY && tp <= priceToCompare) {
        setErrors((prev: any) => ({
          ...prev,
          take_profit_price:
            t("Price should be more than") + " " + priceRendered,
        }));
        setLoading(false);

        return;
      }

      if (order_type == FUTURE_ORDER_TYPE.SELL && tp >= priceToCompare) {
        setErrors((prev: any) => ({
          ...prev,
          take_profit_price:
            t("Price should be less than") + " " + priceRendered,
        }));
        setLoading(false);

        return;
      }
    }

    if (is_tpsl && sl) {
      if (order_type == FUTURE_ORDER_TYPE.SELL && sl <= priceToCompare) {
        setErrors((prev: any) => ({
          ...prev,
          stop_loss_price: t("Price should be more than") + " " + priceRendered,
        }));
        setLoading(false);

        return;
      }

      if (order_type == FUTURE_ORDER_TYPE.BUY && sl >= priceToCompare) {
        setErrors((prev: any) => ({
          ...prev,
          stop_loss_price: t("Price should be less than") + " " + priceRendered,
        }));
        setLoading(false);

        return;
      }
    }

    try {
      const res = await futureOrderApi(payload);

      if (!res.success) {
        toast.error(res.message);
        return;
      }

      toast.success(res.message);

      dispatch(setFuturesBasePrice(null));
    } catch (error) {
      toast.error("Something went wrong");
    } finally {
      setLoading(false);
    }
  };

  return {
    form,
    errors,
    setField,
    onSubmit,
    toggleTpSl,
    toggleReduceOnly,
    onRangeUpdate,
    range,
    marketPrice: market_price,
    loading,
    max_amount_value,
    cost,
    isCostBuyError,
    isCostSellError,
  };
};

export const useFutureUserMarginLeverage = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);
  const { pairDetails } = useSelector((state: RootState) => state.futureTrade);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchFutureUserMarginLeverage = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureUserMarginLeverage(
          pairDetails?.uid || "",
        );

        if (!data?.success) {
          throw new Error(data?.message || "Failed to margin leverage");
        }

        dispatch(setFuturePairLeverage(data.data?.leverage || 1));
        dispatch(
          setFuturePairMarginMode(
            data.data?.margin_mode || FUTURE_MARGIN_MODE.CROSS,
          ),
        );
      } catch (err: any) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn) {
      fetchFutureUserMarginLeverage();
    }
  }, [isLoggedIn, pairDetails?.uid]);

  return {
    loading,
    error,
  };
};

export const useFutureMarginSettingUpdate = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);
  const { pairDetails } = useSelector((state: RootState) => state.futureTrade);

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const [mode, setMode] = useState(FUTURE_MARGIN_MODE.CROSS);

  const onChangeMode = (value: FUTURE_MARGIN_MODE) => {
    setMode(value);
  };

  const onUpdateFutureMarginSetting = async () => {
    try {
      setLoading(true);
      const { data } = await futureMarginSettingApi({
        coin_pair_uid: pairDetails?.uid || "",
        margin_mode: mode,
      });

      if (!data?.success) {
        toast.error(data?.message || "Failed to update margin setting");
        return;
      }

      toast.success(data?.message || "Successfully updated");

      dispatch(setFuturePairMarginMode(mode));
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return {
    loading,
    error,
    onChangeMode,
    mode,
    onUpdateFutureMarginSetting,
  };
};

export const useGetFutureAllLeverageSettings = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);
  const { pairDetails } = useSelector((state: RootState) => state.futureTrade);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchFutureAllLeverageSettings = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureAllLeverageSettingsApi(
          pairDetails?.uid || "",
        );

        if (!data?.success) {
          throw new Error(data?.message || "Failed to margin leverage");
        }

        dispatch(setFutureLeverageSettings(data.data || []));
      } catch (err: any) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn && pairDetails?.uid) {
      fetchFutureAllLeverageSettings();
    }
  }, [isLoggedIn, pairDetails?.uid]);

  return {
    loading,
    error,
  };
};

const futuresLeverageSettingSchema = ({
  symbolDetails,
  t,
}: {
  symbolDetails: FutureCoinPair;
  t: any;
}) => {
  const max_leverage = symbolDetails.max_leverage
    ? Number(symbolDetails.max_leverage)
    : 1;

  return yup.object({
    leverage: yup
      .number()
      .required(t("Leverage is required"))
      .min(1, t("Leverage cannot be less than 1"))
      .max(max_leverage, t("Leverage cannot be more than ") + max_leverage)
      .test(
        "num",
        t("Not a valid number"),
        (v) => !v || isOnlyNumber(String(v)),
      )
      .test("zero", t("Enter a valid amount"), (v) => !v || Number(v) !== 0)
      .test("decimal", (v, ctx) => {
        if (!v) return true;
        const msg = decimalCheckForm(String(v), 0, t);
        return msg === true || ctx.createError({ message: msg });
      }),
  });
};

export const useFuturesLeverageSetting = () => {
  const dispatch = useDispatch();

  const { t } = useTranslation("common");

  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const [loading, setLoading] = useState(false);
  const [isGettingValue, setIsGettingValue] = useState(false);
  const [isGettingMaxValue, setIsGettingMaxValue] = useState(false);

  const [maxPositionAmount, setMaxPositionAmount] = useState(0);
  const [maxLeverage, setMaxLeverage] = useState<number>(0);

  const { pairDetails, leverage: defaultLeverage } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  const [form, setForm] = useState({
    coin_pair_uid: pairDetails?.uid || "",
    leverage: defaultLeverage || 1,
  });

  const debouncedLeverage = useDebounce(form.leverage, 500);

  useEffect(() => {
    const fetchFutureLeverageSettings = async () => {
      try {
        setIsGettingValue(true);
        const { data } = await getFutureLeverageSettingDataApi(
          pairDetails?.uid || "",
          debouncedLeverage,
        );

        if (!data?.success) return;
        setMaxPositionAmount(data?.data?.max_position_amount || 0);
      } catch (error) {
        setIsGettingValue(false);
      } finally {
        setIsGettingValue(false);
      }
    };

    if (isLoggedIn && pairDetails?.uid) {
      fetchFutureLeverageSettings();
    }
  }, [isLoggedIn, pairDetails?.uid, debouncedLeverage]);

  useEffect(() => {
    const fetchFutureMaxLeverage = async () => {
      try {
        setIsGettingMaxValue(true);
        const { data } = await getFutureMaxLeverageApi(pairDetails?.uid || "");

        if (!data?.success) return;
        setMaxLeverage(data?.data?.max_leverage || 1);
      } catch (error) {
        setIsGettingMaxValue(false);
      } finally {
        setIsGettingMaxValue(false);
      }
    };

    if (isLoggedIn && pairDetails?.uid) {
      fetchFutureMaxLeverage();
    }
  }, [isLoggedIn, pairDetails?.uid]);

  const [errors, setErrors] = useState<any>({});

  const setField = (key: string, value: any) => {
    setForm((p) => ({ ...p, [key]: value }));
  };

  const validate = async () => {
    try {
      if (!pairDetails?.uid) return;
      await futuresLeverageSettingSchema({
        symbolDetails: pairDetails,
        t: t,
      }).validate(form, { abortEarly: false });

      setErrors({});

      return true;
    } catch (e: any) {
      const err: any = {};

      e.inner.forEach((x: any) => {
        err[x.path] = x.message;
      });

      setErrors(err);

      return false;
    }
  };

  const onSubmit = async (onSuccessCallback?: () => void) => {
    setLoading(true);
    const ok = await validate();
    if (!ok) {
      setLoading(false);

      return false;
    }

    try {
      const { data } = await futureLeverageSettingUpdateApi(form);

      if (!data.success) {
        toast.error(data.message);
        return;
      }
      dispatch(setFuturePairLeverage(form.leverage || 1));

      onSuccessCallback?.();

      toast.success(data.message);
    } catch (error) {
      toast.error("Something went wrong");
    } finally {
      setLoading(false);
    }
  };

  return {
    form,
    errors,
    setField,
    onSubmit,
    loading,
    isGettingValue,
    maxPositionAmount,
    isGettingMaxValue,
    maxLeverage,
  };
};

export const useGetFutureWalletDetails = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchFutureWalletDetails = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureWalletDetailsApi();

        if (!data?.success) {
          throw new Error(data?.message || "Failed to margin leverage");
        }

        dispatch(
          setFutureWallet(
            data.data || {
              available_balance: 0,
              balance: 0,
              cost: 0,
            },
          ),
        );
      } catch (err: any) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn) {
      fetchFutureWalletDetails();
    }
  }, [isLoggedIn]);

  return {
    loading,
    error,
  };
};

export const useGetFutureOpenOrderAndPositionAmount = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);
  const { pairDetails } = useSelector((state: RootState) => state.futureTrade);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchFutureOpenOrderAndPositionAmount = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureOpenOrderAndPositionAmountApi(
          pairDetails?.uid || "",
        );

        if (!data?.success) {
          throw new Error(
            data?.message || "Failed to get open order and position cost",
          );
        }

        dispatch(
          setFutureOrderTotalAmount(
            data.data || {
              open_buy_total_amount: 0,
              open_sell_total_amount: 0,
              open_positions_total_amount: 0,
            },
          ),
        );
      } catch (err: any) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn && pairDetails?.uid) {
      fetchFutureOpenOrderAndPositionAmount();
    }
  }, [isLoggedIn, pairDetails?.uid]);

  return {
    loading,
    error,
  };
};

export const useGetFutureOpenOrderLists = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const [paginations, setPaginations] = useState({
    per_page: 20,
    total: 0,
    current_page: 1,
  });

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [filters, setFilters] = useState({
    limit: 20,
    page: 1,
    symbol: undefined,
    order_method: undefined,
    side: undefined,
  });

  useEffect(() => {
    const fetchFutureOpenOrderLists = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureOpenOrdersApi(
          filters.page,
          filters.limit,
          filters.side,
          filters.order_method,
          filters.symbol,
        );

        if (!data?.success) {
          toast.error(data.message);
          return;
        }

        dispatch(setFutureOpenOrders(data.data.data || []));
        setPaginations({
          current_page: data.data.current_page,
          per_page: data.data.per_page,
          total: data.data.total,
        });
      } catch (err: any) {
        toast.error("Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn) {
      fetchFutureOpenOrderLists();
    }
  }, [isLoggedIn, filters]);

  const setFilter = (key: string, value: string) => {
    setFilters((prev) => ({
      ...prev,
      [key]: value === "All" || value === "" ? undefined : value,
      page: 1,
    }));
  };

  const setPage = (page: number) => {
    setFilters((prev) => ({
      ...prev,
      page,
    }));
  };

  return {
    loading,
    error,
    setFilter,
    setPage,
    filters,
    paginations,
  };
};

export const useGetFutureOrderHistoryLists = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [paginations, setPaginations] = useState({
    per_page: 20,
    total: 0,
    current_page: 1,
  });

  const [dates, setDates] = useState<{
    start: Date;
    end: Date;
    days?: number;
  }>(getDateRange(7));

  const [pickerDates, setPickerDates] = useState<{
    start?: Date;
    end?: Date;
  }>();

  const [filters, setFilters] = useState({
    limit: 20,
    page: 1,
    symbol: undefined,
    order_method: undefined,
    side: undefined,
  });

  useEffect(() => {
    const fetchFutureOrderHistoryLists = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureOrderHistoryListsApi(
          filters.page,
          filters.limit,
          filters.side,
          filters.order_method,
          filters.symbol,
          dates.start,
          dates.end,
        );

        if (!data?.success) {
          toast.error(data.message);
          return;
        }
        dispatch(setFutureOrderHistories(data.data.data || []));
        setPaginations({
          current_page: data.data.current_page,
          per_page: data.data.per_page,
          total: data.data.total,
        });
      } catch (err: any) {
        toast.error("Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn) {
      fetchFutureOrderHistoryLists();
    }
  }, [isLoggedIn, filters, dates]);

  const setFilter = (key: string, value: string) => {
    setFilters((prev) => ({
      ...prev,
      [key]: value === "All" || value === "" ? undefined : value,
      page: 1,
    }));
  };

  const setPage = (page: number) => {
    setFilters((prev) => ({
      ...prev,
      page,
    }));
  };

  return {
    loading,
    error,
    setFilter,
    setPage,
    filters,
    setDates,
    dates,
    setPickerDates,
    pickerDates,
    paginations,
  };
};

export const useGetFutureTradeHistoryLists = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [dates, setDates] = useState<{
    start: Date;
    end: Date;
    days?: number;
  }>(getDateRange(7));

  const [pickerDates, setPickerDates] = useState<{
    start?: Date;
    end?: Date;
  }>();
  const [paginations, setPaginations] = useState({
    per_page: 20,
    total: 0,
    current_page: 1,
  });
  const [filters, setFilters] = useState({
    limit: 20,
    page: 1,
    symbol: undefined,
    side: undefined,
  });

  useEffect(() => {
    const fetchFutureTradeHistoryLists = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureTradeHistoryListsApi(
          filters.page,
          filters.limit,
          filters.side,
          filters.symbol,
          dates.start,
          dates.end,
        );

        if (!data?.success) {
          toast.error(data.message);
          return;
        }
        dispatch(setFutureTradeHistories(data.data.data || []));
        setPaginations({
          current_page: data.data.current_page,
          per_page: data.data.per_page,
          total: data.data.total,
        });
      } catch (err: any) {
        toast.error("Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn) {
      fetchFutureTradeHistoryLists();
    }
  }, [isLoggedIn, filters, dates]);

  const setFilter = (key: string, value: string) => {
    setFilters((prev) => ({
      ...prev,
      [key]: value === "All" || value === "" ? undefined : value,
      page: 1,
    }));
  };

  const setPage = (page: number) => {
    setFilters((prev) => ({
      ...prev,
      page,
    }));
  };

  return {
    loading,
    error,
    setFilter,
    setPage,
    filters,
    setDates,
    dates,
    setPickerDates,
    pickerDates,
    paginations,
  };
};

export const useGetFuturePositionHistoryLists = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [dates, setDates] = useState<{
    start: Date;
    end: Date;
    days?: number;
  }>(getDateRange(7));

  const [pickerDates, setPickerDates] = useState<{
    start?: Date;
    end?: Date;
  }>();

  const [paginations, setPaginations] = useState({
    per_page: 20,
    total: 0,
    current_page: 1,
  });

  const [filters, setFilters] = useState({
    limit: 20,
    page: 1,
    symbol: undefined,
  });

  useEffect(() => {
    const fetchLists = async () => {
      try {
        setLoading(true);
        const { data } = await getFuturePositionHistoryListsApi(
          filters.page,
          filters.limit,
          filters.symbol,
          dates.start,
          dates.end,
        );

        if (!data?.success) {
          toast.error(data.message);
          return;
        }
        dispatch(setFuturePositionHistories(data.data.data || []));
        setPaginations({
          current_page: data.data.current_page,
          per_page: data.data.per_page,
          total: data.data.total,
        });
      } catch (err: any) {
        toast.error("Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn) {
      fetchLists();
    }
  }, [isLoggedIn, filters, dates]);

  const setFilter = (key: string, value: string) => {
    setFilters((prev) => ({
      ...prev,
      [key]: value === "All" || value === "" ? undefined : value,
      page: 1,
    }));
  };

  const setPage = (page: number) => {
    setFilters((prev) => ({
      ...prev,
      page,
    }));
  };

  return {
    loading,
    error,
    setFilter,
    setPage,
    filters,
    setDates,
    dates,
    setPickerDates,
    pickerDates,
    paginations,
  };
};

export const useGetFutureUserAssetLists = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchLists = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureUserAssetListsApi();

        if (!data?.success) {
          toast.error(data.message);
          return;
        }
        dispatch(
          setFutureAssets(
            data.data || {
              coin: "N/A",
              available_balance: "0",
              in_order_cost: "0",
              total: "0",
              btc: "0",
              btc_to_currency: "N/A",
            },
          ),
        );
      } catch (err: any) {
        toast.error("Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn) {
      fetchLists();
    }
  }, [isLoggedIn]);

  return {
    loading,
    error,
  };
};

export const useGetFutureUserMarginLeverage = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const { pairDetails } = useSelector((state: RootState) => state.futureTrade);

  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchApi = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureUserMarginLeverageApi(
          pairDetails?.uid || "",
        );

        if (!data?.success) {
          toast.error(data.message);
          return;
        }
        dispatch(
          setFutureMargin(
            data.data || {
              maintenance_margin: "0",
              margin_balance: "0",
              margin_ratio: "0",
              wallet_balance: "0",
            },
          ),
        );
      } catch (err: any) {
        toast.error("Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn && pairDetails?.uid) {
      fetchApi();
    }
  }, [isLoggedIn, pairDetails?.uid]);

  return {
    loading,
  };
};

export const useGetFutureUserPositionLists = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchLists = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureUserPositionListsApi();

        if (!data?.success) {
          toast.error(data.message);
          return;
        }
        dispatch(setFuturePositions(data.data || []));
      } catch (err: any) {
        toast.error("Something went wrong");
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn) {
      fetchLists();
    }
  }, [isLoggedIn]);

  return {
    loading,
  };
};

export const useFutureWalletDetailsBalance = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const [walletBalance, setWalletBalance] = useState<FutureUserBalance>({
    available_balance: "0",
    coin_type: "USDT",
    in_order_balance: "0.00000000",
    today_pnl: "0",
    total_balance: "0",
  });

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchApi = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureWalletDetailsBalanceApi();

        if (!data?.success) {
          throw new Error(
            data?.message || "Failed to get future wallet details balance",
          );
        }

        setWalletBalance(data.data);
      } catch (err: any) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn) {
      fetchApi();
    }
  }, [isLoggedIn]);

  return {
    loading,
    error,
    walletBalance,
  };
};

export const useUserAllFutureWallet = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const [userAllFutureWallet, setUserAllFutureWallet] = useState<
    FutureWalletBalance[]
  >([]);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchApi = async () => {
      try {
        setLoading(true);
        const { data } = await getUserAllFutureWalletApi();

        if (!data?.success) {
          throw new Error(data?.message || "Failed to get user future wallets");
        }

        setUserAllFutureWallet([data.data]);
      } catch (err: any) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn) {
      fetchApi();
    }
  }, [isLoggedIn]);

  return {
    loading,
    error,
    userAllFutureWallet,
  };
};

const futuresPositionsTPSLFormSchema = ({
  symbolDetails,
  t,
}: {
  symbolDetails: FuturePosition;
  t: any;
}) => {
  const tradeDecimal = Number(symbolDetails?.trade_decimal);

  return yup.object({
    take_profit_price: yup
      .number()
      .notRequired()
      .test(
        "num",
        t("Not a valid number"),
        (v) => !v || isOnlyNumber(String(v)),
      )
      .test("zero", t("Enter a valid amount"), (v) => !v || Number(v) !== 0)
      .test("decimal", (v, ctx) => {
        if (!v) return true;
        const msg = decimalCheckForm(String(v), tradeDecimal, t);
        return msg === true || ctx.createError({ message: msg });
      }),

    stop_loss_price: yup
      .number()
      .notRequired()
      .test(
        "num",
        t("Not a valid number"),
        (v) => !v || isOnlyNumber(String(v)),
      )
      .test("zero", t("Enter a valid amount"), (v) => !v || Number(v) !== 0)
      .test("decimal", (v, ctx) => {
        if (!v) return true;
        const msg = decimalCheckForm(String(v), tradeDecimal, t);
        return msg === true || ctx.createError({ message: msg });
      }),
  });
};

export const usePositionsTPSLForm = (
  item: FuturePosition,
  onClose: () => void,
) => {
  const { t } = useTranslation("common");
  const dispatch = useDispatch();

  const [loading, setLoading] = useState(false);

  const trade_decimal = Number(item.trade_decimal);
  const order_type = item.order_type;

  const [form, setForm] = useState({
    take_profit_price: Boolean(Number(item.tp_price))
      ? Number(item.tp_price)
      : undefined,
    stop_loss_price: Boolean(Number(item.sl_price))
      ? Number(item.sl_price)
      : undefined,
  });

  const [errors, setErrors] = useState<any>({});

  const [pnl, setPnl] = useState({ tp: "0", sl: "0" });

  const onTpChange = (v: number) => {
    const tp = calculatePNL({
      mark_price: v,
      order_price: Number(item.price),
      order_type: item.order_type,
      position_size: Number(item.amount || 0),
    });

    setPnl((v) => ({
      ...v,
      tp: !v ? "0" : formatAmountDecimal(tp, trade_decimal, true),
    }));
  };

  const onSlChange = (v: number) => {
    const sl = calculatePNL({
      mark_price: v,
      order_price: Number(item.price),
      order_type: item.order_type,
      position_size: Number(item.amount || 0),
    });

    setPnl((v) => ({
      ...v,
      sl: !v ? "0" : formatAmountDecimal(sl, trade_decimal, true),
    }));
  };

  useEffect(() => {
    item.tp_price && onTpChange(Number(item.tp_price));
    item.sl_price && onSlChange(Number(item.sl_price));
  }, []);

  const setField = (key: string, value: any) => {
    setForm((p) => ({ ...p, [key]: value }));
  };

  const isValidForm =
    Number(form.take_profit_price) || Number(form.stop_loss_price);

  const validate = async () => {
    try {
      if (!item?.uid) return;
      await futuresPositionsTPSLFormSchema({
        t: t,
        symbolDetails: item,
      }).validate(form, { abortEarly: false });

      setErrors({});

      return true;
    } catch (e: any) {
      const err: any = {};

      e.inner.forEach((x: any) => {
        err[x.path] = x.message;
      });

      setErrors(err);

      return false;
    }
  };

  const onSubmit = async () => {
    setLoading(true);
    const ok = await validate();
    if (!ok) {
      setLoading(false);

      return false;
    }

    const payload = {
      position_uid: item.uid,
      tp_price: form.take_profit_price
        ? Number(form.take_profit_price)
        : undefined,
      sl_price: form.stop_loss_price ? Number(form.stop_loss_price) : undefined,
    };

    const tp = Number(payload.tp_price || 0);
    const sl = Number(payload.sl_price || 0);

    const priceToCompare = Number(item?.mark_price);

    const priceRendered = noExponents(
      formatAmountDecimal(priceToCompare, Number(item?.trade_decimal), true),
    );

    // check tpsl
    if (tp) {
      if (order_type == FUTURE_ORDER_TYPE.BUY && tp <= priceToCompare) {
        setErrors((prev: any) => ({
          ...prev,
          take_profit_price:
            t("Price should be more than") + " " + priceRendered,
        }));
        setLoading(false);

        return;
      }

      if (order_type == FUTURE_ORDER_TYPE.SELL && tp >= priceToCompare) {
        setErrors((prev: any) => ({
          ...prev,
          take_profit_price:
            t("Price should be less than") + " " + priceRendered,
        }));
        setLoading(false);

        return;
      }
    }

    if (sl) {
      if (order_type == FUTURE_ORDER_TYPE.SELL && sl <= priceToCompare) {
        setErrors((prev: any) => ({
          ...prev,
          stop_loss_price: t("Price should be more than") + " " + priceRendered,
        }));
        setLoading(false);

        return;
      }

      if (order_type == FUTURE_ORDER_TYPE.BUY && sl >= priceToCompare) {
        setErrors((prev: any) => ({
          ...prev,
          stop_loss_price: t("Price should be less than") + " " + priceRendered,
        }));
        setLoading(false);

        return;
      }
    }

    try {
      const res = await futurePositionsTPSLApi(payload);

      if (!res.success) {
        toast.error(res.message);
        return;
      }
      toast.success(res.message);
      onClose();
    } catch (error) {
      toast.error("Something went wrong");
    } finally {
      setLoading(false);
    }
  };

  const onCancel = async (type: TPSL_TYPE) => {
    setLoading(true);

    const payload = {
      position_uid: item.uid,
      type: type,
    };

    try {
      const res = await futurePositionsTPSLCancelApi(payload);

      if (!res.success) {
        toast.error(res.message);
        return;
      }
      toast.success(res.message);

      if (TPSL_TYPE.STOP_LOSS === type) {
        setField("stop_loss_price", undefined);
        onSlChange(Number(0));
        return;
      }
      setField("take_profit_price", undefined);
      onTpChange(Number(0));
    } catch (error) {
      toast.error("Something went wrong");
    } finally {
      setLoading(false);
    }
  };

  return {
    form,
    errors,
    setField,
    onSubmit,
    isValidForm,
    loading,
    pnl,
    onSlChange,
    onTpChange,
    onCancel,
  };
};

export const useCancelOpenOrders = (
  item: FutureOpenOrder,
  onClose: () => void,
) => {
  const [loading, setLoading] = useState(false);
  const dispatch = useDispatch();

  const onCancel = async (order_type: FUTURE_ORDER_TYPE) => {
    setLoading(true);

    const payload = {
      order_uid: item.uid,
      order_type: order_type,
    };

    try {
      const res = await futureCancelOpenOrderApi(payload);

      if (!res.success) {
        toast.error(res.message);
        return;
      }
      toast.success(res.message);

      dispatch(removeFutureOpenOrder(item.uid));

      onClose();
    } catch (error) {
      toast.error("Something went wrong");
    } finally {
      setLoading(false);
    }
  };

  return {
    loading,
    onCancel,
  };
};

const futuresPositionCloseSchema = ({
  item,
  t,
  orderMethod,
}: {
  item: FuturePosition;
  t: any;
  orderMethod: number;
}) => {
  const tradeDecimal = Number(item?.trade_decimal);
  const baseDecimal = Number(item?.base_decimal);

  const amount = formatAmountDecimal(
    Math.abs(Number(item.amount)),
    baseDecimal,
    true,
  );

  return yup.object({
    price: yup
      .number()
      .when([], {
        is: () => orderMethod !== FUTURE_ORDER_METHOD.MARKET,
        then: (s) =>
          s.required(t("Price is required")).min(0, t("Minimum price is 0")),
        otherwise: (s) => s.notRequired(),
      })
      .test(
        "num",
        t("Not a valid number"),
        (v) => !v || isOnlyNumber(String(v)),
      )
      .test("zero", t("Enter a valid amount"), (v) => !v || Number(v) !== 0)
      .test("decimal", (v, ctx) => {
        if (!v) return true;
        const msg = decimalCheckForm(String(v), tradeDecimal, t);
        return msg === true || ctx.createError({ message: msg });
      }),

    amount: yup
      .number()
      .min(0, t("Minimum size is 0"))
      .max(Number(amount), t("Maximum size is ") + amount)
      .test(
        "num",
        t("Not a valid number"),
        (v) => !v || isOnlyNumber(String(v)),
      )
      .test("zero", t("Enter a valid amount"), (v) => !v || Number(v) !== 0)
      .test("decimal", (v, ctx) => {
        if (!v) return true;
        const d = baseDecimal;
        const msg = decimalCheckForm(String(v), d, t);
        return msg === true || ctx.createError({ message: msg });
      }),
  });
};

export const useFuturesPositionCloseForm = (item: FuturePosition) => {
  const { t } = useTranslation("common");
  const { isLoggedIn } = useSelector((state: RootState) => state.user);
  const [loading, setLoading] = useState(false);

  const [form, setForm] = useState({
    coin_pair_uid: item?.coin_pair_uid || "",
    amount: undefined,
    price: undefined,
    margin_mode: item.margin_mode,
    is_reduce: 1,
    order_type:
      item.order_type === FUTURE_ORDER_TYPE.BUY
        ? FUTURE_ORDER_TYPE.SELL
        : FUTURE_ORDER_TYPE.BUY,
  });

  const [errors, setErrors] = useState<any>({});

  const setField = (key: string, value: any) => {
    setForm((p) => ({ ...p, [key]: value }));
  };

  const validate = async (order_method: number) => {
    try {
      if (order_method === FUTURE_ORDER_METHOD.MARKET) {
        setErrors({});
        return true;
      }
      if (!item?.coin_pair_uid) return;
      await futuresPositionCloseSchema({
        orderMethod: order_method,
        t: t,
        item: item,
      }).validate(form, { abortEarly: false });

      setErrors({});

      return true;
    } catch (e: any) {
      const err: any = {};

      e.inner.forEach((x: any) => {
        err[x.path] = x.message;
      });

      setErrors(err);

      return false;
    }
  };

  const onSubmit = async (order_method: FUTURE_ORDER_METHOD) => {
    setLoading(true);
    const ok = await validate(order_method);

    if (!ok) {
      setLoading(false);

      return false;
    }

    const amount = formatAmountDecimal(
      Math.abs(Number(item.amount)),
      item?.base_decimal,
      true,
    );

    const payload = {
      coin_pair_uid: item?.coin_pair_uid || "",
      amount:
        order_method === FUTURE_ORDER_METHOD.LIMIT
          ? Boolean(Number(form.amount))
            ? Number(form.amount)
            : Number(amount)
          : Number(amount),
      price: form.price ? Number(form.price) : undefined,
      order_method: order_method,
      margin_mode: form.margin_mode,
      is_reduce: form.is_reduce,
      order_type: form.order_type,
    };

    try {
      const res = await futurePositionCloseApi(payload);

      if (!res.success) {
        toast.error(res.message);
        return;
      }

      toast.success(res.message);
    } catch (error) {
      toast.error("Something went wrong");
    } finally {
      setLoading(false);
    }
  };

  return {
    form,
    errors,
    setField,
    onSubmit,
    loading,
  };
};

export const useGetFutureUserAsstesSummary = () => {
  const dispatch = useDispatch();

  const { isLoggedIn } = useSelector((state: RootState) => state.user);

  const { pairDetails } = useSelector((state: RootState) => state.futureTrade);

  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchApi = async () => {
      try {
        setLoading(true);
        const { data } = await getFutureUserAsstesSummaryApi(
          pairDetails?.uid || "",
        );

        if (!data?.success) {
          return;
        }
        dispatch(
          setFutureUserAsstesSummary(
            data.data || {
              balance: "0.00",
              trade_coin_code: "USDT",
              unrealized_pnl: "0.00",
            },
          ),
        );
      } catch (err: any) {
      } finally {
        setLoading(false);
      }
    };

    if (isLoggedIn && pairDetails?.uid) {
      fetchApi();
    }
  }, [isLoggedIn, pairDetails?.uid]);

  return {
    loading,
  };
};

const futuresPositionsAdjustMarginFormSchema = ({
  item,
  t,
  max_balance,
}: {
  item: FuturePosition;
  t: any;
  max_balance: number;
}) => {
  const tradeDecimal = Number(item?.trade_decimal);

  return yup.object({
    amount: yup
      .number()
      .required(t("Amount is required"))
      .max(Number(max_balance), t("Maximum stop price is ") + max_balance)
      .test(
        "num",
        t("Not a valid number"),
        (v) => !v || isOnlyNumber(String(v)),
      )
      .test("zero", t("Enter a valid amount"), (v) => !v || Number(v) !== 0)
      .test("decimal", (v, ctx) => {
        if (!v) return true;
        const msg = decimalCheckForm(String(v), tradeDecimal, t);
        return msg === true || ctx.createError({ message: msg });
      }),
  });
};

export const usePositionsAdjustMarginForm = (
  item: FuturePosition,
  onClose: () => void,
) => {
  const { t } = useTranslation("common");

  const [loading, setLoading] = useState(false);
  const { future_wallet } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  const avbl = future_wallet.available_balance;

  const [form, setForm] = useState({
    coin_pair_uid: item.coin_pair_uid,
    amount: undefined,
    action: FUTURE_TRANSACTION_TYPE.CREDIT,
  });

  const trade_currency = nullChecker(item.trade_coin_code);
  const trade_decimal = Number(item.trade_decimal);

  const liq_price = formatAmountDecimal(
    Number(item.liquidation_price),
    trade_decimal,
    true,
  );
  const margin_balance_calculated = formatAmountDecimal(
    Number(item.margin),
    trade_decimal,
    true,
  );

  const removeable_balance = minusNumbers(
    Number(item.margin),
    calculateInitialMargin({
      price: Number(item.price),
      amount: Number(item.amount),
      leverage: Number(item.leverage),
    }),
  );

  let max_balance_value = 0;

  if (form.action == FUTURE_TRANSACTION_TYPE.CREDIT) {
    max_balance_value = Number(avbl);
  } else if (form.action == FUTURE_TRANSACTION_TYPE.DEBIT) {
    max_balance_value = removeable_balance;
  }

  const max_balance = formatAmountDecimal(
    max_balance_value < 0 ? 0 : max_balance_value,
    trade_decimal,
    true,
  );

  const [errors, setErrors] = useState<any>({});

  const setField = (key: string, value: any) => {
    setForm((p) => ({ ...p, [key]: value }));
  };

  const isValidForm = Number(form.amount) || Number(form.action);

  const validate = async () => {
    try {
      if (!item?.coin_pair_uid) return;
      await futuresPositionsAdjustMarginFormSchema({
        t: t,
        item: item,
        max_balance: Number(max_balance),
      }).validate(form, { abortEarly: false });

      setErrors({});

      return true;
    } catch (e: any) {
      const err: any = {};

      e.inner.forEach((x: any) => {
        err[x.path] = x.message;
      });

      setErrors(err);

      return false;
    }
  };

  const onSubmit = async () => {
    setLoading(true);
    const ok = await validate();
    if (!ok) {
      setLoading(false);

      return false;
    }

    const payload = {
      coin_pair_uid: item.coin_pair_uid,
      amount: Number(form.amount || 0),
      action: form.action,
    };

    try {
      const res = await futurePositionsAdjustMarginApi(payload);

      if (!res.success) {
        toast.error(res.message);
        return;
      }
      toast.success(res.message);
      onClose();
    } catch (error) {
      toast.error("Something went wrong");
    } finally {
      setLoading(false);
    }
  };

  return {
    form,
    errors,
    setField,
    onSubmit,
    isValidForm,
    loading,
    margin_balance_calculated,
    trade_currency,
    max_balance,
    liq_price,
  };
};
