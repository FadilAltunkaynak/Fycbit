import { Connection, LAMPORTS_PER_SOL, ParsedAccountsModeBlockResponse, ParsedBlockResponse, ParsedMessageAccount } from "@solana/web3.js";
import prisma from "../../client"
import { ADDRESS_TYPE_EXTERNAL, SOLANA_BASE_COIN, STATUS_ACTIVE } from "../../utils/coreConstant";
import { processNativeTransaction } from "./sol.native.service";
import { getTokenAddressMap, getTokenDepositsFromBlock, getTokenTransferInstructions } from "./sol.token.service";
import { networksPayload } from "@prisma/client";
import { Types, DefaultArgs } from "@prisma/client/runtime";
import { DepositFormBlock } from "../../types/solana.types";

export type BlockTransactions = Array<Omit<ParsedBlockResponse['transactions'][number], 'transaction'> & {
    transaction: Pick<ParsedBlockResponse['transactions'][number]['transaction'], 'signatures'> & {
        accountKeys: ParsedMessageAccount[];
    };
}>

export type CoinType = {
    coinId: number;
    coinType: string;
}

const getParsedTransactions = async (
    transaction: any,
    mintToCoinDecimalMap: Map<string, number>
): Promise<DepositFormBlock[] | null> => {

    let depositsFromBlock: DepositFormBlock[] = [];

    const nativeTransactions = await processNativeTransaction({
        transaction: transaction,
        coinDecimal: mintToCoinDecimalMap['native'] ? mintToCoinDecimalMap['native'] : Math.log10(LAMPORTS_PER_SOL)
    });

    if (nativeTransactions) {
        depositsFromBlock.push(...nativeTransactions);
    }

    const tokenTransactions = await getTokenTransactions(transaction, mintToCoinDecimalMap);

    if (tokenTransactions) {
        depositsFromBlock.push(...tokenTransactions);
    }

    return depositsFromBlock;
}

const getTokenTransactions = async (
    transaction: any,
    mintToCoinDecimalMap: any
): Promise<DepositFormBlock[] | null> => {

    if (transaction?.meta?.err !== null) {
        return null;
    }

    const tokenAddressMapFromTransaction = await getTokenAddressMap(transaction);

    const tokenInstructions = await getTokenTransferInstructions(
        transaction,
        tokenAddressMapFromTransaction,
    )
    
    return await getTokenDepositsFromBlock(
        tokenInstructions,
        transaction,
        mintToCoinDecimalMap,
        tokenAddressMapFromTransaction,
        transaction.transaction.signatures[0]
    );

}

const getTransactionDataFromBlock = async (
    networkData: Types.GetResult<networksPayload, DefaultArgs>,
    client: Connection,
    slotNumber: number,
): Promise<BlockTransactions> => {
    try {
        const blockData = await client.getParsedBlock(Number(slotNumber), {
            maxSupportedTransactionVersion: 0,
            transactionDetails: 'full',
            rewards: false,
        });

        return blockData.transactions;
    } catch (error: any) {
        if (
            (error.name === 'SolanaJSONRPCError' && error.message.includes('ledger jump'))
            || error.message.includes('skipped, or missing')) {

            if (process.env.BLOCK_PROCESSOR_DEBUG_LOG == 'ON') {
                console.log(`[${networkData.slug}]: slot (${slotNumber}) skipped`);
                console.log(`[${networkData.slug}]: slot skip err msg: ${error.message}`);
            }
            return [];
        }

        throw error;
    }
}

const depositExists = async (
    transactionId: string,
    coinId: number,
    address: string,
    networkId: Number
): Promise<boolean> => {
    const deposit = await prisma.deposite_transactions.findFirst({
        where: {
            transaction_id: transactionId,
            coin_id: Number(coinId),
            address: address,
            network_id: Number(networkId)
        }
    });

    return !!deposit;
}


const depositTransactions = async (
    transactions: DepositFormBlock[],
    networkData: Types.GetResult<networksPayload, DefaultArgs>,
    systemWalletAddress: string,
    mintAddressToCurrencyIdMap: Map<string, CoinType>
) => {
    let depositAddressMap: Map<string, boolean> = new Map<string, boolean>();

    for (const transaction of transactions) {

        if (transaction.from_address == systemWalletAddress) {
            console.log(`[${networkData.slug}]: deposit skipped due to sent from system wallet. tx hash: ${transaction.transaction_id}`);
            continue;
        }

        const mintOrType = transaction.contract_address || transaction.type;
        const coinId = mintAddressToCurrencyIdMap.has(mintOrType.toLowerCase()) ? mintAddressToCurrencyIdMap.get(mintOrType.toLowerCase()).coinId : null;
        if (!coinId) {
            continue;
        }

        const key = transaction.transaction_id + '_' +
            String(coinId) + '_' +
            transaction.to_address

        if (depositAddressMap.has(key)) {
            continue;
        }

        if (await depositExists(transaction.transaction_id, Number(coinId), transaction.to_address, Number(networkData.id))) {
            depositAddressMap.set(key, true);
            continue;
        }

        const walletId = await getWalletId(Number(networkData.id), Number(coinId), transaction.to_address);

        if (!walletId) {
            continue;
        }

        await depositSingleTransaction(
            walletId,
            transaction,
            mintAddressToCurrencyIdMap.get(mintOrType.toLowerCase()).coinType,
            Number(networkData.id),
            coinId
        );

        return;
    }
}

const depositSingleTransaction = async (
    walletId: number,
    transaction: DepositFormBlock,
    coinType: string,
    networkId: number,
    coinId: number
) => {

    const date = new Date();

    await prisma.$transaction([
        prisma.wallets.update({
            where: {
                id: Number(walletId)
            },
            data: {
                balance: {
                    increment: transaction.amount
                }
            }
        }),

        prisma.deposite_transactions.create({
            data: {
                address: transaction.to_address,
                from_address: transaction.from_address,
                fees: transaction.fee_limit,
                receiver_wallet_id: Number(walletId),
                address_type: String(ADDRESS_TYPE_EXTERNAL),
                coin_type: coinType,
                amount: Number(transaction.amount),
                transaction_id: transaction.transaction_id,
                status: STATUS_ACTIVE,
                network_id: Number(networkId),
                network_type: String(networkId),
                confirmations: STATUS_ACTIVE,
                coin_id: Number(coinId),
                created_at: date.toISOString(),
                updated_at: date.toISOString()
            }
        })

    ]);
}

const getWalletId = async (
    networkId: number,
    coinId: number,
    address: string,
): Promise<number | null> => {
    const wallet = await prisma.wallet_address_histories.findFirst({
        select: {
            wallet_id: true
        },
        where: {
            network_id: Number(networkId),
            address: address,
            coin_id: Number(coinId),
        }
    });

    return wallet ? Number(wallet.wallet_id) : null;
}

const getSystemWalletAddress = async (networkId: number): Promise<string | null> => {
    const systemWallet = await prisma.admin_wallet_keys.findFirst({
        select: {
            address: true
        },
        where: {
            network_id: Number(networkId),
        }
    });

    return systemWallet?.address || null;
}

export {
    getTransactionDataFromBlock,
    getSystemWalletAddress,
    getParsedTransactions,
    depositTransactions
}