import React from "react";
import { useFuturePairs } from "state/actions/future";
import FuturePairTable from "./FuturePairTable";

export default function FuturePairs() {
  const { loading, error } = useFuturePairs();

  return (
    <div className=" tradex-p-3">
      <FuturePairTable />
    </div>
  );
}
