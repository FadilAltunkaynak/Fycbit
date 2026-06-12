import Footer from "components/common/footer";
import WalletLayout from "components/wallet/WalletLayout";
import { MARKET_TYPE, STATUS_ACTIVE } from "helpers/core-constants";
import useTranslation from "next-translate/useTranslation";
import { useRouter } from "next/router";
import React, { useEffect, useMemo, useState } from "react";
import { BiArrowBack } from "react-icons/bi";
import { HiOutlineChevronUpDown } from "react-icons/hi2";
import { useSelector } from "react-redux";
import Select from "react-select";
import { toast } from "react-toastify";
import {
    getTransferCurrencies,
    walletBalanceTransferApi,
    getFundTransferWalletBalance,
} from "service/common";
import { RootState } from "state/store";

export default function Transfer() {
    const { t } = useTranslation("common");
    const router = useRouter();

    const { settings } = useSelector((state: RootState) => state.common);

    const isFutureEnabled =
        Number(settings?.enable_future_trade) === STATUS_ACTIVE;

    const [currencies, setCurrencies] = useState<any[]>([]);
    const [loadingCurrencies, setLoadingCurrencies] = useState(false);
    const [processing, setProcessing] = useState(false);

    const isP2PEnabled = Number(settings?.p2p_module) === STATUS_ACTIVE;

    const isSpotEnabled = Number(settings?.enable_future_trade) === STATUS_ACTIVE;

    const [transferForm, setTransferForm] = useState({
        fund_from: undefined,
        fund_to: undefined,
        coin_type: undefined,
        amount: undefined,
    });
    const [fundBalances, setFundBalances] = useState({
        from_balance: 0,
        to_balance: 0,
    });

    const options = useMemo(
        () => [
            ...(isSpotEnabled ? [{ value: MARKET_TYPE.SPOT, label: t("Spot") }] : []),
            ...(isFutureEnabled
                ? [{ value: MARKET_TYPE.FUTURE, label: t("Future") }]
                : []),
            ...(isP2PEnabled
                ? [{ value: MARKET_TYPE.P2P, label: t("Funding") }]
                : []),
        ],
        [isFutureEnabled, isP2PEnabled, isSpotEnabled],
    );

    const fromOptions = useMemo(() => {
        return options.filter((opt) => opt.value !== transferForm.fund_to);
    }, [options, transferForm.fund_to]);

    const toOptions = useMemo(() => {
        return options.filter((opt) => opt.value !== transferForm.fund_from);
    }, [options, transferForm.fund_from]);

    const setField = (key: string, value: any) => {
        setTransferForm((p) => ({ ...p, [key]: value }));
    };

    useEffect(() => {
        const { fund_from, fund_to } = transferForm;

        if (!fund_from || !fund_to) return;

        const fetchCurrencies = async () => {
            try {
                setLoadingCurrencies(true);

                const data = await getTransferCurrencies(fund_from, fund_to);

                console.log("data", data);

                let currencyLists =
                    data?.data?.map((item: any) => ({
                        value: item.coin_type,
                        label: item.coin_type,
                        balance: item.balance,
                    })) || [];

                setCurrencies(currencyLists);
            } catch (err) {
                console.error(err);
            } finally {
                setLoadingCurrencies(false);
            }
        };

        fetchCurrencies();
    }, [transferForm.fund_from, transferForm.fund_to]);

    useEffect(() => {
        const { fund_from, fund_to, coin_type } = transferForm;

        if (!fund_from || !fund_to || !coin_type) return;

        const fetchBalances = async () => {
            try {
                const data = await getFundTransferWalletBalance(
                    coin_type,
                    fund_to,
                    fund_from,
                );

                if (data.success) {
                    setFundBalances({
                        from_balance: data.data.from_balance,
                        to_balance: data.data.to_balance,
                    });
                }
            } catch (err) {
                console.error(err);
            }
        };

        fetchBalances();
    }, [transferForm.fund_from, transferForm.fund_to, transferForm.coin_type]);

    const handleSwapFunds = () => {
        if (!transferForm.fund_from || !transferForm.fund_to) return;
        setTransferForm((prev) => ({
            fund_from: prev.fund_to,
            fund_to: prev.fund_from,
            coin_type: undefined,
            amount: undefined,
        }));
    };

    const isDisabledBtn =
        !transferForm.fund_from ||
        !transferForm.fund_to ||
        !transferForm.coin_type ||
        !transferForm.amount ||
        Number(transferForm.amount) <= 0;

    const onSubmit = async () => {
        try {
            if (
                !transferForm.fund_from ||
                !transferForm.fund_to ||
                !transferForm.coin_type ||
                !transferForm.amount ||
                Number(transferForm.amount) <= 0
            ) {
                return;
            }
            setProcessing(true);
            const { amount, coin_type, fund_from, fund_to } = transferForm;
            const data = await walletBalanceTransferApi(
                fund_from,
                fund_to,
                coin_type,
                amount,
            );

            if (!data?.success) {
                toast.error(data?.message);
                return;
            }
            toast.success(data?.message);

            setTransferForm({
                fund_from: undefined,
                fund_to: undefined,
                coin_type: undefined,
                amount: undefined,
            });
        } catch (error) {
            toast.error("Something went wrong");
        } finally {
            setProcessing(false);
        }
    };

    return (
        <div>
            <WalletLayout>
                <div className="tradex-bg-background-main tradex-rounded-lg tradex-border tradex-border-background-primary tradex-shadow-[2px_2px_23px_0px_#6C6C6C0D] tradex-px-4 tradex-pt-6 tradex-pb-12 tradex-space-y-6">
                    <div className=" tradex-pb-4 tradex-border-b tradex-border-background-primary tradex-space-y-4">
                        <h2 className=" tradex-text-[32px] tradex-leading-[38px] md:tradex-text-[40px] md:tradex-leading-[48px] tradex-font-bold !tradex-text-title">
                            {t("Transfer")}
                        </h2>
                        <div
                            onClick={() => {
                                router.back();
                            }}
                            className=" tradex-flex tradex-gap-1 tradex-items-center tradex-cursor-pointer"
                        >
                            <BiArrowBack />
                            <h5 className="tradex-text-xl tradex-leading-6 tradex-font-medium !tradex-text-title">
                                {t("My Wallet")}
                            </h5>
                        </div>
                    </div>

                    <div className=" tradex-space-y-4">
                        <div className=" tradex-space-y-2">
                            <label className=" tradex-input-label tradex-mb-0 tradex-flex tradex-justify-between tradex-items-center">
                                <span>{t("From")}</span>
                            </label>

                            <Select
                                placeholder={t("Select...")}
                                classNamePrefix="deposit-withdraw-select"
                                options={fromOptions}
                                isSearchable={false}
                                value={
                                    transferForm.fund_from
                                        ? fromOptions.find(
                                            (o) => o.value === transferForm.fund_from,
                                        )
                                        : null
                                }
                                onChange={(selected) => setField("fund_from", selected?.value)}
                            />
                        </div>

                        <div
                            onClick={handleSwapFunds}
                            className=" tradex-cursor-pointer tradex-mx-auto tradex-w-10 tradex-h-10 tradex-rounded-full tradex-bg-primary tradex-text-white tradex-flex tradex-justify-center tradex-items-center"
                        >
                            <HiOutlineChevronUpDown size={20} />
                        </div>

                        <div className=" tradex-space-y-2">
                            <label className=" tradex-input-label tradex-mb-0 tradex-flex tradex-justify-between tradex-items-center">
                                <span>{t("To")}</span>
                            </label>

                            <Select
                                placeholder={t("Select...")}
                                classNamePrefix="deposit-withdraw-select"
                                options={toOptions}
                                isSearchable={false}
                                value={
                                    transferForm.fund_to
                                        ? toOptions.find((o) => o.value === transferForm.fund_to)
                                        : null
                                }
                                onChange={(selected) => setField("fund_to", selected?.value)}
                            />
                        </div>
                        <div className=" tradex-space-y-2">
                            <label className=" tradex-input-label tradex-mb-0 tradex-flex tradex-justify-between tradex-items-center">
                                <span>{t("Currency")}</span>
                            </label>

                            <Select
                                placeholder={t("Select...")}
                                classNamePrefix="deposit-withdraw-select"
                                options={currencies}
                                isDisabled={loadingCurrencies}
                                isSearchable={false}
                                value={
                                    transferForm.coin_type
                                        ? currencies.find((c) => c.value === transferForm.coin_type)
                                        : null
                                }
                                onChange={(selected) => setField("coin_type", selected?.value)}
                            />
                        </div>
                        <div className="tradex-space-y-2">
                            <p className="tradex-input-label">{t("Amount")}</p>
                            <div className=" tradex-space-y-1">
                                <div className="tradex-input-field tradex-flex tradex-justify-between tradex-items-center">
                                    <input
                                        type="number"
                                        className="tradex-w-full !tradex-pl-2.5 !tradex-border-none tradex-bg-transparent tradex-text-sm"
                                        id="amountWithdrawal"
                                        name="amount"
                                        placeholder={t("Amount...")}
                                        value={transferForm.amount ?? ""}
                                        onChange={(e) => {
                                            setField("amount", e.target.value);
                                        }}
                                    // onBlur={() => validateField("amount", amount)}
                                    />
                                </div>

                                {transferForm.coin_type && transferForm.fund_from && transferForm.fund_to && (
                                    <div className="tradex-text-xs">
                                        {t("From Balance")}: {fundBalances.from_balance}
                                        <br />
                                        {t("To Balance")}: {fundBalances.to_balance}
                                    </div>
                                )}
                            </div>
                        </div>
                        <div className=" tradex-pt-6">
                            <button
                                className={`
             ${(isDisabledBtn || processing) &&
                                    " tradex-cursor-not-allowed tradex-opacity-50"
                                    } tradex-w-full tradex-flex tradex-items-center tradex-justify-center tradex-min-h-[56px] tradex-py-4 tradex-rounded-lg tradex-bg-primary tradex-text-white`}
                                type="button"
                                disabled={isDisabledBtn || processing}
                                onClick={onSubmit}
                            >
                                {processing ? t("Processing...") : t("Transfer")}
                            </button>
                        </div>
                    </div>
                </div>
            </WalletLayout>

            <Footer />
        </div>
    );
}
