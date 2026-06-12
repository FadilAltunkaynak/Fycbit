import { FUTURE_ORDER_METHOD } from "helpers/core-constants";

type OrderTypeButtonProps = {
  label: string;
  value: FUTURE_ORDER_METHOD;
  active: FUTURE_ORDER_METHOD;
  onChange: (value: FUTURE_ORDER_METHOD) => void;
};

export const OrderTypeButton = ({
  label,
  value,
  active,
  onChange,
}: OrderTypeButtonProps) => {
  const isActive = active === value;

  return (
    <button
      onClick={() => onChange(value)}
      className={`
        tradex-px-1
        tradex-text-sm
        tradex-leading-7
        tradex-capitalize
        ${isActive ? "tradex-text-primary" : "tradex-text-body"}
      `}
    >
      {label}
    </button>
  );
};
