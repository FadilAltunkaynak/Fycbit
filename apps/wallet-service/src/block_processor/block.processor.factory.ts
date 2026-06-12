import { DefaultArgs, Types } from "@prisma/client/runtime";
import { SolanaBlockProcessor, SolanaCurrentBlockFetcher } from "../services/solana/sol.block.processor";
import { EVM_BASE_COIN, SOLANA_BASE_COIN, TRON_BASE_COIN } from "../utils/coreConstant";
import { networksPayload } from "@prisma/client";
import { EthBlockProcessor, EthCurrentBlockFetcher } from "../services/eth/eth.block.processor";
import { TronBlockProcessor, TronCurrentBlockFetcher } from "../services/tron/tron.block.processor";
import { BlockProcessorHandlerInterface, CurrentBlockFetcherInterface } from "./block.processor.interface";
import { SingleBlockFetchableHandler } from "./block_processor_hadlers/single.block.fetchable.handler";
import { NetworkData } from "../types/db.types";
import { MultiBlockFechableHandler } from "./block_processor_hadlers/multi.block.fetcheablbe.handler";
import { TronBlock } from "../types/tron.types";

const BlockProcessorMap: Map<number, BlockProcessorHandlerInterface> = new Map([
    [
        TRON_BASE_COIN, new MultiBlockFechableHandler<TronBlock>(
            new TronBlockProcessor(),
            new TronCurrentBlockFetcher(),
        ) as BlockProcessorHandlerInterface
    ],
    [
        SOLANA_BASE_COIN, new SingleBlockFetchableHandler(
            new SolanaBlockProcessor(),
            new SolanaCurrentBlockFetcher(),
        )
    ],
    [
        EVM_BASE_COIN, new SingleBlockFetchableHandler(
            new EthBlockProcessor(),
            new EthCurrentBlockFetcher(),
        )
    ]
]);

const CurrentBlockFetcherMap: Map<number, CurrentBlockFetcherInterface> = new Map([
    [SOLANA_BASE_COIN, new SolanaCurrentBlockFetcher()],
    [EVM_BASE_COIN, new EthCurrentBlockFetcher()],
    [TRON_BASE_COIN, new TronCurrentBlockFetcher()]
])

export class BlockProcessorFactory {
    getBlockProcessor(networkData: Types.GetResult<networksPayload, DefaultArgs>): BlockProcessorHandlerInterface {
        const baseType = networkData.base_type;

        if(!BlockProcessorMap.has(baseType)) {
            throw new Error(`Block processor for ${networkData.name} is not supported yet.`);
        }

        return BlockProcessorMap.get(baseType);

    }

    getCurrentBlockFetcher(networkData: NetworkData): CurrentBlockFetcherInterface {
        if (!CurrentBlockFetcherMap.has(Number(networkData.base_type))) {
            throw new Error(`Current block fetcher for ${networkData.name} is not supported yet.`);
        }

        return CurrentBlockFetcherMap.get(Number(networkData.base_type));
    }
}