import useTranslation from "next-translate/useTranslation";
import React, { useEffect, useState } from "react";
import { FaHome } from "react-icons/fa";
import { ImListNumbered } from "react-icons/im";
import { copyTextById, formateZert } from "common";
import WalletGoogleAuth from "components/wallet/wallet-google-auth";
import { UserSettingsApi } from "service/settings";
import { IoIosArrowBack } from "react-icons/io";
import Link from "next/link";
import { RootState } from "state/store";
import { useSelector } from "react-redux";
import { WalletWithdrawProcessApiAction } from "state/actions/wallet";
import {
  WITHDRAW_FESS_FIXED,
  WITHDRAW_FESS_PERCENT,
} from "helpers/core-constants";
import { getFeeAmountApi } from "service/wallet";
import { useRouter } from "next/router";

export const WithdrawComponent = ({
  responseData,
  baseType,
  fullPage,
}: any) => {
  const { t } = useTranslation("common");
  const { settings } = useSelector((state: RootState) => state.common);
  const [selectedNetwork, setSelectedNetwork] = React.useState(
    responseData?.coin_payment_networks &&
      responseData?.coin_payment_networks[0]
  );
  const [withdrawalCredentials, setWithdrawalCredentials] = React.useState({
    wallet_id: responseData?.wallet?.id,
    code: "",
    address: "",
    amount: "",
    note: "withdrawal",
    memo: "",
    network_type: selectedNetwork?.network_type ?? "",
    network_id: "",
    base_type: "",
  });

  const [feesData, setFeesData] = React.useState<any>({});
  const [errorMessage, setErrorMessage] = React.useState({
    status: false,
    message: "",
  });
  const [processing, setProcessing] = React.useState(false);
  const handleSubmit = async (e: any) => {
    e.preventDefault();
    let credentials = {
      ...withdrawalCredentials,
      wallet_id: responseData?.wallet?.id,
      base_type: responseData?.data?.base_type,
      network_id: selectedNetwork?.id,
    };
    if (
      responseData?.data?.base_type == 8 ||
      responseData?.data?.base_type == 6 ||
      responseData?.data?.base_type == 10
    ) {
      credentials = {
        ...credentials,
        base_type: selectedNetwork?.base_type,
      };
    }

    if (
      parseInt(responseData?.data?.base_type) !== 8 &&
      parseInt(responseData?.data?.base_type) !== 6 &&
      parseInt(responseData?.data?.base_type) !== 10
    ) {
      if (
        // responseData?.wallet.coin_type != "USDT" &&
        parseInt(responseData?.data?.base_type) === 1
      ) {
        credentials = {
          ...credentials,
          network_id: responseData?.wallet?.network_id,
        };
      }
    }

    const response = await WalletWithdrawProcessApiAction(
      credentials,
      setProcessing
    );
    if (response.success) {
      setWithdrawalCredentials({
        wallet_id: responseData?.wallet?.id,
        code: "",
        address: "",
        amount: "",
        note: "withdrawal",
        memo: "",
        network_type: selectedNetwork?.network_type ?? "",
        network_id: "",
        base_type: "",
      });
    }
  };
  const CheckG2faEnabled = async () => {
    const { data } = await UserSettingsApi();
    const { user } = data;
    if (
      !user.google2fa_secret &&
      parseInt(settings.two_factor_withdraw) === 1
    ) {
      setErrorMessage({
        status: true,
        message: "Google 2FA is not enabled, Please enable Google 2FA fist",
      });
    }
  };

  React.useEffect(() => {
    if (
      responseData?.coin_payment_networks &&
      responseData?.coin_payment_networks[0]
    ) {
      setSelectedNetwork(responseData?.coin_payment_networks[0]);
    }
  }, [responseData?.coin_payment_networks]);
  React.useEffect(() => {
    setWithdrawalCredentials((prev) => ({
      ...prev,
      wallet_id: responseData?.wallet?.id,
    }));

    CheckG2faEnabled();
  }, [responseData?.wallet?.id]);

  React.useEffect(() => {
    if (
      parseInt(responseData?.data?.base_type) === 1 &&
      responseData?.wallet.coin_type == "USDT"
    ) {
      setWithdrawalCredentials((prev) => ({
        ...prev,
        network_type: selectedNetwork?.network_type,
      }));
      return;
    }
    if (
      parseInt(responseData?.data?.base_type) === 8 ||
      parseInt(responseData?.data?.base_type) === 6 ||
      parseInt(responseData?.data?.base_type) === 10
    ) {
      setWithdrawalCredentials((prev) => ({
        ...prev,
        network_type: "",
      }));
    }
  }, [selectedNetwork?.network_type]);

  useEffect(() => {
    if (
      parseInt(responseData?.data?.base_type) === 1 &&
      responseData?.wallet.coin_type == "USDT"
    ) {
      setSelectedNetwork(responseData?.data?.coin_payment_networks[0]);

      setWithdrawalCredentials((prev) => ({
        ...prev,
        network_id: responseData?.data?.coin_payment_networks[0]?.id,
      }));
    }
  }, [responseData?.wallet.coin_type]);

  const checkNetworkFunc = (networkId: any) => {
    if (networkId == 4) {
      return `(ERC20 Token)`;
    }
    if (networkId == 5) {
      return `(BEP20 Token)`;
    }
    if (networkId == 6) {
      return `(TRC20 Token)`;
    }
    return "";
  };

  useEffect(() => {
    if (withdrawalCredentials?.address && withdrawalCredentials?.amount) {
      getFeeAmount();
    }
  }, [withdrawalCredentials]);

  const getFeeAmount = async () => {
    let newCredntials = { ...withdrawalCredentials };
    if (
      parseInt(responseData?.data?.base_type) !== 8 &&
      parseInt(responseData?.data?.base_type) !== 6 &&
      parseInt(responseData?.data?.base_type) !== 10
    ) {
      if (
        // responseData?.wallet.coin_type != "USDT" &&
        parseInt(responseData?.data?.base_type) === 1
      ) {
        newCredntials = {
          ...newCredntials,
          network_id: responseData?.wallet?.network_id,
        };
      }
    }
    if (
      parseInt(responseData?.data?.base_type) === 2 ||
      parseInt(responseData?.data?.base_type) === 3
    ) {
      newCredntials = {
        ...newCredntials,
        network_id: responseData?.wallet?.network_id,
      };
    }

    const response = await getFeeAmountApi(newCredntials);
    if (!response.success) {
      return;
    }
    setFeesData(response.data);
  };

  return (
    <div className="tradex-space-y-6">
      <div className=" tradex-space-y-2">
        <p className=" tradex-input-label tradex-mb-0">{t("Total Balance")}</p>
        <div className="tradex-input-field tradex-flex tradex-justify-between tradex-items-center">
          <div className=" tradex-flex tradex-gap-2 tradex-items-center">
            <img
              className=" tradex-max-w-[25px] tradex-max-h-[25px] tradex-object-cover tradex-object-center"
              src={responseData?.wallet?.coin_icon || "/bitcoin.png"}
              alt="coin"
              width="25px"
              height="25px"
            />
            <p className=" tradex-text-sm tradex-text-body">
              {responseData?.wallet?.coin_type}
            </p>
          </div>

          <p className="tradex-text-sm tradex-text-body">
            {responseData?.wallet?.balance
              ? responseData?.wallet?.balance +
                " " +
                responseData?.wallet?.coin_type
              : "Loading"}
          </p>
        </div>
      </div>

      <div className=" tradex-space-y-6">
        {responseData?.wallet.coin_type == "USDT" &&
          parseInt(responseData?.network?.id) === 1 && (
            <div className="tradex-space-y-2">
              <p className="tradex-input-label">{t("Select Network")}</p>
              <select
                name="currency"
                className="tradex-input-field !tradex-bg-background-primary !tradex-border-solid !tradex-border !tradex-border-background-primary"
                onChange={(e) => {
                  const findObje = responseData?.coin_payment_networks?.find(
                    (x: any) => x.network_type === e.target.value
                  );
                  setSelectedNetwork(findObje);
                }}
              >
                {responseData?.coin_payment_networks?.map(
                  (item: any, index: number) => (
                    <option value={item.network_type} key={index}>
                      {item?.network_name}
                    </option>
                  )
                )}
              </select>
            </div>
          )}

        <div className="tradex-space-y-2">
          <p className="tradex-input-label">{t("Address")}</p>
          <div className=" tradex-space-y-1">
            <div className="tradex-input-field tradex-flex tradex-justify-between tradex-items-center">
              <input
                type="text"
                className=" tradex-w-full !tradex-border-none tradex-bg-transparent tradex-text-sm"
                id="address"
                name="address"
                placeholder={t("Address")}
                value={withdrawalCredentials.address}
                onChange={(e) => {
                  setWithdrawalCredentials((prev) => ({
                    ...prev,
                    address: e.target.value,
                  }));
                }}
              />
              <span className="input-address-bar-btn">
                <FaHome />
              </span>
            </div>

            <p className="tradex-text-red-600 tradex-text-xs">
              {t(
                `Only enter a ${
                  responseData?.wallet?.coin_type ?? ""
                } ${checkNetworkFunc(
                  responseData?.network?.id
                )} address in this field. Otherwise the asset you withdraw, may be lost.`
              )}
            </p>
          </div>
        </div>

        <div className="tradex-space-y-2">
          <p className="tradex-input-label">{t("Amount")}</p>
          <div className="tradex-space-y-1">
            <div className="tradex-space-y-1">
              <div className="tradex-input-field tradex-flex tradex-justify-between tradex-items-center">
                <input
                  type="number"
                  className="tradex-w-full !tradex-border-none tradex-bg-transparent !tradex-text-sm"
                  id="amountWithdrawal"
                  name="amount"
                  placeholder={t("AMOUNT To Withdraw")}
                  value={withdrawalCredentials.amount}
                  onChange={(e) => {
                    setWithdrawalCredentials((prev) => ({
                      ...prev,
                      amount: e.target.value,
                    }));
                  }}
                />
                <span className="input-address-bar-btn">
                  <ImListNumbered />
                </span>
              </div>
              {Object.keys(feesData).length > 0 && (
                <div>
                  <p className=" tradex-text-xs tradex-text-body">
                    {t(
                      `You will be charged ${feesData?.fees} ${feesData?.coin_type} as Withdrawal Fee for this withdrawal.`
                    )}
                  </p>
                </div>
              )}
              {responseData?.wallet?.withdrawal_fees_type ==
                WITHDRAW_FESS_PERCENT && (
                <small>
                  {/* <span className="mr-2">
                    {t("Fees ")}
                    {parseFloat(responseData?.wallet?.withdrawal_fees).toFixed(
                      8
                    )}{" "}
                    %
                  </span> */}
                  <span className="mr-2">
                    {t("Min withdraw ")}{" "}
                    {parseFloat(
                      responseData?.wallet?.minimum_withdrawal
                    ).toFixed(5)}
                    {responseData?.wallet?.coin_type}
                  </span>
                  <span className="mr-2">
                    {t("Max withdraw")}{" "}
                    {parseFloat(responseData?.wallet?.maximum_withdrawal)}{" "}
                    {responseData?.wallet?.coin_type}
                  </span>
                </small>
              )}
            </div>
          </div>
        </div>
        <div className="tradex-space-y-2">
          <p className=" tradex-input-label">
            {t("Memo")} ({t("optional")})
          </p>
          <div className=" tradex-space-y-1">
            <input
              type="text"
              className="tradex-input-field !tradex-text-sm"
              id="memo"
              name="memo"
              placeholder={t("Memo if needed")}
              value={withdrawalCredentials.memo}
              onChange={(e) => {
                setWithdrawalCredentials({
                  ...withdrawalCredentials,
                  memo: e.target.value,
                });
              }}
            />

            <p className=" tradex-text-xs tradex-text-body">
              {t(
                `Add your memo if needed but please ensure it that's correct, otherwise you lost coin.`
              )}
            </p>
          </div>
        </div>

        <WalletGoogleAuth
          handleSubmit={handleSubmit}
          withdrawalCredentials={withdrawalCredentials}
          setWithdrawalCredentials={setWithdrawalCredentials}
          processing={processing}
        />
        <input type="hidden" name="wallet_id" value="19" />
        {errorMessage.status && (
          <div className="alert alert-danger">{errorMessage.message}</div>
        )}
        {parseInt(settings.two_factor_withdraw) === 1 ? (
          <button
            type="button"
            className={` ${
              (withdrawalCredentials.address === "" ||
                withdrawalCredentials.amount === "" ||
                errorMessage.status === true ||
                processing) &&
              "tradex-cursor-not-allowed"
            } tradex-w-full tradex-flex tradex-items-center tradex-justify-center tradex-min-h-[56px] tradex-py-4 tradex-rounded-lg tradex-bg-primary tradex-text-white`}
            data-target="#exampleModal"
            disabled={
              withdrawalCredentials.address === "" ||
              withdrawalCredentials.amount === "" ||
              errorMessage.status === true ||
              processing
            }
            data-toggle="modal"
            onClick={() => {
              setErrorMessage({
                status: false,
                message: "",
              });
            }}
          >
            {t("Withdraw")}
          </button>
        ) : (
          <button
            className={` ${
              (withdrawalCredentials.address === "" ||
                withdrawalCredentials.amount === "" ||
                processing) &&
              "tradex-cursor-not-allowed"
            } tradex-w-full tradex-flex tradex-items-center tradex-justify-center tradex-min-h-[56px] tradex-py-4 tradex-rounded-lg tradex-bg-primary tradex-text-white`}
            type="button"
            style={{ height: "44px" }}
            disabled={
              withdrawalCredentials.address === "" ||
              withdrawalCredentials.amount === "" ||
              processing
            }
            onClick={handleSubmit}
          >
            {processing ? t("Processing..") : t("Withdraw")}
          </button>
        )}
      </div>
    </div>
  );
};
