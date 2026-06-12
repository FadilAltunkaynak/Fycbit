import {
  Dropdown,
  DropdownContent,
  DropdownTrigger,
} from "components/ui/dropdown";
import React from "react";
import { FaChevronDown } from "react-icons/fa";
import { useSelector } from "react-redux";
import { RootState } from "state/store";
import FuturePairs from "./FuturePairs";

export default function FuturePairSection() {
  const { currentPair } = useSelector((state: RootState) => state.futureTrade);

  return (
    <div>
      <Dropdown>
        <DropdownTrigger>
          <div className=" tradex-flex tradex-gap-2 tradex-items-center">
            <p className=" tradex-text-title tradex-text-2xl tradex-font-semibold">
              {currentPair}
            </p>
            <span>
              <FaChevronDown />
            </span>
          </div>
        </DropdownTrigger>

        <DropdownContent className=" tradex-min-w-[400px] tradex-rounded-xl">
          <FuturePairs />
        </DropdownContent>
      </Dropdown>
    </div>
  );
}
