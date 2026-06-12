import { NetworkData } from "../types/db.types";

export interface SingleBlockFetchableProcessor {
    processSingleBlock(
        networkData: NetworkData,
        blockNumber: number,
    ): Promise<void>;
}

export interface MultiblockFetchableProcessor<TData> {
    getBlocksData(
        networkData: NetworkData, 
        fromBlock: number, 
        toBlock: number
    ): Promise<TData[]>
    processSingleBlock(
        networkData: NetworkData, 
        blockNumber: number, 
        data: TData, 
    ): Promise<void>;
}

export interface BlockProcessorHandlerInterface {
    handleBlocks(
        networkData: NetworkData, 
        fromBlock: number, 
        toBlock: number,
        isAutoBlockProcessing: boolean,  
    ): Promise<void>;
}
export interface CurrentBlockFetcherInterface {
    getCurrentBlock(networkData: NetworkData): Promise<number>;
}