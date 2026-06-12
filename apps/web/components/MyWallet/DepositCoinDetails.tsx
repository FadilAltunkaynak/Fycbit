import useTranslation from "next-translate/useTranslation";
import React, { useEffect, useMemo, useState } from "react";
import DipositQrCode from "./diposit-qr-code";
import DipositAddressCopyField from "./diposit-address-copy-field";
import TokenAddressBox from "./TokenAddressBox";
import { toast } from "react-toastify";
import { getWalletDepositAddressApi } from "service/wallet";
import { BITGO_API, COIN_PAYMENT } from "helpers/core-constants";

export default function DepositCoinDetails({
  coinDetails,
  selectedCoin,
  address,
  setAddress,
  setActiveProvider,
}: any) {
  const { t } = useTranslation("common");
  const [isGettingAddress, setIsGettingAddress] = useState<boolean>(false);
  const [selectedNetwork, setSelectedNetwork] = useState<any>("");
  const [memo, setMemo] = useState<any>("");
  const [contactAddress, setContactAddress] = useState<any>("");

  const networks = useMemo(() => {
    return coinDetails?.coin_payment_networks?.length > 0
      ? coinDetails.coin_payment_networks
      : coinDetails?.networks ?? [];
  }, [coinDetails]);

  const getNetworkAddress = async (payload: any) => {
    if (!selectedCoin?.id) {
      toast.error("Please select a coin type first.");
      setSelectedNetwork("");
      return;
    }
    setIsGettingAddress(true);
    setAddress("");
    setMemo("");
    setContactAddress("");
    try {
      const response = await getWalletDepositAddressApi({
        coin_id: selectedCoin?.id,
        ...payload,
      });
      if (!response?.success) {
        toast.error(response.message);
        return;
      }

      toast.success(response.message);

      setAddress(response.data?.address || "");
      setMemo(response.data?.memo || "");
      setContactAddress(response.data?.token_address || "");
    } catch (error) {
      toast.error("Something went wrong");
      setSelectedNetwork("");
    } finally {
      setIsGettingAddress(false);
    }
  };

  useEffect(() => {
    if (networks.length === 0) return;

    const isPaymentOrBitgo = [COIN_PAYMENT, BITGO_API].includes(
      networks[0].provider_type
    );

    if (isPaymentOrBitgo) {
      setSelectedNetwork(networks[0].id);
      setActiveProvider(networks[0].active_provider);
      getNetworkAddress({ network_id: networks[0].id });
    }
  }, [networks]);

  const renderNetworkSelect = () => {
    if (networks.length === 0) return null;
    const isPaymentNetwork = coinDetails?.coin_payment_networks?.length > 0; // Only USDT with CoinPayment

    if ([COIN_PAYMENT, BITGO_API].includes(networks[0].provider_type))
      return null;

    return (
      <div className="tradex-space-y-2">
        <p className="tradex-input-label tradex-mb-0">{t("Select Network")}</p>
        <select
          name="currency"
          className="tradex-input-field !tradex-bg-background-primary !tradex-border-solid !tradex-border !tradex-border-background-primary"
          onChange={(e) => {
            const value = e.target.value;
            setSelectedNetwork(value);

            if (!value) return;

            if (!isPaymentNetwork) {
              const activeProvider =
                coinDetails?.networks?.find((n: any) => n.id == value)
                  ?.active_provider || null;
              setActiveProvider(activeProvider);
              getNetworkAddress({ network_id: value });
            } else {
              getNetworkAddress({ network_type: value });
            }
          }}
        >
          <option value="">{t("Select Network")}</option>
          {networks.map((item: any, idx: number) => (
            <option
              key={idx}
              value={isPaymentNetwork ? item.network_type : item.id}
            >
              {item.network_name || item.name}
            </option>
          ))}
        </select>
      </div>
    );
  };

  return (
    <>
      {memo && (
        <div className=" tradex-space-y-2">
          <p className="tradex-input-label tradex-mb-0">{t("Memo")}</p>
          <div className="tradex-input-field">
            <p className="tradex-text-sm tradex-text-body tradex-overflow-hidden tradex-text-ellipsis">
              {coinDetails?.memo}
            </p>
          </div>
        </div>
      )}

      {renderNetworkSelect()}

      {selectedNetwork && (
        <>
          <div className="tradex-space-y-2">
            <p className="tradex-input-label tradex-mb-0">
              {t("Deposit Address")}
            </p>
            <p className="tradex-input-field !tradex-h-auto tradex-py-[14px] !tradex-text-sm">
              {t("Only send")} ${selectedCoin?.coin_type ?? ""} $
              {coinDetails?.network?.name
                ? `(${coinDetails?.network?.name})`
                : ""}{" "}
              {t("to this address")}.
              {t(
                "Sending any others asset to this adress may result in the loss of your deposit!"
              )}
            </p>
          </div>
          {!isGettingAddress &&
            (address ? (
              <div className=" tradex-flex tradex-justify-center tradex-items-center tradex-flex-col tradex-space-y-4">
                <DipositQrCode address={address} />

                <DipositAddressCopyField address={address} />
              </div>
            ) : (
              <p className="tradex-input-field !tradex-text-sm !tradex-items-center tradex-justify-center">
                {t("No address found!")}
              </p>
            ))}
          {contactAddress && <TokenAddressBox token_address={contactAddress} />}
        </>
      )}
    </>
  );
}
