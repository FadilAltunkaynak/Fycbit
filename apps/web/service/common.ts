import request from "lib/request";

export const getModulesStatus = async () => {
    const { data } = await request.get(`/modules-status`);
    return data;
};

export const getTransferCurrencies = async (
    from_fund_type: number,
    to_fund_type: number,
) => {
    const { data } = await request.get(
        `/get-coin-list?transfer=1&from_fund_type=${from_fund_type}&to_fund_type=${to_fund_type}`,
    );
    return data;
};

export const walletBalanceTransferApi = async (
    fund_from: number,
    fund_to: number,
    coin_type: string,
    amount: number,
) => {
    const { data } = await request.post("/wallet-fund-transfer", {
        fund_from: fund_from,
        fund_to: fund_to,
        coin_type: coin_type,
        amount: amount,
    });
    return data;
};

export const getFundTransferWalletBalance = async (
    coin_type: string,
    to_fund_type: number,
    from_fund_type: number,
) => {
    const { data } = await request.get(
        `/get-fund-transfer-wallet-balance?coin_type=${coin_type}&to_fund_type=${to_fund_type}&from_fund_type=${from_fund_type}`,
    );
    return data;
};
