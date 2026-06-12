import { Connection, VersionedMessage } from "@solana/web3.js";

export type DepositFormBlock = {
    from_address: string;
    to_address: string;
    transaction_id: string;
    fee_limit: number;
    amount: number;
    contract_address?: string;
    block_number?: string;
    type: "native" | "token";
};

export type SolanaBalanceCalculator = (
    data: SolanaBalanceFinderArgs
) => Promise<number>

export type SolanaBalanceFinderArgs = {
    connection: Connection;
    publicKey: string;
    coinDecimal?: number,
    mintAddress?: string;
}

export type SolanaCheckDepositProcessor = (
    data: SolanTransactionProcessorArgs
) => Promise<DepositFormBlock[] | null>

export type SolanaCoinTransferMessageBuilderArgs = {
    fromAddress: string;
    destinationAddress: string;
    amount: number;
    decimal: number;
    connection: Connection;
    mintAddress?: string;
    privateKey?: string;
}

export type SolanaMessageBuilder = (
    data: SolanaCoinTransferMessageBuilderArgs
) => Promise<VersionedMessage>;

export type SolanaPendingDepositCallable = {
    messageBulder: SolanaMessageBuilder;
    balanceCalculator: SolanaBalanceCalculator;
}

export type SolanTransactionProcessorArgs = {
    transaction: any;
    coinDecimal?: number;
    mintAddress?: string;
}