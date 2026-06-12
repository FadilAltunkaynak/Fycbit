import { Modal } from "components/ui/modal";
import {
  FUTURE_MARGIN_MODE,
  FUTURE_ORDER_TYPE,
  TPSL_TYPE,
} from "helpers/core-constants";
import { cn, noExponents } from "helpers/functions";
import useTranslation from "next-translate/useTranslation";
import Tooltip from "rc-tooltip";
import React, { useState } from "react";
import { FaEdit } from "react-icons/fa";
import { IoMdClose } from "react-icons/io";
import { useSelector } from "react-redux";
import {
  useGetFutureUserPositionLists,
  usePositionsTPSLForm,
} from "state/actions/future";
import { FuturePosition } from "state/reducer/futureReducer";
import { RootState } from "state/store";
import PositionsTPSLForm from "./PositionsTPSLForm";
import FuturesPositionCloseForm from "./FuturesPositionCloseForm";
import { useRouter } from "next/router";
import PositionsAdjustMarginForm from "./PositionsAdjustMarginForm";
type Column = {
  label: string;
  tooltip?: string;
};
export default function FuturePositions() {
  const { t } = useTranslation("common");

  const { futurePositions } = useSelector(
    (state: RootState) => state.futureTrade,
  );

  const { loading } = useGetFutureUserPositionLists();

  const POSITION_COLUMNS: Column[] = [
    { label: t("Symbol") },
    {
      label: t("Size"),
    },
    {
      label: t("Entry Price"),
    },
    {
      label: t("Mark Price"),
    },
    {
      label: t("Liq. Price"),
    },
    {
      label: t("Margin Ratio"),
    },
    {
      label: t("Margin"),
    },
    {
      label: t("PNL (ROI %)"),
    },
    {
      label: t("Close Positions"),
    },
    {
      label: t("TP/SL for position"),
    },
  ];
  return (
    <div className="tradex-min-w-max">
      <table className=" tradex-w-full">
        <thead>
          <tr className=" tradex-border-b tradex-border-border">
            {POSITION_COLUMNS.map((col) => (
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
          {futurePositions?.map((item, index) => (
            <PositionIten key={index} item={item} />
          ))}
        </tbody>
      </table>
    </div>
  );
}

const PositionIten = ({ item }: { item: FuturePosition }) => {
  const { t } = useTranslation("common");

  const router = useRouter();

  const onClickCode = () => {
    router.push(`/futures/exchange/${item.code}`);
  };

  const [showTpSl, setShowTpSl] = useState(false);

  const closeTpSl = () => setShowTpSl(false);

  const [showAdjustMargin, setShowAdjustMargin] = useState(false);

  const closeAdjustMargin = () => setShowAdjustMargin(false);

  return (
    <>
      <tr
        className={cn(
          "tradex-border-l-4  ",
          Number(item.order_type) === FUTURE_ORDER_TYPE.BUY
            ? "tradex-border-future-trade-green"
            : "tradex-border-future-trade-red",
        )}
      >
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <div
            onClick={onClickCode}
            className=" tradex-flex tradex-gap-2 tradex-items-center tradex-px-1.5"
          >
            <span className=" tradex-text-xs tradex-text-title tradex-font-semibold">
              {item.code}
            </span>{" "}
            <span className=" tradex-flex tradex-items-center tradex-px-1 tradex-rounded tradex-bg-primary tradex-text-white tradex-text-[10px] tradex-leading-4">
              {item.leverage}x
            </span>
          </div>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p
            className={cn(
              " tradex-text-xs ",
              Number(item.amount) < 0
                ? "tradex-text-future-trade-red"
                : "tradex-text-future-trade-green",
            )}
          >
            {item.amount} {item.base_coin_code}
          </p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-title">{item.price}</p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-title">{item.mark_price}</p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-primary">
            {item.liquidation_price}
          </p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <p className=" tradex-text-xs tradex-text-title">{item.ratio}%</p>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <div className=" tradex-flex tradex-gap-1.5 tradex-items-center">
            <div>
              <p className=" tradex-text-xs tradex-text-title">
                {item.margin} {item.trade_coin_code}
              </p>
              <p className=" tradex-text-xs tradex-text-title">
                {item.margin_mode === FUTURE_MARGIN_MODE.ISOLATED
                  ? "(Isolated)"
                  : "(Cross)"}
              </p>
            </div>
            {item.margin_mode === FUTURE_MARGIN_MODE.ISOLATED && (
              <span
                className=" tradex-cursor-pointer"
                onClick={() => setShowAdjustMargin(true)}
              >
                <FaEdit className=" tradex-text-primary" size={14} />
              </span>
            )}
          </div>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <div>
            <p
              className={cn(
                " tradex-text-xs ",
                Number(item.pnl) < 0
                  ? "tradex-text-future-trade-red"
                  : "tradex-text-future-trade-green",
              )}
            >
              {item.pnl} {item.trade_coin_code}
            </p>
            <p
              className={cn(
                " tradex-text-xs ",
                Number(item.pnl) < 0
                  ? "tradex-text-future-trade-red"
                  : "tradex-text-future-trade-green",
              )}
            >
              ({item.roi}%)
            </p>
          </div>
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <FuturesPositionCloseForm item={item} />
        </td>
        <td className="tradex-pr-4 tradex-whitespace-nowrap tradex-py-1.5">
          <div className=" tradex-flex tradex-gap-2 tradex-items-center">
            <span className=" tradex-text-xs tradex-text-title">
              {item.tp_price} / {item.sl_price}
            </span>
            <span
              className=" tradex-cursor-pointer"
              onClick={() => setShowTpSl(true)}
            >
              <FaEdit className=" tradex-text-primary" size={14} />
            </span>
          </div>
        </td>
      </tr>

      <Modal
        open={showTpSl}
        onOpenChange={setShowTpSl}
        className=" tradex-max-w-[550px] tradex-p-6 !tradex-rounded-2xl"
      >
        <PositionsTPSLForm item={item} close={closeTpSl} />
      </Modal>

      <Modal
        open={showAdjustMargin}
        onOpenChange={setShowAdjustMargin}
        className=" tradex-max-w-[450px] tradex-p-6 !tradex-rounded-2xl"
      >
        <PositionsAdjustMarginForm item={item} close={closeAdjustMargin} />
      </Modal>
    </>
  );
};
