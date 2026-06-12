import { initEcho } from "helpers/echo";
import { useEffect } from "react";

interface Props {
  coinPairUid: string;
  onTrade?: (data: any) => void;
  onOrderbook?: (data: any) => void;
  on24hChange?: (data: any) => void;
}

export const useFutureSocket = ({
  coinPairUid,
  onTrade,
  onOrderbook,
  on24hChange,
}: Props) => {
  useEffect(() => {
    if (!coinPairUid) return;

    const echo = initEcho();
    if (!echo) return;

    const channel = echo.channel("future_trade");

    if (onTrade) {
      channel.listen(`.future.trade.${coinPairUid}`, (data: any) => {
        onTrade(data);
      });
    }

    if (onOrderbook) {
      channel.listen(`.future.orderbook.${coinPairUid}`, (data: any) => {
        onOrderbook(data);
      });
    }

    if (on24hChange) {
      channel.listen(`.future.trade.24h.change.${coinPairUid}`, (data: any) => {
        on24hChange(data);
      });
    }

    return () => {
      echo.leave("future_trade");
    };
  }, [coinPairUid]);
};
