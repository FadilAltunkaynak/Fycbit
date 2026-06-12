import { cn, dateTimeDisplayerForLocal, getDateRange } from "helpers/functions";
import useTranslation from "next-translate/useTranslation";
import React from "react";
import { useSelector } from "react-redux";
import {
  useGetFuturePositionHistoryLists,
  useProtectedFuturePairs,
} from "state/actions/future";

//@ts-ignore
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";

import { RootState } from "state/store";
import { FUTURE_POSITION_STATUS } from "helpers/core-constants";
import CustomPaginationForFutureHistory from "components/Pagination/CustomPaginationForFutureHistory";

type Column = {
  label: string;
};

type StatusUIConfig = {
  label: string;
  bg: string;
  text: string;
};

export default function FuturePositionHistory() {
  const { t } = useTranslation("common");

  const { futurePairs, futurePositionHistory } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  const {
    loading,
    setFilter,
    setDates,
    dates,
    setPickerDates,
    pickerDates,
    paginations,
    setPage,
  } = useGetFuturePositionHistoryLists();

  const { loading: isPairGeeting } = useProtectedFuturePairs();

  const FUTURE_POSITIONS_COLUMNS: Column[] = [
    { label: t("Time") },
    { label: t("Symbol") },
    { label: t("Size") },
    { label: t("Entry Price") },
    { label: t("Status") },
  ];

  const FUTURE_POSITION_STATUS_CONFIG: Record<number, StatusUIConfig> = {
    [FUTURE_POSITION_STATUS.OPEN]: {
      label: t("Open"),
      bg: "tradex-bg-blue-500/15",
      text: "tradex-text-blue-600",
    },

    [FUTURE_POSITION_STATUS.UPDATED]: {
      label: t("Updated"),
      bg: "tradex-bg-yellow-500/15",
      text: "tradex-text-yellow-600",
    },

    [FUTURE_POSITION_STATUS.CLOSED]: {
      label: t("Closed"),
      bg: "tradex-bg-gray-500/15",
      text: "tradex-text-gray-600",
    },

    [FUTURE_POSITION_STATUS.LIQUIDATED]: {
      label: t("Liquidated"),
      bg: "tradex-bg-red-500/15",
      text: "tradex-text-red-600",
    },
  };

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
      </div>
      <div className="tradex-min-w-max">
        <table className=" tradex-w-full">
          <thead>
            <tr className=" tradex-border-b tradex-border-border">
              {FUTURE_POSITIONS_COLUMNS.map((col) => (
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
            {futurePositionHistory?.map((item, index) => (
              <tr key={index}>
                <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
                  <p className=" tradex-text-xs tradex-text-title">
                    {dateTimeDisplayerForLocal(item.created_at)}
                  </p>
                </td>
                <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
                  <p className=" tradex-text-xs tradex-text-title tradex-font-semibold">
                    {item.symbol}
                  </p>
                </td>

                <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
                  <p className=" tradex-text-xs tradex-text-title">
                    {item.amount}
                  </p>
                </td>

                <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
                  <p className=" tradex-text-xs tradex-text-title">
                    {item.price}
                  </p>
                </td>
                <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
                  <span
                    className={cn(
                      " tradex-flex tradex-w-fit tradex-items-center tradex-px-1 tradex-rounded  tradex-text-[10px] tradex-leading-4",
                      FUTURE_POSITION_STATUS_CONFIG[item.status].bg,
                      FUTURE_POSITION_STATUS_CONFIG[item.status].text,
                    )}
                  >
                    {FUTURE_POSITION_STATUS_CONFIG[item.status].label}
                  </span>
                </td>
              </tr>
            ))}
            {Number(futurePositionHistory?.length) === 0 && (
              <tr>
                <td
                  colSpan={FUTURE_POSITIONS_COLUMNS.length}
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
