import { FUTURE_ORDER_METHOD } from "helpers/core-constants";
import { cn } from "helpers/functions";
import useTranslation from "next-translate/useTranslation";
import React from "react";
import { useFuturesPositionCloseForm } from "state/actions/future";
import { FuturePosition } from "state/reducer/futureReducer";

export default function FuturesPositionCloseForm({
  item,
}: {
  item: FuturePosition;
}) {
  const { t } = useTranslation("common");

  const { errors, form, onSubmit, setField, loading } =
    useFuturesPositionCloseForm(item);

  return (
    <div className=" tradex-flex tradex-gap-2 tradex-items-center">
      <button
        disabled={loading}
        onClick={() => onSubmit(FUTURE_ORDER_METHOD.MARKET)}
        className=" tradex-text-xs tradex-text-primary tradex-font-semibold"
      >
        {loading ? "..." : t("Market")}
      </button>
      <button
        disabled={loading}
        onClick={() => onSubmit(FUTURE_ORDER_METHOD.LIMIT)}
        className=" tradex-text-xs tradex-text-primary tradex-font-semibold"
      >
        {loading ? "..." : t("Limit")}
      </button>
      <input
        type="number"
        step={"any"}
        placeholder="Price"
        min={0}
        value={form.price || ""}
        onChange={(e) => setField("price", e.target.value)}
        className={cn(
          " tradex-w-[60px] tradex-max-w-[60px] tradex-px-1 tradex-h-[22px] tradex-bg-background-main tradex-rounded tradex-text-xs tradex-text-body",
          errors.price && "!tradex-border !tradex-border-red-700",
        )}
      />
      <input
        type="number"
        step={"any"}
        placeholder="QTY:Full"
        min={0}
        value={form.amount || ""}
        onChange={(e) => setField("amount", e.target.value)}
        className={cn(
          " tradex-w-[60px] tradex-max-w-[60px] tradex-px-1 tradex-h-[22px] tradex-bg-background-main tradex-rounded tradex-text-xs tradex-text-body",
          errors.amount && "!tradex-border !tradex-border-red-700",
        )}
      />
    </div>
  );
}
