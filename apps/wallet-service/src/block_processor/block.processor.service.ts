import prisma from "../client";
import { EVM_BASE_COIN, SOLANA_BASE_COIN, TRON_BASE_COIN } from "../utils/coreConstant";
import { generateErrorResponse, generateSuccessResponse } from "../utils/commonObject";
import { BlockProcessorFactory } from "./block.processor.factory";
import { BlockProcessorProxy } from "./block.preocessor.proxy";
import { NetworkData } from "../types/db.types";


export const startAllNetworkBlockProcessor = async () => {
    try {

        const blockProcessingStatus = process.env.BLOCK_PROCESSOR_STATUS;

        if (blockProcessingStatus === 'OFF') {
            console.log('Block processor is OFF');
            return;
        }

        const networkData = await prisma.networks.findMany({});

        if (networkData && networkData.length > 0) {
            for (let x = 0; x < networkData.length; x++) {
                startSingleNetworkBlockProcessor(networkData[x]);
            }
        }

        return generateSuccessResponse('Success');
    } catch (e) {
        console.log(e.stack);
        return generateErrorResponse(e.stack);
    }
};

export async function startSingleNetworkBlockProcessor(
    networkData: NetworkData
): Promise<void> {
    try {
        if ([EVM_BASE_COIN, TRON_BASE_COIN, SOLANA_BASE_COIN].includes(networkData.base_type)) {
            const blockProcessorFactory = new BlockProcessorFactory();
            new BlockProcessorProxy(
                blockProcessorFactory.getBlockProcessor(networkData),
                blockProcessorFactory.getCurrentBlockFetcher(networkData)
            ).startProcessingBlocks(networkData);
        }
    } catch (e) {
        console.log(
            `Block processor not started for ${networkData.slug}, error: ${e.message ?? e.stack}`
        );
    }
}