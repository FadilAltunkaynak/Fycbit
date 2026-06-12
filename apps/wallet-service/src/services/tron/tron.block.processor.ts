import { TronBlock, TronBlockTx } from "../../types/tron.types";
import { getCoinDecimalFromContractAddress, getDecimalByContractAddress, initializeTronWeb } from "../evm/trx.token.service";
import { getTrxCurrentBlockNumberByRpcUrl } from "../evm/trx.tron-web.service";
import { checkAndProcessTronDeposit, checkIfTokenTransfer, processTronTxDetails } from "./tron.auto.deposit.service";
import { getContractAddressMaps } from "../coin/contract.address.map.service";
import { CurrentBlockFetcherInterface, MultiblockFetchableProcessor } from "../../block_processor/block.processor.interface";
import { NetworkData } from "../../types/db.types";

export class TronBlockProcessor implements MultiblockFetchableProcessor<TronBlock> {

    async getBlocksData(
        networkData: NetworkData,
        fromBlock: number,
        toBlock: number
    ): Promise<TronBlock[]> {

        let blockList: TronBlock[] = [];

        const tronWeb = await initializeTronWeb(networkData.rpc_url);

        if (fromBlock == toBlock) {
            blockList = await tronWeb.trx.getBlockRange(fromBlock - 1, toBlock);
            return [blockList[1]];
        }
        
        blockList = await tronWeb.trx.getBlockRange(fromBlock, toBlock);
        return blockList;
    }

    async processSingleBlock(
        networkData: NetworkData,
        blockNumber: number,
        blockData: TronBlock,
    ): Promise<void> {
        let map = await getContractAddressMaps(Number(networkData.id));
        let decimalMap = map.decmial_map;

        for (const tx of blockData?.transactions || []) {
            // process single Tx start
            let decimal = 18;

            if (checkIfTokenTransfer(tx)) {
                const contractAddress = await this.getContractAddress(networkData.rpc_url, tx);
                if (!decimalMap.has(contractAddress.toLowerCase())) {
                    continue;
                }

                const decimalFromMap = decimalMap.get(contractAddress.toLowerCase());
                decimal = decimalFromMap;
                if (!decimalFromMap) {
                    decimal = await getCoinDecimalFromContractAddress(networkData.rpc_url, contractAddress);
                    decimalMap.set(contractAddress.toLowerCase(), decimal);
                }
            }

            const txData = await processTronTxDetails(networkData.rpc_url, tx, blockNumber, decimal);
            await checkAndProcessTronDeposit(networkData, txData);
        }

    }

    private async getContractAddress(rpcUrl: string, transaction: TronBlockTx): Promise<string> {
        const tronWeb = await initializeTronWeb(rpcUrl);
        const contractAddress = transaction.raw_data.contract[0].parameter.value.contract_address;
        return tronWeb.address.fromHex(contractAddress);
    }
}

export class TronCurrentBlockFetcher implements CurrentBlockFetcherInterface {
    async getCurrentBlock(networkData: NetworkData): Promise<number> {
        let currentBlockNumber = await getTrxCurrentBlockNumberByRpcUrl(networkData.rpc_url);
        if (!currentBlockNumber) {
            throw new Error('Current block number not found');
        }

        return currentBlockNumber;
    }
}