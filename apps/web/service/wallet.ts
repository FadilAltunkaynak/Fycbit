import request from "lib/request";
import evmRequest from "lib/requestForEvm";

export const WalletListApi = async (url: string) => {
  const { data } = await request.get(url);
  return data;
};
export const GetCoinListApi = async () => {
  const { data } = await request.get("get-coin-list");
  return data;
};
export const WalletDepositApi = async (coin_type: any) => {
  const { data } = await request.get(`/wallet-deposit-${coin_type}`);
  return data;
};
export const WalletWithdrawApi = async (coin_type: string) => {
  const { data } = await request.get(`/wallet-withdrawal-${coin_type}`);
  return data;
};
export const MyWalletProcessSidebar = async (id: string) => {
  const { data } = await request.get(
    `/wallet-history-app?type=${id}&per_page=20&page=1`
  );
  return data;
};

export const getNetworkForDepositAndWithdrawApi = async (
  coin_type: any,
  wallet_type: any
) => {
  const { data } = await request.get(
    `/wallet-networks/${coin_type}?type=${wallet_type}`
  );
  return data;
};

export const WalletWithdrawProcessApi = async (credential: any) => {
  const { data } = await request.post("/wallet-withdrawal-process", credential);
  return data;
};
export const WalletWithdrawProcessApiForEvm = async (credential: any) => {
  const { data } = await evmRequest.post(
    "/evm/wallet-withdrawal-process",
    credential
  );
  return data;
};
export const GetWalletAddress = async (credential: any) => {
  const { data } = await request.post("/get-wallet-network-address", {
    coin_type: credential.coin_type,
    network_type: credential.network_type,
  });
  return data;
};

export const getFeeAmountApi = async (credential: any) => {
  const { data } = await request.post("/pre-withdrawal-process", credential);
  return data;
};

export const networkHandlerApi = async (walletId: any, networkId: any) => {
  const { data } = await request.get(`wallet-deposit-${walletId}-${networkId}`);
  return data;
};

export const getEvmNetworkAddressApi = async (credential: any) => {
  const { data } = await evmRequest.post("/evm/create-wallet", credential);
  return data;
};
export const getTotalWalletBalanceApi = async () => {
  const { data } = await request.get(`/wallet-total-value`);
  return data;
};

export const getCoinListForDepositAndWithdrawalApi = async (query: string) => {
  const { data } = await request.get(`get-coin-list?${query}`);
  return data;
};

export const getWalletDepositAddressApi = async (credential: any) => {
  const { data } = await request.post("/wallet-deposit-address", credential);
  return data;
};
