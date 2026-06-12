import prisma from "../client";
import { NetworkData } from "../types/db.types";

const updateNotifiedBlock = async (
    currentBlockNumber: number,
    blockNumber: number,
    networkId: number,
    error?: string,
) => {
    await prisma.notified_blocks.updateMany({
        where: { network_id: Number(networkId) },
        data: {
            block_number: String(blockNumber),
            node_block: String(currentBlockNumber),
            updated_at: new Date().toISOString(),
            error: error || null,
        },
    });
}

// Logs blog processing error and store error message in notified_blocks table
const processBlockProcessingErrror = async (
    error: any,
    networkData: NetworkData
) => {

    let lastProcessedBlock = 0;

    try {
        const notifiedBlock = await prisma.notified_blocks.findFirst({
            where: { network_id: Number(networkData.id) },
        });
        lastProcessedBlock = Number(notifiedBlock?.block_number ?? 0);
    } catch (e) {
        console.log(error.stack);
    }

    console.log(
        `[${networkData.slug}] block processing err [lastProcessedBlock:${lastProcessedBlock}]: ${error.message ?? error.stack
        } ${error.name}`
    );

    try {
        await prisma.notified_blocks.updateMany({
            where: { network_id: Number(networkData.id) },
            data: { error: error.message ?? error.stack },
        });
    } catch (e) {
        console.log(error.stack);
    }

}

const getNetworkData = async (id: number): Promise<NetworkData | null> => {
    return await prisma.networks.findFirst({
        where: { id },
    });
}

const logDataAfterSuccess = (
    networkData: NetworkData,
    blockNumber: number,
    currentBlockNumber: number,
    start: Date
) => {
    if (process.env.BLOCK_PROCESSOR_DEBUG_LOG == 'ON') {
        // if (networkData.slug === 'solana_mainnet') {
        console.log(
            `[${networkData.slug}]: processing block ${blockNumber}, end at: ${new Date().toISOString()}`
        );
        console.log(
            `[${networkData.slug}]: processing block ${blockNumber}, duration: ${(new Date().getTime() - start.getTime()) / 1000
            } seconds`
        );
        console.log(`[${networkData.slug}]: Block difference ${currentBlockNumber - blockNumber}`);
        // }
    }

}

const logBlockDataBeforeStart = (
    networkData: NetworkData,
    blockNumber: number,
    start: Date
) => {
    if (process.env.BLOCK_PROCESSOR_DEBUG_LOG == 'ON') {
        // if (networkData.slug === 'solana_mainnet') {
        console.log(
            `[${networkData.slug}]: processing block ${blockNumber}, start at: ${start.toISOString()}`
        );
        // }
    }
    /*  */
}

export {
    updateNotifiedBlock,
    processBlockProcessingErrror,
    getNetworkData,
    logDataAfterSuccess,
    logBlockDataBeforeStart
}