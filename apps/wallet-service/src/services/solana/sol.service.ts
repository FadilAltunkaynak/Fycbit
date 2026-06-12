import base58, * as bs58 from "bs58";
import { generateErrorResponse, generateSuccessResponse, ResponseData } from "../../utils/commonObject";
import {
    AccountInfo,
    Connection,
    Keypair,
    LAMPORTS_PER_SOL,
    PublicKey,
    TransactionMessage,
    VersionedMessage,
    VersionedTransaction,
} from "@solana/web3.js";
import {
    ASSOCIATED_TOKEN_PROGRAM_ID,
    createAssociatedTokenAccount,
    createAssociatedTokenAccountInstruction,
    getAssociatedTokenAddress,
    getMinimumBalanceForRentExemptAccount,
    NATIVE_MINT,
    TOKEN_PROGRAM_ID,
} from "@solana/spl-token";
import { SOLANA_MIN_ACCOUNT_RENT_IN_SOL, STATUS_ACTIVE } from "../../utils/coreConstant";
import { getSolanaTokenSendingMessage, processTokenTrasction, tokenAccountExists } from "./sol.token.service";
import { getNativeCoinSendingMessage, processNativeTransaction, sendSolanaNativeCoin } from "./sol.native.service";
import prisma from "../../client";
import { convertCoinAmountFromInt, createUniqueCode } from "../../utils/helper";
import { CustomError } from "../../utils/errors";
import {
    DepositFormBlock,
    SolanaBalanceFinderArgs,
    SolanaCheckDepositProcessor,
    SolanaPendingDepositCallable,
    SolanTransactionProcessorArgs,
} from "../../types/solana.types";
import { DepositCollectionStatus } from "../../enums/DepositCollectionStatus";

const getConnection = async (rpc: string): Promise<Connection> => {
    return new Connection(rpc);
};

const getAccountInfo = async (connection: Connection, publicKey: string): Promise<AccountInfo<Buffer> | null> => {
    return await connection.getAccountInfo(new PublicKey(publicKey));
};

const isAddressValid = (address: string): boolean => {
    try {
        const publicKey = new PublicKey(address);
        return true;
    } catch (error) {
        return false;
    }
};

const checkIfAccountExists = async (connection: Connection, publicKey: string): Promise<boolean> => {
    return (await getAccountInfo(connection, publicKey)) ? true : false;
};

const createSolanaAddress = async (rpc?: string) => {
    try {
        let keyPair = Keypair.generate();
        console.log(keyPair);
        if (keyPair) {
            let address = keyPair.publicKey.toBase58();
            let secretKey = bs58.encode(keyPair.secretKey);
            const data = {
                address: address,
                pk: secretKey,
            };
            return generateSuccessResponse("Wallet created successfully", data);
        }
        return generateErrorResponse("Wallet not generated");
    } catch (err) {
        console.log("createSolAddress service", err);
        return generateErrorResponse(err.message || "Something went wrong");
    }
};

const getEstimatedFee = async (connection: Connection, message: VersionedMessage): Promise<number> => {
    const fee = await connection.getFeeForMessage(message);

    return (fee.value || 0) / LAMPORTS_PER_SOL;
};

const getFee = async (
    connection: Connection,
    message: VersionedMessage,
    toAddress: string,
    mintAddress: string | null = null
): Promise<number> => {
    let fee = Number(await getEstimatedFee(connection, message));

    if (mintAddress && !(await tokenAccountExists(connection, toAddress, mintAddress))) {
        const rentExemptBalance = (await getMinimumBalanceForRentExemptAccount(connection)) / LAMPORTS_PER_SOL;
        fee += Number(rentExemptBalance);
    }

    return fee;
};

const validateBalance = (senderBalance: number, amount: number): boolean => {
    return senderBalance >= amount;
};

const getSolBalance = async (data: SolanaBalanceFinderArgs): Promise<number> => {
    const { connection, publicKey, coinDecimal } = data;
    return Number(
        convertCoinAmountFromInt(
            await connection.getBalance(new PublicKey(publicKey)),
            coinDecimal ? coinDecimal : Math.log10(LAMPORTS_PER_SOL)
        )
    );
};

const getSolanaTokenBalance = async (data: SolanaBalanceFinderArgs): Promise<number> => {
    const { connection, publicKey, coinDecimal, mintAddress } = data;

    const tokenAddress = await getAssociativeAccount(publicKey, mintAddress);
    const accountInfo = await connection.getAccountInfo(tokenAddress);

    if (!accountInfo) {
        throw new CustomError(`Sender token account does not exist`);
    }

    const balance = await connection.getTokenAccountBalance(tokenAddress);

    return Number(
        convertCoinAmountFromInt(
            Number(balance.value.amount) || 0,
            coinDecimal ? coinDecimal : Math.log10(LAMPORTS_PER_SOL)
        )
    );
};

const executeTransaction = async (connection: Connection, message: VersionedMessage, payerPrivateKey: string) => {
    const payer = Keypair.fromSecretKey(bs58.decode(payerPrivateKey));
    const versionedTransaction = new VersionedTransaction(message);
    versionedTransaction.sign([payer]);

    const signature = await connection.sendTransaction(versionedTransaction);

    return signature;
};

const getPublicKey = async (privateKey: string) => {
    const keypair = Keypair.fromSecretKey(bs58.decode(privateKey));
    const publicKey = keypair.publicKey;

    return publicKey.toBase58();
};

const getAssociativeAccount = async (
    address: string,
    mintAddress: string,
    allowOffCurveAccount: boolean = false
): Promise<PublicKey | null> => {
    return await getAssociatedTokenAddress(new PublicKey(mintAddress), new PublicKey(address), allowOffCurveAccount);
};

const getSolanaTransferFromTransactionHash = async (
    rpcUrl: string,
    coinId: number,
    networkId: number,
    txHash: string,
    transactionProcessor: SolanaCheckDepositProcessor,
    decimal: number | null = null,
    mintAddress: string | null = null
): Promise<DepositFormBlock> => {
    const connection = await getConnection(rpcUrl);

    try {
        const transaction = await connection.getParsedTransaction(txHash, {
            commitment: "finalized",
            maxSupportedTransactionVersion: 0,
        });

        const result = await transactionProcessor({
            transaction: transaction,
            coinDecimal: decimal,
            mintAddress: mintAddress,
        } as SolanTransactionProcessorArgs);
        console.log(result);

        if (result == null) {
            throw new CustomError("This Transaction ID does not belong to the selected Coin/Token");
        }

        const transactionFromBlock = await filterTransactions(coinId, networkId, result, mintAddress);
        if (!transactionFromBlock) {
            throw new CustomError("This Transaction ID does not belong to the selected Coin/Token");
        }

        return transactionFromBlock;
    } catch (error: any) {
        if (error.message.includes("Invalid param")) {
            console.error(error.stack);
            throw new CustomError("This Transaction ID does not belong to the selected Coin/Token.");
        }

        throw error;
    }
};

const filterTransactions = async (
    coinId: number,
    networkId: number,
    transactions: DepositFormBlock[],
    mintAddress: string | null = null
): Promise<DepositFormBlock | null> => {
    for (const transaction of transactions) {
        const walletId = await getWalletId(transaction.to_address, coinId, networkId);

        if (!walletId) {
            continue;
        }

        if (mintAddress && transaction.contract_address !== mintAddress) {
            continue;
        }

        return transaction;
    }

    return null;
};

const getWalletId = async (address: string, coinId: number, networkId: Number): Promise<number | null> => {
    const walletId = await prisma.wallet_address_histories.findFirst({
        select: {
            wallet_id: true,
        },
        where: {
            coin_id: Number(coinId),
            network_id: Number(networkId),
            address: address,
        },
    });

    if (!walletId) {
        return null;
    }

    return Number(walletId.wallet_id);
};

const getTransactionProcessor = (mintAddress: string | null = null): SolanaCheckDepositProcessor => {
    if (mintAddress) {
        return processTokenTrasction;
    }

    return processNativeTransaction;
};

const sleep = (ms) => {
    return new Promise((resolve) => setTimeout(resolve, ms));
};

const sendToSolanaSystemWallet = async (
    callables: SolanaPendingDepositCallable,
    rpc: string,
    userAddress: string,
    systemWalletAddress: string,
    amount: number,
    decimal: number,
    nativeCoinDecimal: number | null,
    privateKey: string,
    systemWalletPrivateKey: string,
    networkData,
    mintAddress: string = null,
    walletId: number,
    coinType: string
): Promise<ResponseData> => {
    const connection = await getConnection(rpc);

    const balance = await callables.balanceCalculator({
        connection: connection,
        publicKey: userAddress,
        coinDecimal: decimal,
        mintAddress: mintAddress,
    } as SolanaBalanceFinderArgs);

    console.log(balance);

    if (Number(balance) < Number(amount)) {
        throw new CustomError(
            `User has insufficient ${coinType}. Current balance: ${balance}. Trying to send amount ${amount}`
        );
    }

    const message = await callables.messageBulder({
        fromAddress: userAddress,
        destinationAddress: systemWalletAddress,
        amount: amount,
        decimal: decimal,
        connection: connection,
        mintAddress: mintAddress,
        privateKey: privateKey,
    });

    let fee = 0;

    if (!mintAddress) {
        fee += Number(amount);
    }

    const estimatedFee = await getFee(connection, message, systemWalletAddress, mintAddress);

    console.log(estimatedFee, fee, Number(estimatedFee), Number(fee));

    fee = fee + Number(estimatedFee);

    console.log("fee", fee);
    const solBalance = await getSolBalance({
        connection: connection,
        publicKey: userAddress,
        coinDecimal: nativeCoinDecimal,
    });

    const neededSolBalance = Number(fee) + Number(SOLANA_MIN_ACCOUNT_RENT_IN_SOL);

    let gasTransactionId = null;

    if (Number(solBalance) < neededSolBalance) {
        try {
            gasTransactionId = await sendSolanaNativeCoin(rpc, userAddress, Number(neededSolBalance) - Number(solBalance), nativeCoinDecimal ? nativeCoinDecimal : 9, systemWalletPrivateKey);
        } catch (error: any) {
            if (error.name == "CustomError" && error.message && error.message.includes("Balance is = ")) {
                throw new CustomError("System wallet has insufficient SOL for fee. " + error.message);
            }

            throw error;
        }

        await addGasData(
            networkData,
            Number(neededSolBalance) - Number(solBalance),
            systemWalletAddress,
            gasTransactionId.data.transaction_id,
            walletId
        );

        await sleep(20000);
    }

    let transactionId = "";

    try {
        transactionId = await executeTransaction(connection, message, privateKey);
    } catch (err) {
        if (
            err.message.includes("Attempt to debit an account but found no record of a prior credit") &&
            gasTransactionId
        ) {
            throw new CustomError(
                "Gas sent to user wallet. Transaction id " +
                    gasTransactionId.data.transaction_id +
                    ". It is taking some time to take effect. Please try again after a while"
            );
        }

        throw err;
    }

    await updateTransaction(networkData, systemWalletAddress, userAddress, transactionId);

    return generateSuccessResponse("Coins received successfully");
};

const addGasData = async (
    networkData,
    amount: number,
    systemWalletAddress: string,
    transactionId: string,
    walletId: number
) => {
    await prisma.estimate_gas_fees_transaction_histories.create({
        data: {
            unique_code: createUniqueCode(),
            wallet_id: Number(walletId),
            deposit_id: Number(networkData.transaction_id),
            amount: amount,
            coin_type: networkData.coin_type,
            admin_address: systemWalletAddress,
            user_address: networkData.from_address,
            transaction_hash: transactionId,
            status: STATUS_ACTIVE,
            type: 1,
        },
    });
};

const updateTransaction = async (
    networkData,
    systemWalletAddress: string,
    userWalletAddress: string,
    transactionHash: string
) => {
    const transaction = await prisma.deposite_transactions.update({
        where: { id: networkData.transaction_id },
        data: { status: STATUS_ACTIVE, is_admin_receive: DepositCollectionStatus.SUCCESS, reject_note: null },
    });

    const adminTokenReceive = await prisma.admin_receive_token_transaction_histories.create({
        data: {
            unique_code: createUniqueCode(),
            amount: networkData.amount,
            deposit_id: Number(networkData.transaction_id),
            fees: "0",
            to_address: systemWalletAddress,
            from_address: userWalletAddress,
            transaction_hash: transactionHash,
            status: STATUS_ACTIVE,
            type: 1,
        },
    });
};

const getPendingDepositCallables = (mintAddress: string = null): SolanaPendingDepositCallable => {
    if (mintAddress) {
        return {
            messageBulder: getSolanaTokenSendingMessage,
            balanceCalculator: getSolanaTokenBalance,
        };
    }

    return {
        messageBulder: getNativeCoinSendingMessage,
        balanceCalculator: getSolBalance,
    };
};

export const validateSolToken = async (mintAddress: string, rpcUrl: string): Promise<ResponseData> => {
    if (mintAddress.toLowerCase() === NATIVE_MINT.toBase58().toLowerCase()) {
        return generateErrorResponse("Wrapped SOL is not supported");
    }

    const connection = new Connection(rpcUrl);

    if (!isAddressValid(mintAddress)) {
        return generateErrorResponse("Not a valid SPL token");
    }

    const accountInfo = await getAccountInfo(connection, mintAddress);

    if (!accountInfo || accountInfo.owner.toBase58().toLowerCase() != TOKEN_PROGRAM_ID.toBase58().toLowerCase()) {
        return generateErrorResponse("Not a valid SPL token");
    }

    return generateSuccessResponse("Valid SPL token");
};

export {
    createSolanaAddress,
    getConnection,
    getAccountInfo,
    checkIfAccountExists,
    getEstimatedFee,
    getAssociativeAccount,
    validateBalance,
    getSolBalance,
    executeTransaction,
    getPublicKey,
    isAddressValid,
    getSolanaTokenBalance,
    getFee,
    getSolanaTransferFromTransactionHash,
    getTransactionProcessor,
    getPendingDepositCallables,
    sendToSolanaSystemWallet,
};
