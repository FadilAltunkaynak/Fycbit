import CustomPaginationForFutureHistory from "components/Pagination/CustomPaginationForFutureHistory";
import {
  FUTURE_ORDER_METHOD,
  FUTURE_ORDER_STATUS,
  FUTURE_ORDER_TYPE,
} from "helpers/core-constants";
import { cn, dateTimeDisplayerForLocal, getDateRange } from "helpers/functions";
import useTranslation from "next-translate/useTranslation";
import React, { useState } from "react";
type Column = {
  label: string;
};
//@ts-ignore
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import { useSelector } from "react-redux";
import {
  useGetFutureOrderHistoryLists,
  useProtectedFuturePairs,
} from "state/actions/future";
import { RootState } from "state/store";

export default function FutureOrderHistory() {
  const { t } = useTranslation("common");

  const { futurePairs, futureOrderHistory } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  const {
    loading,
    setFilter,
    setDates,
    dates,
    setPickerDates,
    pickerDates,
    setPage,
    paginations,
  } = useGetFutureOrderHistoryLists();

  const { loading: isPairGeeting } = useProtectedFuturePairs();

  const ORDER_HISTORY_COLUMNS: Column[] = [
    { label: t("Time") },
    { label: t("Symbol") },
    { label: t("Type") },
    { label: t("Side") },
    { label: t("Price") },
    { label: t("Executed") },
    { label: t("Amount") },
    { label: t("Reduce Only") },
    { label: t("Trigger Conditions") },
    { label: t("Status") },
    { label: t("Actions") },
  ];

  const FUTURE_ORDER_METHOD_LABEL: Record<string, string> = {
    [FUTURE_ORDER_METHOD.LIMIT]: t("Limit"),
    [FUTURE_ORDER_METHOD.MARKET]: t("Market"),
    [FUTURE_ORDER_METHOD.STOP_LIMIT]: t("Stop Limit"),
  };
  const FUTURE_ORDER_STATUS_CONFIG: any = {
    [FUTURE_ORDER_STATUS.OPEN]: {
      label: t("Open"),
      bg: "tradex-bg-yellow-500/15",
      text: "tradex-text-yellow-600",
    },
    [FUTURE_ORDER_STATUS.COMPLETE]: {
      label: t("Complete"),
      bg: "tradex-bg-green-500/15",
      text: "tradex-text-green-600",
    },
    [FUTURE_ORDER_STATUS.CANCEL]: {
      label: t("Cancel"),
      bg: "tradex-bg-red-500/15",
      text: "tradex-text-red-600",
    },
  } as const;

  return (
    <div>
      <div className=" tradex-flex tradex-gap-2 tradex-items-center tradex-flex-wrap tradex-mb-2">
        <span
          onClick={() => {
            setPickerDates(undefined);
            setDates(getDateRange(1));
          }}
          className={cn(
            " tradex-text-xs  tradex-font-medium tradex-px-1 rounded tradex-leading-5 tradex-cursor-pointer",
            dates.days === 1
              ? "tradex-text-white tradex-bg-primary"
              : "tradex-text-body",
          )}
        >
          1 {t("Day")}
        </span>
        <span
          onClick={() => {
            setPickerDates(undefined);
            setDates(getDateRange(7));
          }}
          className={cn(
            " tradex-text-xs  tradex-font-medium tradex-px-1 rounded tradex-leading-5 tradex-cursor-pointer",
            dates.days === 7
              ? "tradex-text-white tradex-bg-primary"
              : "tradex-text-body",
          )}
        >
          1 {t("Week")}
        </span>
        <span
          onClick={() => {
            setPickerDates(undefined);
            setDates(getDateRange(30));
          }}
          className={cn(
            " tradex-text-xs  tradex-font-medium tradex-px-1 rounded tradex-leading-5 tradex-cursor-pointer",
            dates.days === 30
              ? "tradex-text-white tradex-bg-primary"
              : "tradex-text-body",
          )}
        >
          1 {t("Month")}
        </span>
        <span
          onClick={() => {
            setPickerDates(undefined);
            setDates(getDateRange(90));
          }}
          className={cn(
            " tradex-text-xs  tradex-font-medium tradex-px-1 rounded tradex-leading-5 tradex-cursor-pointer",
            dates.days === 90
              ? "tradex-text-white tradex-bg-primary"
              : "tradex-text-body",
          )}
        >
          3 {t("Months")}
        </span>
        <div className=" custom-react-date-picker">
          <DatePicker
            selected={pickerDates?.end}
            onChange={(d: any) => {
              const [start, end] = d;
              setPickerDates({
                start,
                end,
              });

              if (start && end)
                setDates((v) => ({ start: start || dates.start, end }));
            }}
            startDate={pickerDates?.start}
            endDate={pickerDates?.end}
            maxDate={new Date()}
            minDate={new Date(new Date().setDate(new Date().getDate() - 365))}
            // monthsShown={2}
            dateFormatCalendar={"MMM yyyy"}
            selectsRange
            placeholderText={t("Select")}
            className={cn(
              " !tradex-max-w-[210px] tradex-px-2.5 tradex-rounded tradex-border  !tradex-text-xs tradex-border-border !tradex-bg-background-main !tradex-text-body",
            )}
            calendarClassName={cn(
              "!tradex-bg-background-main tradex-rounded tradex-border !tradex-border-border !tradex-text-body",
            )}
            popperClassName={cn("tradex-z-10")}
          />
        </div>
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
            <option value={""}>All</option>
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
              {ORDER_HISTORY_COLUMNS.map((col) => (
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
            {futureOrderHistory?.map((item, index) => (
              <tr key={index}>
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
                    {item.executed} {item.base_coin_code}
                  </p>
                </td>
                <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
                  <p className=" tradex-text-xs tradex-text-title">
                    {item.amount} {item.base_coin_code}
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
                  <span
                    className={cn(
                      " tradex-flex tradex-w-fit tradex-items-center tradex-px-1 tradex-rounded  tradex-text-[10px] tradex-leading-4",
                      FUTURE_ORDER_STATUS_CONFIG[item.status].bg,
                      FUTURE_ORDER_STATUS_CONFIG[item.status].text,
                    )}
                  >
                    {FUTURE_ORDER_STATUS_CONFIG[item.status].label}
                  </span>
                </td>
                <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
                  <p className=" tradex-text-xs tradex-text-title">N/A</p>
                </td>
              </tr>
            ))}
            {Number(futureOrderHistory?.length) === 0 && (
              <tr>
                <td
                  colSpan={ORDER_HISTORY_COLUMNS.length}
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
