import { EVM_BASE_COIN, SOLANA_BASE_COIN, STATUS_ACTIVE, TRON_BASE_COIN } from "../utils/coreConstant";
import { sleep } from "../utils/helper";
import prisma from "../client";
import { processBlockProcessingErrror } from "./block.processing.utils";
import { BlockProcessorHandlerInterface, CurrentBlockFetcherInterface } from "./block.processor.interface";
import { NetworkData } from "../types/db.types";

export class BlockProcessorProxy {
    
    private defaultBlockDifferences: Map<number, number> = new Map([
        [SOLANA_BASE_COIN, 100],
        [TRON_BASE_COIN, 25],
        [EVM_BASE_COIN, 100],
    ]);

    private blockConfirmations: Map<number, number> = new Map([
        [SOLANA_BASE_COIN, Math.max(Number(process.env.SOLANA_BLOCK_CONFIRMATION) || 1, 1)],
        [TRON_BASE_COIN, Math.max(Number(process.env.TRON_BLOCK_CONFIRMATION) || 30, 30)],
        [EVM_BASE_COIN, Math.max(Number(process.env.EVM_BLOCK_CONFIRMATION) || 1, 1)],
    ]);
    
    constructor(
        private blockProcessor: BlockProcessorHandlerInterface,
        private currentBlockFetcher: CurrentBlockFetcherInterface
    ) {}

    async startProcessingBlocks(networkData: NetworkData): Promise<void> {
        
        if(!this.shouldProcessNetwork(networkData.base_type)) {
            // console.log(`Block processing for ${networkData.slug} is not active`);
            return;
        }

        let lastProcessedBlock = 0;
        let firstCall = true;

        while (true) {
            try {
                if (!firstCall) {
                    networkData = await prisma.networks.findFirst({
                        where: { id: networkData.id },
                    });
                }

                if (firstCall) {
                    firstCall = false;
                }

                if(!networkData) {
                    return;
                }

                if (networkData.status != STATUS_ACTIVE) {
                    await sleep(30000); // 30 sec
                    continue;
                }

                const notifiedBlock = await prisma.notified_blocks.findFirst({
                    where: { network_id: Number(networkData.id) },
                });
                lastProcessedBlock = Number(notifiedBlock?.block_number ?? 0);
            } catch (e) {
                const msg = `lastProcessedBlock fetching err: ${e.message}`;
                console.log(`[${networkData.slug}]: ${msg}`);

                try {
                    await prisma.notified_blocks.updateMany({
                        where: { network_id: Number(networkData.id) },
                        data: { error: msg },
                    });
                } catch (e) {
                    console.log(e.stack);
                }

                await sleep(30000); // 30 sec
                continue;
            }

            try {
                if (!networkData.rpc_url) {
                    throw new Error('RPC url not found');
                }

                const blockDifference = this.defaultBlockDifferences.get(networkData.base_type) || 100;

                let currentBlockNumber = await this.currentBlockFetcher.getCurrentBlock(networkData);
                
                if (!currentBlockNumber) {
                    throw new Error('Current block number not found, RPC node issue');
                }

                currentBlockNumber -= this.blockConfirmations.get(Number(networkData.base_type)) || 1;

                if (lastProcessedBlock >= currentBlockNumber) {
                    await sleep(30); // 30 milisec
                    continue;
                }

                await prisma.notified_blocks.updateMany({
                    where: { network_id: Number(networkData.id) },
                    data: {
                        node_block: currentBlockNumber.toString(),
                        updated_at: new Date().toISOString(),
                    },
                });

                let fromBlockNumber = Number(networkData?.from_block_number ?? 0);
                let toBlockNumber = Number(networkData?.to_block_number ?? 0);

                // initial case
                if (toBlockNumber <= 0 && fromBlockNumber <= 0) {
                    toBlockNumber = currentBlockNumber;
                    fromBlockNumber = Math.max(toBlockNumber - blockDifference, 1);
                } else {
                    fromBlockNumber = Math.max(lastProcessedBlock + 1, fromBlockNumber);
                    toBlockNumber = Math.min(currentBlockNumber, fromBlockNumber + blockDifference);
                }
                
                if (fromBlockNumber >= toBlockNumber) {
                    await sleep(1000); // 1 sec
                    continue;
                }

                await prisma.networks.update({
                    where: { id: Number(networkData.id) },
                    data: {
                        from_block_number: fromBlockNumber,
                        to_block_number: toBlockNumber,
                    },
                });

                await this.blockProcessor.handleBlocks(
                    networkData,
                    fromBlockNumber,
                    toBlockNumber,
                    true
                );

            } catch (e) {
                await processBlockProcessingErrror(e, networkData);
                await sleep(30000); // 30 sec
                continue;
            }
        }

    }

    private shouldProcessNetwork(baseType: number): boolean {
        const networkEnv = {
            [Number(SOLANA_BASE_COIN)]: process.env.SOLANA_BLOCK_PROCESSING_STATUS
        }

        if(!networkEnv[Number(baseType)]) {
            return true;
        }

        return networkEnv[Number(baseType)] === 'ON';
    }
}