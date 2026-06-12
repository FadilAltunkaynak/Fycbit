import React, { useMemo, useState } from "react";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
import PairItem from "./FuturePairItem";
import useTranslation from "next-translate/useTranslation";

export default function FuturePairTable() {
  const { t } = useTranslation("common");
  const { futurePairs } = useSelector((state: RootState) => state.futureTrade);
  const [query, setQuery] = useState("");

  const filteredPairs = useMemo(() => {
    const normalizedQuery = query.trim().toUpperCase().replace(/[^A-Z0-9]/g, "");
    if (!normalizedQuery) return futurePairs;

    return futurePairs?.filter((item) => {
      const normalizedCode = item.code
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, "");
      return normalizedCode.includes(normalizedQuery);
    });
  }, [futurePairs, query]);

  return (
    <div>
      <div className=" tradex-mb-2">
        <input
          type="text"
          className=" tradex-bg-background-main tradex-h-10 tradex-rounded-lg tradex-border tradex-border-border tradex-pl-4 tradex-pr-12 tradex-w-full placeholder:tradex-text-body tradex-text-title tradex-text-sm"
          placeholder="Search..."
          value={query}
          onChange={(event) => setQuery(event.target.value)}
        />
      </div>
      <p className=" tradex-text-primary tradex-text-sm tradex-font-medium tradex-leading-7 tradex-cursor-pointer">
        USDT-M
      </p>
      <div className=" tradex-flex tradex-flex-col tradex-mt-2">
        <div className=" tradex-grid tradex-grid-cols-4 tradex-mb-3 tradex-pr-1">
          <p className=" tradex-text-body tradex-font-medium tradex-text-xs tradex-leading-5">
            {t("Symbols")}
          </p>
          <p className=" tradex-text-body tradex-font-medium tradex-text-xs tradex-leading-5 tradex-text-end">
            {t("Last Price")}
          </p>
          <p className=" tradex-text-body tradex-font-medium tradex-text-xs tradex-leading-5 tradex-text-end">
            24h {t("Change")}
          </p>
          <p className=" tradex-text-body tradex-font-medium tradex-text-xs tradex-leading-5 tradex-text-end">
            {t("Volume")}
          </p>
        </div>
        <div className=" tradex-max-h-[200px] tradex-min-h-[200px] tradex-overflow-y-auto tradex-pr-1">
          {filteredPairs?.length ? (
            filteredPairs.map((item, index) => (
              <PairItem item={item} key={index} />
            ))
          ) : (
            <p className=" tradex-text-body tradex-text-xs tradex-leading-5 tradex-text-center tradex-py-6">
              {t("No results")}
            </p>
          )}
        </div>
      </div>
    </div>
  );
}
