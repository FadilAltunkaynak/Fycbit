import * as React from "react";
import { cn } from "helpers/functions";
import { Range, getTrackBackground } from "react-range";

interface SliderRangeProps {
  range: number;
  onChange: (x: number) => void;
  step?: number;
  min?: number;
  max?: number;
  className?: string;
  disabled?: boolean;
  hideDots?: boolean;
  thumbSuffix?: string;
}

export const SliderRange: React.FC<SliderRangeProps> = ({
  range,
  onChange,
  step = 10,
  min = 0,
  max = 100,
  className,
  disabled,
  hideDots,
  thumbSuffix = "%",
}) => {
  return (
    <div className="tradex-px-1">
      <Range
        disabled={disabled}
        values={[range]}
        step={step}
        min={min}
        max={max}
        onChange={(values) => onChange(values[0])}
        renderTrack={({ props, children }) => (
          <div
            onMouseDown={props.onMouseDown}
            onTouchStart={props.onTouchStart}
            style={{ ...props.style }}
            className={cn(
              "tradex-relative tradex-flex tradex-w-full tradex-h-[36px] tradex-group tradex-px-2 ",
              className,
            )}
          >
            {Array.from(Array(Math.ceil(max / 25) + 1).keys()).map(
              (x, i, arr) => (
                <div
                  hidden={hideDots}
                  key={x}
                  className={cn(
                    "tradex-absolute tradex-top-[14px] tradex-w-2 tradex-h-2 tradex-rounded-full tradex-bg-primary tradex-shadow-sm",
                    i === arr.length - 1 && "-tradex-translate-x-[10px]",
                  )}
                  style={{
                    left: `${x * 25}%`,
                  }}
                />
              ),
            )}

            <div
              ref={props.ref}
              style={{
                height: "2px",
                width: "100%",
                borderRadius: "1px",
                alignSelf: "center",
                background: getTrackBackground({
                  values: [range],
                  colors: [
                    "rgba(var(--primary-color), 1)",
                    "var(--primary-background-color)",
                  ],
                  min: min,
                  max: max,
                }),
              }}
            >
              {children}
            </div>
          </div>
        )}
        renderThumb={({ props, isDragged }) => (
          <div
            {...props}
            style={{ ...props.style }}
            className={cn(
              "tradex-h-[18px] tradex-w-[18px] tradex-rounded-full tradex-bg-background-primary tradex-border-[4px] tradex-border-border tradex-shadow-[0px_8px_16px_-8px_rgba(15,15,15,0.2)] tradex-flex tradex-items-center tradex-justify-center",
            )}
          >
            <div
              style={{}}
              className={cn(
                "tradex-absolute -tradex-top-[27px] tradex-text-white tradex-font-medium tradex-text-xs tradex-leading-4  tradex-px-[6px] tradex-py-[2px] tradex-rounded-[6px] tradex-bg-primary tradex-opacity-0 tradex-transition group-hover:tradex-opacity-100",
              )}
            >
              {[range][0].toFixed(0) + thumbSuffix}
            </div>
          </div>
        )}
      />
    </div>
  );
};

SliderRange.displayName = "SliderRange";
