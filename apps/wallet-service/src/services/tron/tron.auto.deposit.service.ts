import { Decimal, DefaultArgs, Types } from "@prisma/client/runtime";
import { TronBlockTx, TronTxData } from "../../types/tron.types";
import { convertAddressAmount } from "../evm/trx.tron-web.service";
import { deposite_transactionsPayload, networksPayload } from "@prisma/client";
import prisma from "../../client";
import { ADDRESS_TYPE_EXTERNAL, NATIVE_COIN, STATUS_ACTIVE } from "../../utils/coreConstant";
import { depositUserWalletBySingleTx } from "../evm/deposit.service";
import { initializeTronWeb } from "../evm/trx.token.service";
import { convertCoinAmountFromInt } from "../../utils/helper";

const processTronTxDetails = async (rpcUrl: string, transaction: TronBlockTx, resBlockNumber: number, decimal?: number) => {
    let data: TronTxData = {};
    const rawData = transaction.raw_data;
    const contractType = rawData.contract[0].type;
    const rawTransactionData = rawData.contract[0].parameter;
    
    if (contractType === 'TransferContract') {
        const convertData = await convertAddressAmountForAutoDeposit(
            rpcUrl,
            'native',
            rawTransactionData.value.owner_address,
            rawTransactionData.value.to_address,
            rawTransactionData.value.amount
        );
        const fromAddress = convertData.from_address;
        const toAddress = convertData.to_address;
        const amount = convertData.amount;
        let tx_type = 'native';
        data = {
            tx_type: tx_type,
            from_address: fromAddress,
            to_address: toAddress,
            amount: amount,
            block_number: resBlockNumber,
            transaction_id: transaction.txID,
            contract_address: '',
            fee_limit: rawData.fee_limit ? rawData.fee_limit : 0,
        };
    } else if (contractType === 'TriggerSmartContract') {
        const valueData = rawTransactionData.value.data;
        const method = valueData.slice(0, 10);
        if (method === 'a9059cbb00') {
            const toAddress = '0x' + valueData.slice(32, 72);
            let amountData = '0x' + valueData.slice(74);
            const amount = parseInt(amountData, 16);
            const convertData = await convertAddressAmountForAutoDeposit(
                rpcUrl,
                'token',
                rawTransactionData.value.owner_address,
                toAddress,
                amount,
                rawTransactionData.value.contract_address,
                decimal
            );
            const fromAddress = convertData.from_address;
            const to_address = convertData.to_address;
            const amountVal = convertData.amount;
            const contract_address = convertData.contract_address;
            
            data = {
                tx_type: 'token',
                from_address: fromAddress,
                to_address: to_address,
                amount: amountVal,
                block_number: resBlockNumber,
                transaction_id: transaction.txID,
                contract_address: String(contract_address),
                fee_limit: rawData.fee_limit ? rawData.fee_limit : 0,
            };
        }
    }
    
    return data;
}

const checkIfTokenTransfer = (transaction: TronBlockTx): boolean => {
    return transaction.raw_data.contract[0].type === 'TriggerSmartContract';
}

const convertAddressAmountForAutoDeposit = async (
    rpcUrl: string,
    type: string,
    fromAddress: string,
    toAddress: string,
    amountVal: any,
    contractAddress?: string,
    coinDecimal?: number
) => {
    try {
        const tronWeb = await initializeTronWeb(rpcUrl);
        const from_address = tronWeb.address.fromHex(fromAddress);
        let to_address = toAddress;
        let contract_address = "";
        let amount = 0;
        if (type == "token") {
            to_address = tronWeb.address.fromHex(tronWeb.address.toHex(toAddress));
            if (contractAddress) {
                contract_address = tronWeb.address.fromHex(contractAddress);
            }

            let decimal = 18;
            decimal = coinDecimal;
            amount = Number(convertCoinAmountFromInt(amountVal, decimal));
        } else {
            to_address = tronWeb.address.fromHex(toAddress);
            amount = parseFloat(tronWeb.fromSun(amountVal));
        }

        return {
            from_address: from_address,
            to_address: to_address,
            contract_address: contract_address,
            amount: amount,
        };
    } catch (err) {
        console.log("ex err", err);
        throw err;
    }
};

const checkAndProcessTronDeposit = async (
    networkData: Types.GetResult<networksPayload, DefaultArgs>,
    txData: TronTxData
): Promise<void> => {
    if (txData) {
        let address = txData.to_address;
        let txHash = txData.transaction_id;
        let amount = txData.amount;
        let walletAddress: any = null;

        if (address) {
            if (txData.tx_type == 'native') {
                // native tron coin deposit
                walletAddress = await prisma.wallet_address_histories.findFirst({
                    where: {
                        address: txData.to_address,
                        network_id: Number(networkData.id),
                    },
                });

                if (walletAddress) {
                    const nativeCoinNetwork = await prisma.coin_networks.findFirst({
                        where: {
                            network_id: Number(walletAddress.network_id),
                            type: NATIVE_COIN,
                        },
                    });

                    if (!nativeCoinNetwork) {
                        console.log('Native Coin network not found, skipped');
                        return;
                    }

                    const nativeCoin = await prisma.coins.findFirst({
                        where: { id: nativeCoinNetwork.currency_id },
                    });

                    if (!nativeCoin) {
                        throw new Error('Native Coin not found');
                    }
                    
                    let wallet = await prisma.wallets.findFirst({
                        where: {
                            user_id: walletAddress.user_id,
                            coin_id: nativeCoinNetwork.currency_id,
                        },
                    });

                    if (!wallet) {
                        wallet = await prisma.wallets.create({
                            data: {
                                user_id: walletAddress.user_id,
                                coin_id: nativeCoinNetwork.currency_id,
                                coin_type: nativeCoin.coin_type,
                                name: `${nativeCoin.coin_type} Wallet`,
                            },
                        });
                    }

                    const depositHistory: Partial<Types.GetResult<deposite_transactionsPayload, DefaultArgs>> = {
                        address: address,
                        receiver_wallet_id: wallet.id,
                        address_type: String(ADDRESS_TYPE_EXTERNAL),
                        coin_type: nativeCoin.coin_type,
                        amount: new Decimal(amount),
                        transaction_id: txHash,
                        status: STATUS_ACTIVE,
                        confirmations: 1,
                        from_address: txData.from_address,
                        network_id: Number(walletAddress.network_id),
                        block_number: String(txData.block_number),
                        coin_id: Number(nativeCoinNetwork.currency_id),
                        network_type: String(networkData.base_type)
                    };

                    await depositUserWalletBySingleTx(<any>depositHistory, networkData);
                }

            } else {
                // token deposit check
                const tokenCoinNetwork = await prisma.coin_networks.findFirst({
                    where: { contract_address: txData.contract_address },
                });
                
                if (!tokenCoinNetwork) return;

                const tokenCoin = await prisma.coins.findFirst({
                    where: { id: tokenCoinNetwork.currency_id },
                });

                if (!tokenCoin) {
                    throw new Error('Token Coin data not found');
                }
                
                const walletAddress = await prisma.wallet_address_histories.findFirst({
                    where: {
                        address: txData.to_address,
                        network_id: Number(networkData.id),
                        coin_id: Number(tokenCoin.id),
                    },
                });

                if (!walletAddress) return;

                const depositHistory: Partial<Types.GetResult<deposite_transactionsPayload, DefaultArgs>> = {
                    address: txData.to_address,
                    receiver_wallet_id: walletAddress.wallet_id,
                    address_type: String(ADDRESS_TYPE_EXTERNAL),
                    coin_type: walletAddress.coin_type,
                    amount: new Decimal(amount),
                    transaction_id: txHash,
                    status: STATUS_ACTIVE,
                    confirmations: 1,
                    from_address: txData.from_address,
                    network_id: Number(walletAddress.network_id),
                    block_number: String(txData.block_number),
                    coin_id: Number(walletAddress.coin_id),
                    network_type: String(networkData.base_type)
                };

                await depositUserWalletBySingleTx(<any>depositHistory, networkData);
            }
        }
    }

};

export {
    processTronTxDetails,
    checkAndProcessTronDeposit,
    checkIfTokenTransfer
}