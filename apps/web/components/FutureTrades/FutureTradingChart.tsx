import cn from "classnames";
import { useTradingViewChartFutures } from "hooks/trading/useTradingViewChartFutures";
import { CSSProperties } from "react";

const TradingViewChartFutures: React.FC<{
  className?: string;
  style?: CSSProperties;
}> = ({ className, style }) => {
  const { container_id, containerRef } = useTradingViewChartFutures();

  return (
    <>
      <div
        ref={containerRef as React.RefObject<HTMLDivElement>}
        id={container_id}
        className={cn(className)}
        style={style}
      />
    </>
  );
};

export default TradingViewChartFutures;
