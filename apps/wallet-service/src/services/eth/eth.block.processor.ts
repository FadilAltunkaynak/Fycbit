import { getLatestWeb3BlockNumber, initializeWeb3 } from "../evm/erc20.web3.service";
import Web3 from "web3";
import { EvmTxData } from "../../types/block.processor.meta.data.type";
import { checkIfTokenTransfer, processEthNativeCoinDeposit, processEthTokenCoinDeposit } from "./eth.deposit.service";
import { getContractAddressMaps } from "../coin/contract.address.map.service";
import { decodeContractInputParameter } from "../evm/erc20.token.service";
import { NetworkData } from "../../types/db.types";
import { CurrentBlockFetcherInterface, SingleBlockFetchableProcessor } from "../../block_processor/block.processor.interface";

export class EthBlockProcessor implements SingleBlockFetchableProcessor {
    async processSingleBlock(
        networkData: NetworkData,
        blockNumber: number,
    ): Promise<void> {
        const web3 = await initializeWeb3(networkData.rpc_url);
        const decimalMap = (await getContractAddressMaps(Number(networkData.id))).decmial_map;

        // block details with transactions
        const blockData = await web3.eth.getBlock(blockNumber, true);
    
        for (const tx of blockData?.transactions || []) {

            if(!tx.to) {
                continue;
            }

            const txData: EvmTxData = {
                tx_hash: tx.hash,
                block_hash: tx.blockHash,
                from_address: tx.from,
                to_address: tx.to,
                amount: Web3.utils.fromWei(tx.value, 'ether'),
                block_number: blockData.number,
                gas: tx.gas,
                gas_price: tx.gasPrice,
                input: tx.input,
                nonce: tx.nonce,
                transactionIndex: tx.transactionIndex,
                value: tx.value,
                type: tx['type'],
                chain_id: tx['chainId'],
            };

            if(checkIfTokenTransfer(txData, decimalMap)) {
                const contractData = await decodeContractInputParameter(
                    networkData.rpc_url, 
                    txData.to_address, 
                    txData.input,
                    decimalMap.get(txData.to_address.toLowerCase())
                );
                
                if(!contractData.to_address) {
                    continue;
                }

                decimalMap.set(txData.to_address.toLowerCase(), contractData.decimal);

                await processEthTokenCoinDeposit(
                    networkData,
                    txData,
                    contractData.to_address,
                    contractData.amount
                );

                continue;
            }

            await processEthNativeCoinDeposit(networkData, txData);
        }
    }
}

export class EthCurrentBlockFetcher implements CurrentBlockFetcherInterface {
    async getCurrentBlock(networkData: NetworkData): Promise<number> {
        const currentBlockNumber = await getLatestWeb3BlockNumber(networkData.rpc_url);
                
        if (!currentBlockNumber) {
            throw new Error('Current block number not found, RPC node issue');
        }

        return currentBlockNumber;
    }
}