import {
    ComputeBudgetProgram,
    Connection,
    LAMPORTS_PER_SOL,
    ParsedInstruction,
    ParsedTransactionWithMeta,
    PartiallyDecodedInstruction,
    PublicKey,
    TransactionMessage,
    VersionedMessage,
} from "@solana/web3.js";
import { generateErrorResponse, generateSuccessResponse, ResponseData } from "../../utils/commonObject";
import { SOLANA_MIN_ACCOUNT_RENT_IN_SOL, SOLANA_TRANSFER_PRIORITY_FEE, TYPE_TOKEN } from "../../utils/coreConstant";
import {
    executeTransaction,
    getAssociativeAccount,
    getConnection,
    getEstimatedFee,
    getPublicKey,
    getSolanaTokenBalance,
    getSolBalance,
    isAddressValid,
} from "./sol.service";
import {
    createAssociatedTokenAccountInstruction,
    createTransferInstruction,
    getMinimumBalanceForRentExemptAccount,
    TOKEN_PROGRAM_ID,
} from "@solana/spl-token";
import { CustomError } from "../../utils/errors";
import { DepositFormBlock, SolanaBalanceFinderArgs, SolanaCoinTransferMessageBuilderArgs, SolanTransactionProcessorArgs } from "../../types/solana.types";
import { convertCoinAmountFromInt, convertCoinAmountToInt } from "../../utils/helper";

const sendSolanaCoin = async (
    rpc: string,
    mintAddress: string,
    toAddress: string,
    amount: number,
    decimal: number,
    nativeCoinDecimal: number,
    privateKey: string
): Promise<ResponseData> => {
    if (!isAddressValid(toAddress)) {
        throw new CustomError("Invalid recipient address");
    }

    const connection = await getConnection(rpc);
    const fromAddress = await getPublicKey(privateKey);

    const balance = await getSolanaTokenBalance({
        connection: connection,
        publicKey: fromAddress,
        coinDecimal: decimal,
        mintAddress: mintAddress,
    } as SolanaBalanceFinderArgs);

    if (Number(balance) < Number(amount)) {
        throw new CustomError(`Current balance ${balance}. Trying to send ${amount}`);
    }

    const message = await getSolanaTokenSendingMessage({
        fromAddress: fromAddress,
        destinationAddress: toAddress,
        amount: amount,
        decimal: decimal,
        connection: connection,
        mintAddress: mintAddress,
        privateKey: privateKey,
    });

    const fee = await getEstimatedFee(connection, message);
    const solBalance = await getSolBalance({
        connection: connection,
        publicKey: fromAddress,
        coinDecimal: nativeCoinDecimal,
    } as SolanaBalanceFinderArgs);

    let totalSolBalanceNeeded = Number(fee) + Number(SOLANA_MIN_ACCOUNT_RENT_IN_SOL);

    if (!(await tokenAccountExists(connection, toAddress, mintAddress))) {
        const rentExemptBalance = (await getMinimumBalanceForRentExemptAccount(connection)) / LAMPORTS_PER_SOL;
        totalSolBalanceNeeded += rentExemptBalance;
    }

    if (Number(solBalance) < totalSolBalanceNeeded) {
        throw new CustomError(
            `Insufficient SOL balance for fee. Needed: ${totalSolBalanceNeeded}, Available: ${solBalance}`
        );
    }

    const hash = await executeTransaction(connection, message, privateKey);

    if (hash) {
        return generateSuccessResponse("Transaction successful", {
            transaction_id: hash,
            used_gas: 0,
        });
    }

    return generateErrorResponse("Transaction failed");
};

const processTokenTrasction = async (data: SolanTransactionProcessorArgs): Promise<DepositFormBlock[] | null> => {
    let { transaction, coinDecimal, mintAddress } = data;

    if (transaction?.meta?.err !== null) {
        return null;
    }

    const transactionId = transaction.transaction.signatures[0];

    const map = await getTokenAddressMap(transaction);

    const instructions: (ParsedInstruction | PartiallyDecodedInstruction)[] = await getTokenTransferInstructions(
        transaction,
        map,
        mintAddress
    );

    if (!instructions) {
        return null;
    }

    return getTokenDepositsFromBlock(instructions, transaction, null, map, transactionId, mintAddress, coinDecimal);
};

const getTokenDepositsFromBlock = (
    instructions: (ParsedInstruction | PartiallyDecodedInstruction)[],
    transaction: any,
    mintToDecimalMap: Map<string, number> | null = null,
    mapFromTransaction: any,
    transactionId: string,
    mintAddress: string | null = null,
    coinDecimal: number | null = null
): DepositFormBlock[] => {
    let result: DepositFormBlock[] = [];

    instructions.forEach((instruction) => {
        let realCoinDecimal = coinDecimal;
        let finalMint = mintAddress;
        const tokenAddress = instruction["parsed"]?.info?.destination;

        if (!finalMint) {
            finalMint = mapFromTransaction[tokenAddress]?.mint;
        }

        if (!realCoinDecimal) {
            realCoinDecimal = Number(
                getCoinDecimal(finalMint, mintToDecimalMap, tokenAddress, instruction, mapFromTransaction)
            );
        }

        const fee = Number(convertCoinAmountFromInt(Number(transaction.meta?.fee || 0), realCoinDecimal));

        result.push(parseTransferInstruction(instruction, transaction, fee, transactionId, finalMint, realCoinDecimal));
    });

    return result;
};

const getCoinDecimal = (
    mintAddress: string | null = null,
    mintToDecimalMap: Map<string, number> | null = null,
    tokenAddress: string,
    instruction: ParsedInstruction | PartiallyDecodedInstruction,
    mapFromTransaction: any
): number => {
    if (mintToDecimalMap && mintAddress && mintToDecimalMap[mintAddress]) {
        return mintToDecimalMap[mintAddress];
    }

    return Number(
        instruction["parsed"]?.info?.tokenAmount?.decimals ||
            mapFromTransaction[tokenAddress]?.decimal ||
            Math.log10(LAMPORTS_PER_SOL)
    );
};

const getTokenTransferInstructions = async (
    transaction: any,
    mapFromTransaction: any,
    mintAddress: string | null = null
): Promise<(ParsedInstruction | PartiallyDecodedInstruction)[] | null> => {
    return transaction.transaction?.message?.instructions?.filter((instruction) => {
        return isTokenTransferInstruction(instruction, mapFromTransaction, mintAddress);
    });
};

const getTokenAddressMap = async (transaction: any) => {
    let map = {};

    for (const tokenBalance of transaction?.meta?.postTokenBalances || []) {
        if (!tokenBalance.mint || !tokenBalance.owner || !tokenBalance.programId) {
            continue;
        }

        const tokenAccountAddress = await getAssociativeAccount(tokenBalance.owner, tokenBalance.mint, true);

        map[tokenAccountAddress.toBase58()] = {
            mint: tokenBalance.mint,
            owner: tokenBalance.owner,
            programId: tokenBalance.programId,
            decimal: tokenBalance?.uiTokenAmount?.decimals || null,
        };
    }

    return map;
};

const parseTransferInstruction = (
    instruction: ParsedInstruction | PartiallyDecodedInstruction,
    transaction: any,
    fee: number,
    transactionId: string,
    mintAddress: string,
    coinDecimal: number = null
): DepositFormBlock => {
    const fromAddress = instruction["parsed"]?.info?.multisigAuthority || instruction["parsed"]?.info?.authority;
    const toAddress = transaction.meta?.postTokenBalances?.find((tokenData) => {
        return tokenData.mint == mintAddress && tokenData.owner != fromAddress;
    })?.owner;

    const amount = instruction["parsed"]?.info?.tokenAmount?.amount || instruction["parsed"]?.info?.amount || 0;

    return {
        from_address: fromAddress,
        to_address: toAddress,
        transaction_id: transactionId,
        fee_limit: fee,
        amount: Number(convertCoinAmountFromInt(Number(amount), coinDecimal)),
        contract_address: mintAddress,
        block_number: transaction?.slot,
        type: TYPE_TOKEN,
    } as DepositFormBlock;
};

const getMintAddressFromInstruction = async (
    instruction: ParsedInstruction | PartiallyDecodedInstruction,
    transaction: any
): Promise<string | null> => {
    for (const tokenBalance of transaction.meta?.postTokenBalances || []) {
        if (tokenBalance["programId"] !== instruction.programId.toBase58()) {
            continue;
        }

        const tokenAddress = (await getAssociativeAccount(tokenBalance.owner, tokenBalance.mint)).toBase58();

        if (instruction["parsed"]?.info?.destination === tokenAddress) {
            return tokenBalance.mint;
        }
    }
    return null;
};

const isTokenTransferInstruction = (
    instruction: ParsedInstruction | PartiallyDecodedInstruction,
    tokenAddressMap: any,
    mintAddress: string | null = null
) => {
    const tokenAddress = instruction["parsed"]?.info?.destination;
    
    if (mintAddress) {
        return (
            (instruction["parsed"]?.info?.mint === mintAddress ||
                tokenAddressMap[tokenAddress]?.mint === mintAddress) &&
            (instruction["parsed"]?.type === "transferChecked" || instruction["parsed"]?.type === "transfer") &&
                instruction["program"] === "spl-token" && 
                tokenAddressMap[tokenAddress]?.programId == instruction.programId.toBase58()
        );
    }

    return (
        (instruction["parsed"]?.type == "transferChecked" || instruction["parsed"]?.type == "transfer") &&
        instruction["program"] == "spl-token" && 
        tokenAddressMap[tokenAddress]?.programId == instruction.programId.toBase58()
    );
};

const tokenAccountExists = async (connection: Connection, toAddress: string, mintAddress: string): Promise<boolean> => {
    const toTokenAddress = await getAssociativeAccount(toAddress, mintAddress);

    const toAccountInfo = await connection.getAccountInfo(toTokenAddress);

    if (toAccountInfo) {
        return true;
    }

    return false;
};

const getSolanaTokenSendingMessage = async (data: SolanaCoinTransferMessageBuilderArgs): Promise<VersionedMessage> => {
    const { fromAddress, destinationAddress, amount, decimal, connection, mintAddress, privateKey } = data;

    const fromTokenAddress = await getAssociativeAccount(fromAddress, mintAddress);

    const fromAccountInfo = await connection.getAccountInfo(fromTokenAddress);

    if (!fromAccountInfo) {
        throw new CustomError(`Sender token account does not exist`);
    }

    const toTokenAddress = await getAssociativeAccount(destinationAddress, mintAddress);

    let instructions = [
        ComputeBudgetProgram.setComputeUnitPrice({
            microLamports: SOLANA_TRANSFER_PRIORITY_FEE,
        }),
    ];

    if (!(await tokenAccountExists(connection, destinationAddress, mintAddress))) {
        const accountInstruction = createAssociatedTokenAccountInstruction(
            new PublicKey(fromAddress),
            toTokenAddress,
            new PublicKey(destinationAddress),
            new PublicKey(mintAddress)
        );

        instructions.push(accountInstruction);
    }

    instructions.push(
        createTransferInstruction(
            fromTokenAddress,
            toTokenAddress,
            new PublicKey(fromAddress),
            Number(convertCoinAmountToInt(amount, decimal))
        )
    );

    const latestBlockhash = await connection.getLatestBlockhash("finalized");

    const message = new TransactionMessage({
        payerKey: new PublicKey(fromAddress),
        recentBlockhash: latestBlockhash.blockhash,
        instructions: instructions,
    }).compileToV0Message(); // latest
    // }).compileToLegacyMessage(); // old

    return message;
};

export {
    sendSolanaCoin,
    processTokenTrasction,
    tokenAccountExists,
    getSolanaTokenSendingMessage,
    getTokenTransferInstructions,
    getMintAddressFromInstruction,
    parseTransferInstruction,
    getTokenAddressMap,
    getTokenDepositsFromBlock,
};
