import { Connection } from "@solana/web3.js";
import {
    depositTransactions, getParsedTransactions,
    getSystemWalletAddress, getTransactionDataFromBlock,
} from "./sol.auto.deposit.service";
import { getContractAddressMaps } from "../coin/contract.address.map.service";
import { NetworkData } from "../../types/db.types";
import { CurrentBlockFetcherInterface, SingleBlockFetchableProcessor } from "../../block_processor/block.processor.interface";

export class SolanaBlockProcessor implements SingleBlockFetchableProcessor {   
    async processSingleBlock(
        networkData: NetworkData,
        blockNumber: number,
    ): Promise<void> {

        const client = new Connection(networkData.rpc_url);
        const mapData = await getContractAddressMaps(Number(networkData.id));
        const contractAddressToDecimalMap = mapData.decmial_map;
        const contractAddressToCoinMap = mapData.coin_id_map;
        const systemWalletAddress = await getSystemWalletAddress(Number(networkData.id));

        const transactions = await getTransactionDataFromBlock(networkData, client, blockNumber);

        for (const transaction of transactions) {
            const parsedTransactions = await getParsedTransactions(transaction, contractAddressToDecimalMap);

            await depositTransactions(
                parsedTransactions,
                networkData,
                systemWalletAddress,
                contractAddressToCoinMap
            );

        }
    }
}

export class SolanaCurrentBlockFetcher implements CurrentBlockFetcherInterface {
    async getCurrentBlock(networkData: NetworkData): Promise<number> {
        const client = new Connection(networkData.rpc_url);
        return await client.getSlot();
    }
}