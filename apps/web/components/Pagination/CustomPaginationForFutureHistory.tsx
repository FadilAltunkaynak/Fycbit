import useTranslation from "next-translate/useTranslation";
import React from "react";
import { BsThreeDots } from "react-icons/bs";
import { MdChevronLeft, MdChevronRight } from "react-icons/md";
import ReactPaginate from "react-paginate";

export default function CustomPaginationForFutureHistory({
  handlePageClick,
  current_page,
  total,
  per_page = 15,
  page_range_displayed = 2,
  margin_range_displayed = 1,
  className = "",
}: any) {
  const { t } = useTranslation("common");
  if (!total) return <></>;
  return (
    <div
      className={` tradex-flex tradex-flex-col sm:tradex-flex-row tradex-items-center tradex-justify-end tradex-gap-6 tradex-py-2.5 ${className}`}
    >
      <div>
        <ReactPaginate
          nextLabel={<MdChevronRight />}
          onPageChange={handlePageClick}
          pageRangeDisplayed={page_range_displayed}
          breakLabel={<BsThreeDots />}
          marginPagesDisplayed={margin_range_displayed}
          pageCount={Math.ceil(total / per_page)}
          previousLabel={<MdChevronLeft />}
          renderOnZeroPageCount={null}
          className={` tradex-flex tradex-items-center tradex-justify-center tradex-gap-1`}
          pageLinkClassName={`!tradex-w-7 !tradex-min-h-7 sm:!tradex-min-w-7 sm:!tradex-min-h-7 tradex-inline-block tradex-text-sm sm:tradex-text-sm tradex-text-primary tradex-flex tradex-items-center tradex-justify-center !tradex-rounded-full tradex-border tradex-border-primary`}
          activeLinkClassName={`!tradex-bg-primary !tradex-text-white !tradex-font-bold`}
          previousLinkClassName={`!tradex-w-7 !tradex-min-h-7 sm:!tradex-min-w-7 sm:!tradex-min-h-7 tradex-inline-block tradex-text-lg sm:tradex-text-xl tradex-text-primary tradex-flex tradex-items-center tradex-justify-center !tradex-rounded-full`}
          nextLinkClassName={`!tradex-w-7 !tradex-min-h-7 sm:!tradex-min-w-7 sm:!tradex-min-h-7 tradex-inline-block tradex-text-lg sm:tradex-text-xl tradex-text-primary tradex-flex tradex-items-center tradex-justify-center !tradex-rounded-full`}
          breakLinkClassName={`!tradex-w-7 !tradex-min-h-7 sm:!tradex-min-w-7 sm:!tradex-min-h-7 tradex-inline-block tradex-text-lg sm:tradex-text-xl tradex-text-primary tradex-flex tradex-items-center tradex-justify-center !tradex-rounded-full tradex-border tradex-border-primary`}
          forcePage={current_page - 1}
        />
      </div>
    </div>
  );
}
