// TradeSideButton.tsx
import clsx from "clsx";
export enum ORDER_SIDE {
  BUYSELL = "BUYSELL",
  BUY = "BUY",
  SELL = "SELL",
}
type Props = {
  side: typeof ORDER_SIDE[keyof typeof ORDER_SIDE];
  activeSide: typeof ORDER_SIDE[keyof typeof ORDER_SIDE];
  onChange: (side: typeof ORDER_SIDE[keyof typeof ORDER_SIDE]) => void;
};

export const TradeSideButton = ({ side, activeSide, onChange }: Props) => {
  const isActive = activeSide === side;

  return (
    <button
      className={clsx(
        "tradex-flex tradex-gap-[3px] tradex-h-[18px] tradex-items-center",
        !isActive && "tradex-opacity-70",
      )}
      onClick={() => onChange(side)}
    >
      <div className="tradex-w-[5px] tradex-flex tradex-flex-col tradex-gap-[2px] tradex-h-full">
        {side === ORDER_SIDE.BUYSELL && (
          <>
            <div className="tradex-w-full tradex-rounded-xl tradex-bg-future-trade-red tradex-h-1/2" />
            <div className="tradex-w-full tradex-rounded-xl tradex-bg-future-trade-green tradex-h-1/2" />
          </>
        )}

        {side === ORDER_SIDE.BUY && (
          <div className="tradex-w-full tradex-rounded-xl tradex-bg-future-trade-green tradex-h-full" />
        )}

        {side === ORDER_SIDE.SELL && (
          <div className="tradex-w-full tradex-rounded-xl tradex-bg-future-trade-red tradex-h-full" />
        )}
      </div>

      <OrderBookThreeLine />
    </button>
  );
};

const OrderBookThreeLine = () => {
  return (
    <div className=" tradex-w-3 tradex-flex tradex-flex-col tradex-gap-[2px]">
      <div className=" tradex-w-full tradex-rounded-xl tradex-bg-body tradex-h-1"></div>
      <div className=" tradex-w-full tradex-rounded-xl tradex-bg-body tradex-h-1"></div>
      <div className=" tradex-w-full tradex-rounded-xl tradex-bg-body tradex-h-1"></div>
    </div>
  );
};
