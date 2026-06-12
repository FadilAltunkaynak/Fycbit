import { ReactNode } from "react";

type TickerItemRootProps = {
  children: ReactNode;
};

function TickerItemRoot({ children }: TickerItemRootProps) {
  return <div className="tradex-space-y-1">{children}</div>;
}

function Title({
  children,
  className = "",
}: {
  children: ReactNode;
  className?: string;
}) {
  return (
    <div
      className={`tradex-text-xs tradex-leading-5 tradex-text-body tradex-font-medium ${className}`}
    >
      {children}
    </div>
  );
}

type ValueProps = {
  children: ReactNode;
  className?: string;
  iconClassName?: string;
  icon?: ReactNode;
};

function Value({
  children,
  className = "",
  icon,
  iconClassName = "",
}: ValueProps) {
  return (
    <div className="tradex-flex tradex-gap-0.5 tradex-items-center">
      <div
        className={`tradex-text-lg tradex-leading-5 tradex-font-semibold ${className}`}
      >
        {children}
      </div>
      {icon && <span className={iconClassName}>{icon}</span>}
    </div>
  );
}

function SubValue({
  children,
  className,
}: {
  children: ReactNode;
  className?: string;
}) {
  return (
    <div
      className={`tradex-text-xs tradex-text-body tradex-font-medium ${className}`}
    >
      {children}
    </div>
  );
}

export const TickerItem = Object.assign(TickerItemRoot, {
  Title,
  Value,
  SubValue,
});
