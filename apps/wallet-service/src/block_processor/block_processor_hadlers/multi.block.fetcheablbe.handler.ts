import { NetworkData } from "../../types/db.types";
import { STATUS_ACTIVE } from "../../utils/coreConstant";
import { sleep } from "../../utils/helper";
import { getNetworkData, logBlockDataBeforeStart, logDataAfterSuccess, updateNotifiedBlock } from "../block.processing.utils";
import { BlockProcessorHandlerInterface, CurrentBlockFetcherInterface, MultiblockFetchableProcessor } from "../block.processor.interface";

export class MultiBlockFechableHandler<TData> implements BlockProcessorHandlerInterface {
    constructor(
        private blockProcessor: MultiblockFetchableProcessor<TData>,
        private currentBlockFetcher: CurrentBlockFetcherInterface
    ) { }

    async handleBlocks(
        networkData: NetworkData,
        fromBlock: number,
        toBlock: number,
        isAutoBlockProcessing: boolean = true
    ): Promise<void> {

        const blockData = await this.blockProcessor.getBlocksData(networkData, fromBlock, toBlock);
        let blockNumber = fromBlock;

        for (const singleBlockData of blockData) {

            networkData = await getNetworkData(Number(networkData.id))
            if (!networkData || networkData.status != STATUS_ACTIVE) {
                await sleep(30000); // 30 sec
                break;
            }

            const start = new Date();

            /* Debug Log */
            logBlockDataBeforeStart(
                networkData,
                blockNumber,
                start
            );

            await this.blockProcessor.processSingleBlock(networkData, blockNumber, singleBlockData);

            logDataAfterSuccess(
                networkData,
                blockNumber,
                await this.currentBlockFetcher.getCurrentBlock(networkData),
                start
            );

            if (isAutoBlockProcessing) {
                await updateNotifiedBlock(
                    await this.currentBlockFetcher.getCurrentBlock(networkData),
                    blockNumber,
                    Number(networkData.id),
                    null
                )
            }

            blockNumber++;
            await sleep(30); // 30 milisec
        }

        if (isAutoBlockProcessing) {
            await updateNotifiedBlock(
                await this.currentBlockFetcher.getCurrentBlock(networkData),
                toBlock,
                Number(networkData.id),
                null
            )
        }
    }
}