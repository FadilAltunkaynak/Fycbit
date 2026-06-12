import request from "lib/request";
import futureRequest from "lib/futureTradeRequest";

export const getMarketCardDatasApi = async (currency_type: any) => {
  const { data } = await request.get(
    `/market-overview-coin-statistic-list?currency_type=${currency_type}`
  );
  return data;
};

export const getMarketsTradeSectionDataApi = async (
  currency_type: any,
  selectType: any,
  search: any,
  page: any
) => {
  const endpoint = `/market-overview-top-coin-list?currency_type=${currency_type}&limit=15&type=${selectType}&search=${search}&page=${page}`;
  const client = Number(selectType) === 3 ? futureRequest : request;
  const { data } = await client.get(endpoint);
  return data;
};

export const getCurrenyApi = async () => {
  const { data } = await request.get(`currency-list`);
  return data;
};
