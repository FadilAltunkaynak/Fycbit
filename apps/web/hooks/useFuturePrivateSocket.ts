import { initEcho } from "helpers/echo";
import { useEffect } from "react";
import { useSelector } from "react-redux";
import { RootState } from "state/store";

interface Props {
  onAssetSummary?: (data: any) => void;
  onMarginSummary?: (data: any) => void;
  onPosition?: (data: any) => void;
  onFutureWallet?: (data: any) => void;
  onFutureOrderSummary?: (data: any) => void;
}

export const useFuturePrivateSocket = ({
  onAssetSummary,
  onMarginSummary,
  onPosition,
  onFutureWallet,
  onFutureOrderSummary,
}: Props) => {
  const { user } = useSelector((state: RootState) => state.user);
  const { pairDetails } = useSelector((state: RootState) => state.futureTrade);

  const userId = user?.id;
  const coinPairUid = pairDetails?.uid;

  useEffect(() => {
    if (!userId || !coinPairUid) return;

    const echo = initEcho();

    if (!echo) return;

    const channel = echo.private(`future_trade_${userId}`);

    if (onAssetSummary) {
      channel.listen(`.future.asset.summery.${coinPairUid}`, (data: any) => {
        onAssetSummary(data);
      });
    }

    if (onMarginSummary) {
      channel.listen(`.future.margin.summery.${coinPairUid}`, (data: any) => {
        onMarginSummary(data);
      });
    }

    if (onPosition) {
      channel.listen(`.future.position`, (data: any) => {
        onPosition(data);
      });
    }

    if (onFutureWallet) {
      channel.listen(`.future.wallet`, (data: any) => {
        onFutureWallet(data);
      });
    }

    if (onFutureOrderSummary) {
      channel.listen(`.future.order.summary.${coinPairUid}`, (data: any) => {
        onFutureOrderSummary(data);
      });
    }

    return () => {
      echo.leave(`future_trade_${userId}`);
    };
  }, [userId, coinPairUid]);
};
