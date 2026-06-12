import CustomPaginationForFutureHistory from "components/Pagination/CustomPaginationForFutureHistory";
import { Modal } from "components/ui/modal";
import { FUTURE_ORDER_METHOD, FUTURE_ORDER_TYPE } from "helpers/core-constants";
import { cn, dateTimeDisplayerForLocal } from "helpers/functions";
import useTranslation from "next-translate/useTranslation";
import React, { useState } from "react";
import { useSelector } from "react-redux";
import {
  useCancelOpenOrders,
  useGetFutureOpenOrderLists,
  useProtectedFuturePairs,
} from "state/actions/future";
import { FutureOpenOrder } from "state/reducer/futureReducer";
import { RootState } from "state/store";
type Column = {
  label: string;
};

export default function FutureOpenOrders() {
  const { t } = useTranslation("common");
  const { futureOpenOrders, futurePairs } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  const { loading, setFilter, paginations, setPage } =
    useGetFutureOpenOrderLists();

  const { loading: isPairGeeting } = useProtectedFuturePairs();

  const OPEN_ORDERS_COLUMNS: Column[] = [
    { label: t("Time") },
    { label: t("Symbol") },
    { label: t("Type") },
    { label: t("Side") },
    { label: t("Price") },
    { label: t("Amount") },
    { label: t("Filled") },
    { label: t("Reduce Only") },
    { label: t("Trigger Conditions") },
    { label: t("TP/SL") },
    { label: t("Actions") },
  ];

  return (
    <div>
      <div className=" tradex-flex tradex-gap-2 tradex-items-center tradex-flex-wrap tradex-mb-2">
        <div className=" tradex-flex tradex-gap-1 tradex-items-center">
          <p className=" tradex-text-xs tradex-font-medium tradex-text-body">
            {t("Symbol")}
          </p>
          <select
            className="tradex-w-[100px]  md:tradex-w-[150px] tradex-rounded tradex-text-xs tradex-h-[34px] !tradex-text-body !tradex-bg-background-main tradex-px-2 !tradex-border !tradex-border-border tradex-min-w-[100px] md:tradex-min-w-[150px]"
            id="currency-one"
            onChange={(e) => setFilter("symbol", e.target.value)}
          >
            <option value={""}>{t("All")}</option>

            {futurePairs?.map((item) => (
              <option value={item.code} key={item.code}>
                {item.code}
              </option>
            ))}
          </select>
        </div>
        <div className=" tradex-flex tradex-gap-1 tradex-items-center">
          <p className=" tradex-text-xs tradex-font-medium tradex-text-body">
            {t("Limit Type")}
          </p>
          <select
            onChange={(e) => setFilter("order_method", e.target.value)}
            className="tradex-w-[100px]  md:tradex-w-[150px] tradex-rounded tradex-text-xs tradex-h-[34px] !tradex-text-body !tradex-bg-background-main tradex-px-2 !tradex-border !tradex-border-border tradex-min-w-[100px] md:tradex-min-w-[150px]"
          >
            <option value={""}>{t("All")}</option>
            <option value={FUTURE_ORDER_METHOD.LIMIT}>{t("Limit")}</option>
            <option value={FUTURE_ORDER_METHOD.MARKET}>{t("Market")}</option>
            <option value={FUTURE_ORDER_METHOD.STOP_LIMIT}>
              {t("Stop-Limit")}
            </option>
          </select>
        </div>
        <div className=" tradex-flex tradex-gap-1 tradex-items-center">
          <p className=" tradex-text-xs tradex-font-medium tradex-text-body">
            {t("Side")}
          </p>
          <select
            onChange={(e) => setFilter("side", e.target.value)}
            className="tradex-w-[100px]  md:tradex-w-[150px] tradex-rounded tradex-text-xs tradex-h-[34px] !tradex-text-body !tradex-bg-background-main tradex-px-2 !tradex-border !tradex-border-border tradex-min-w-[100px] md:tradex-min-w-[150px]"
          >
            <option value={""}>{t("All")}</option>

            <option value={FUTURE_ORDER_TYPE.BUY}>{t("Buy")}</option>
            <option value={FUTURE_ORDER_TYPE.SELL}>{t("Sell")}</option>
          </select>
        </div>
      </div>
      <div className="tradex-min-w-max">
        <table className=" tradex-w-full">
          <thead>
            <tr className=" tradex-border-b tradex-border-border">
              {OPEN_ORDERS_COLUMNS.map((col) => (
                <th
                  key={col.label}
                  className=" tradex-pr-4 tradex-whitespace-nowrap tradex-pb-1.5"
                >
                  <p className="tradex-text-xs tradex-text-body tradex-leading-5 tradex-font-semibold">
                    {col.label}
                  </p>
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {futureOpenOrders?.map((item) => (
              <FutureOpenOrderItem key={item.uid} item={item} />
            ))}
            {Number(futureOpenOrders?.length) === 0 && (
              <tr>
                <td
                  colSpan={OPEN_ORDERS_COLUMNS.length}
                  className="tradex-text-center tradex-py-6 tradex-text-sm tradex-text-body"
                >
                  {t("No Data Found")}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
      <div className=" tradex-mt-4">
        <CustomPaginationForFutureHistory
          per_page={paginations?.per_page}
          current_page={paginations?.current_page}
          total={paginations?.total}
          handlePageClick={(event: any) => setPage(event.selected + 1)}
        />
      </div>
    </div>
  );
}

const FutureOpenOrderItem = ({ item }: { item: FutureOpenOrder }) => {
  const { t } = useTranslation("common");

  const [openCancelModal, setOpenCancelModal] = useState(false);

  const openModal = () => setOpenCancelModal(true);

  const closeModal = () => setOpenCancelModal(false);

  const { loading, onCancel } = useCancelOpenOrders(item, closeModal);

  const FUTURE_ORDER_METHOD_LABEL: Record<string, string> = {
    [FUTURE_ORDER_METHOD.LIMIT]: t("Limit"),
    [FUTURE_ORDER_METHOD.MARKET]: t("Market"),
    [FUTURE_ORDER_METHOD.STOP_LIMIT]: t("Stop Limit"),
  };

  return (
    <>
      <tr>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-title">
            {dateTimeDisplayerForLocal(item.created_at)}
          </p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-title tradex-font-semibold">
            {item.base_coin_code}
            {item.trade_coin_code}
          </p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-title">
            {FUTURE_ORDER_METHOD_LABEL[item.type]}
          </p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p
            className={cn(
              " tradex-text-xs ",
              item.side === FUTURE_ORDER_TYPE.BUY
                ? "tradex-text-future-trade-green"
                : "tradex-text-future-trade-red",
            )}
          >
            {item.side === FUTURE_ORDER_TYPE.BUY ? t("Buy") : t("Sell")}
          </p>
        </td>

        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-title">
            {item.price} {item.trade_coin_code}
          </p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-title">
            {item.amount} {item.base_coin_code}
          </p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-title">
            {item.filed} {item.base_coin_code}
          </p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-title">
            {Boolean(Number(item.reduce_only)) ? t("Yes") : t("No")}
          </p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-title">
            {item.trigger_conditions}
          </p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-title">
            {item.tp_price}/{item.sl_price}
          </p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <button
            onClick={openModal}
            className=" tradex-text-xs tradex-font-semibold tradex-text-white tradex-bg-red-600 tradex-rounded-full tradex-px-3 tradex-py-0.5"
          >
            {t("Cancel")}
          </button>
        </td>
      </tr>

      <Modal
        open={openCancelModal}
        onOpenChange={setOpenCancelModal}
        className=" tradex-max-w-[450px] tradex-p-6 !tradex-rounded-xl"
      >
        <div className=" tradex-text-center">
          <p className=" tradex-text-xl !tradex-text-title tradex-font-semibold">
            {t("Do you want to cancel this order?")}
          </p>
          <p className=" tradex-text-base tradex-text-body tradex-mt-2">
            {t(`You won't be able to revert this!`)}
          </p>
          <div className=" tradex-flex tradex-justify-center tradex-gap-4 tradex-items-center tradex-mt-6">
            <button
              onClick={() => onCancel(item.side)}
              disabled={loading}
              className={cn(
                " tradex-bg-primary tradex-text-white tradex-border-primary   tradex-h-10 tradex-w-fit tradex-rounded-[20px] tradex-border tradex-flex tradex-justify-center tradex-items-center tradex-px-12 tradex-text-sm tradex-font-semibold",
                loading && " tradex-opacity-50 tradex-cursor-not-allowed",
              )}
            >
              {loading ? t("Loading..") : t("Confirm")}
            </button>
            <button
              onClick={closeModal}
              disabled={loading}
              className={cn(
                " tradex-text-primary tradex-border-primary   tradex-h-10 tradex-w-fit tradex-rounded-[20px] tradex-border tradex-flex tradex-justify-center tradex-items-center tradex-px-12 tradex-text-sm tradex-font-semibold",
                loading && " tradex-opacity-50 tradex-cursor-not-allowed",
              )}
            >
              {loading ? t("Loading..") : t("Cancel")}
            </button>
          </div>
        </div>
      </Modal>
    </>
  );
};
