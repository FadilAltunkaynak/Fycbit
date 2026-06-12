import { NetworkData } from "../../types/db.types";
import { STATUS_ACTIVE } from "../../utils/coreConstant";
import { sleep } from "../../utils/helper";
import { getNetworkData, logBlockDataBeforeStart, logDataAfterSuccess, updateNotifiedBlock } from "../block.processing.utils";
import { BlockProcessorHandlerInterface, CurrentBlockFetcherInterface, SingleBlockFetchableProcessor } from "../block.processor.interface";

export class SingleBlockFetchableHandler implements BlockProcessorHandlerInterface {
    constructor(
        private blockProcessor: SingleBlockFetchableProcessor,
        private currentBlockFetcher: CurrentBlockFetcherInterface
    ) { }

    async handleBlocks(
        networkData: NetworkData,
        fromBlock: number,
        toBlock: number,
        isAutoBlockProcessing: boolean = true
    ): Promise<void> {
      
        for (let block = fromBlock; block <= toBlock; block++) {
            const blockNumber = block;
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
            
            await this.blockProcessor.processSingleBlock(networkData, blockNumber);

            const currentBlockNumber = await this.currentBlockFetcher.getCurrentBlock(networkData);

            if(isAutoBlockProcessing) {
                await updateNotifiedBlock(
                    currentBlockNumber,
                    blockNumber,
                    Number(networkData.id),
                    null
                )
            }

            logDataAfterSuccess(
                networkData,
                blockNumber,
                currentBlockNumber,
                start
            );

            await sleep(30); // 30 milisec
        }
    }
}