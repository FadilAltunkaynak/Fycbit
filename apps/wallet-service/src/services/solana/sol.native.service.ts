import { ComputeBudgetProgram, LAMPORTS_PER_SOL, ParsedInstruction, ParsedTransactionWithMeta, PartiallyDecodedInstruction, PublicKey, SystemProgram, TransactionMessage, VersionedMessage } from "@solana/web3.js";
import { generateErrorResponse, generateSuccessResponse, ResponseData } from "../../utils/commonObject";
import { SOLANA_MIN_ACCOUNT_RENT_IN_SOL, SOLANA_TRANSFER_PRIORITY_FEE, TYPE_NATIVE } from "../../utils/coreConstant";
import { executeTransaction, getConnection, getEstimatedFee, getPublicKey, getSolBalance, isAddressValid, validateBalance } from "./sol.service"
import { CustomError } from "../../utils/errors";
import { DepositFormBlock, SolanaBalanceFinderArgs, SolanaCoinTransferMessageBuilderArgs, SolanTransactionProcessorArgs } from "../../types/solana.types";
import { addNumbers, convertCoinAmountFromInt, convertCoinAmountToInt, minusNumbers } from "../../utils/helper";

const sendSolanaNativeCoin = async (
    rpc: string,
    toAddress: string,
    amount: number,
    decimal: number,
    privateKey: string
): Promise<ResponseData> => {

    if (!isAddressValid(toAddress)) {
        throw new CustomError("Invalid recipient address. Cannot send SOL");
    }

    const connection = await getConnection(rpc)
    const fromAddress = await getPublicKey(privateKey);
    const balance = await getSolBalance({
        connection: connection,
        publicKey: fromAddress,
        coinDecimal: decimal
    } as SolanaBalanceFinderArgs);

    const recipientBalance = await getSolBalance({
        connection: connection,
        publicKey: toAddress,
        coinDecimal: decimal
    });

    let amountToSend = amount;

    if ((Number(recipientBalance) + Number(amount)) < Number(SOLANA_MIN_ACCOUNT_RENT_IN_SOL)) {
        const minAmount = minusNumbers(SOLANA_MIN_ACCOUNT_RENT_IN_SOL, recipientBalance); 
        throw new CustomError(`Need to send min ${minAmount} SOL to this account`);
    }

    if (Number(amountToSend) > Number(balance)) {
        throw new CustomError("Balance is = " + balance + " Trying to send " + amountToSend + " sol");
    }

    const message = await getNativeCoinSendingMessage({
        fromAddress: fromAddress,
        destinationAddress: toAddress,
        amount: amountToSend,
        decimal: decimal,
        connection: connection,
    } as SolanaCoinTransferMessageBuilderArgs);

    const estimatedFee = await getEstimatedFee(connection, message);

    const neededBalance = Number(amount) + Number(estimatedFee) + Number(SOLANA_MIN_ACCOUNT_RENT_IN_SOL);

    if (!validateBalance(Number(balance), neededBalance)) {
        throw new CustomError("balance is = " + balance + ". Need to have at least " + neededBalance + " sol including fee");
    }

    const hash = await executeTransaction(
        connection,
        message,
        privateKey
    );

    if (hash) {
        return generateSuccessResponse('SOL transaction successful', {
            transaction_id: hash,
            used_gas: 0
        });
    }

    return generateErrorResponse('SOL Transaction failed');
}

const processNativeTransaction = async (
    data: SolanTransactionProcessorArgs
): Promise<DepositFormBlock[] | null> => {

    const { transaction, coinDecimal } = data;

    if(transaction?.meta?.err !== null) {
        return null;
    }

    const transactionId = transaction.transaction.signatures[0];
    const instructions: (ParsedInstruction | PartiallyDecodedInstruction)[] = transaction.transaction?.message?.instructions?.filter(
        (instruction) => {
            return instruction.programId.toBase58() == SystemProgram.programId.toBase58() &&
                instruction['parsed']?.type == 'transfer';
        }
    );

    if (!instructions) {
        return null;
    }

    const fee = Number(convertCoinAmountFromInt(
        Number(transaction.meta?.fee || 0),
        coinDecimal ? coinDecimal : Math.log10(LAMPORTS_PER_SOL)
    ));

    let result: DepositFormBlock[] = [];

    instructions.forEach((instruction) => {
        result.push(parseTransferInstruction(
            instruction,
            fee,
            transactionId,
            coinDecimal
        ))
    });

    return result;

}

const parseTransferInstruction = (
    instruction: ParsedInstruction | PartiallyDecodedInstruction,
    fee: number,
    transactionId: string,
    coinDecimal: number = null
): DepositFormBlock => {

    let transfersFromBlock: DepositFormBlock = {
        from_address: instruction['parsed']?.info.source,
        to_address: instruction['parsed']?.info?.destination,
        transaction_id: transactionId,
        fee_limit: fee,
        amount: Number(convertCoinAmountFromInt(
            instruction['parsed']?.info?.lamports || 0,
            coinDecimal ? coinDecimal : Math.log10(LAMPORTS_PER_SOL)
        )),
        type: TYPE_NATIVE
    } as DepositFormBlock;

    return transfersFromBlock;
}

const getNativeCoinSendingMessage = async (
    data: SolanaCoinTransferMessageBuilderArgs
): Promise<VersionedMessage> => {

    const { fromAddress, destinationAddress, amount, decimal, connection } = data;

    const latestBlockhash = await connection.getLatestBlockhash('finalized');
    const priorityFee = ComputeBudgetProgram.setComputeUnitPrice({
        microLamports: SOLANA_TRANSFER_PRIORITY_FEE,
    });
    
    const transferIx = SystemProgram.transfer({
        fromPubkey: new PublicKey(fromAddress),
        toPubkey: new PublicKey(destinationAddress),
        lamports: Number(convertCoinAmountToInt(amount, decimal)),
    });

    return new TransactionMessage({
        payerKey: new PublicKey(fromAddress),
        recentBlockhash: latestBlockhash.blockhash,
        instructions: [priorityFee, transferIx],
    }).compileToV0Message();
}


export {
    getNativeCoinSendingMessage,
    processNativeTransaction,
    sendSolanaNativeCoin
}