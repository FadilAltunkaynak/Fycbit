import useTranslation from "next-translate/useTranslation";
import { useEffect, useMemo, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  FutureOrderBookItem,
  FUTURES_ORDER_ITEM,
  setFuturesBasePrice,
} from "state/reducer/futureReducer";
import { ORDER_SIDE } from "./TradeSideButton";
import { RootState } from "state/store";
import { noExponents, nullChecker } from "helpers/functions";
import { TRADING_ORDER_LIMITS } from "helpers/core-constants";

export const useFutureOrdersMain = () => {
  const { t } = useTranslation("common");

  const dispatch = useDispatch();
  const [activeSide, setActiveSide] = useState(ORDER_SIDE.BUYSELL);

  const handleUpdateTradeBasePrice = (v: number) =>
    dispatch(setFuturesBasePrice(v));

  const { pairDetails, futureOrderBook } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  const buyOrders = futureOrderBook.buy;
  const sellOrders = futureOrderBook.sell;

  const tradeDecimal = pairDetails?.trade_decimal || 2;

  const baseDecimal = pairDetails?.base_decimal || 2;

  const isUpPrice =
    Number(pairDetails?.market_price) >= Number(pairDetails?.previous_price);

  const base_currency = nullChecker(pairDetails?.base_coin_code);
  const trade_currency = nullChecker(pairDetails?.trade_coin_code);

  const [limit, setLimit] = useState(7);

  useEffect(() => {
    setLimit(activeSide === ORDER_SIDE.BUYSELL ? 7 : 100);
  }, [activeSide]);

  const buildOrderBook = (orders: FutureOrderBookItem[]) => {
    let cumulative = 0;

    const total = orders.reduce((sum, o) => sum + Number(o.pending_amount), 0);

    return orders.map((o) => {
      const amount = Number(o.pending_amount);
      cumulative += amount;

      return {
        uid: Number(o.price),
        price: Number(o.price),
        amount,
        total: cumulative,
        percent: total ? (cumulative / total) * 100 : 0,
      };
    });
  };

  const buyOrdersBook: FUTURES_ORDER_ITEM[] = useMemo(() => {
    const buyOrdersArray = buyOrders.slice(0, limit);

    const result = buildOrderBook(buyOrdersArray);

    return result;
  }, [buyOrders, limit]);

  const sellOrdersBook: FUTURES_ORDER_ITEM[] = useMemo(() => {
    // const sellOrdersArray = [...sellOrders.slice(-(limit || 0))].reverse();
    const sellOrdersArray = sellOrders.slice(0, limit);

    const result = buildOrderBook(sellOrdersArray);

    return result.reverse();
  }, [sellOrders, limit]);

  return {
    pairDetails,
    base_currency,
    trade_currency,
    limit,
    buyOrdersBook,
    sellOrdersBook,
    setActiveSide,
    activeSide,
    tradeDecimal,
    baseDecimal,
    isUpPrice,
    handleUpdateTradeBasePrice,
  };
};
