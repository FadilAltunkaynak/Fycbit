import { deposite_transactionsPayload, networksPayload, wallet_address_historiesPayload } from "@prisma/client";
import { Decimal, DefaultArgs, Types } from "@prisma/client/runtime";
import { EvmTxData } from "../../types/block.processor.meta.data.type";
import prisma from "../../client";
import { ADDRESS_TYPE_EXTERNAL, NATIVE_COIN, STATUS_ACTIVE } from "../../utils/coreConstant";
import { decodeInputParameter } from "../evm/erc20.token.service";
import { depositUserWalletBySingleTx } from "../evm/deposit.service";

const checkIfTokenTransfer = (txData: EvmTxData, decimalMap: Map<string, number>): boolean => {
    const contractAddress = txData.to_address.toLowerCase();
    return decimalMap.has(contractAddress);
}

const processEthNativeCoinDeposit = async (
    networkData: Types.GetResult<networksPayload, DefaultArgs>,
    txData: EvmTxData
) => {

    const walletAddress = await prisma.wallet_address_histories.findFirst({
        where: {
            address: txData.to_address,
            network_id: Number(networkData.id),
        },
    });

    if(!walletAddress) {
        return;
    }

    const address = txData.to_address;
    const txHash = txData.tx_hash;
    const amount = txData.amount;

    const nativeCoinNetwork = await prisma.coin_networks.findFirst({
        where: {
            network_id: Number(walletAddress.network_id),
            type: NATIVE_COIN,
        },
    });

    if (!nativeCoinNetwork) {
        console.log('[Eth Block Processor] Native Coin network not found, skipped');
        return;
    }

    const nativeCoin = await prisma.coins.findFirst({
        where: { id: nativeCoinNetwork.currency_id },
    });

    if (!nativeCoin) {
        throw new Error(`Native coin for ${networkData.name} not found. But coin network exists.`);
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
        coin_type: walletAddress.coin_type,
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
};

const processEthTokenCoinDeposit = async (
    networkData: Types.GetResult<networksPayload, DefaultArgs>,
    txData: EvmTxData,
    toAddress?: string,
    amount?: number
) => {
    if(!toAddress || !amount) {
        return;
    }
    const contractAddress = txData.to_address;
    const txHash = txData.tx_hash;

    const tokenCoinNetwork = await prisma.coin_networks.findFirst({
        where: { contract_address: contractAddress },
    });

    if (!tokenCoinNetwork) {
        return;
    }

    const tokenCoin = await prisma.coins.findFirst({
        where: { id: tokenCoinNetwork.currency_id },
    });

    if (!tokenCoin) {
        throw new Error('Token Coin data not found');
    }

    const walletAddress = await prisma.wallet_address_histories.findFirst({
        where: {
            address: toAddress,
            network_id: Number(networkData.id),
            coin_id: Number(tokenCoin.id),
        },
    });

    if (!walletAddress) return;

    const depositHistory: Partial<Types.GetResult<deposite_transactionsPayload, DefaultArgs>> = {
        address: toAddress,
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
};

export {
    processEthTokenCoinDeposit,
    checkIfTokenTransfer,
    processEthNativeCoinDeposit
}