export interface TronBlock {
    blockID: string;
    block_header: {
        raw_data: {
            number: number;
            txTrieRoot: string;
            witness_address: string;
            parentHash: string;
            version: number;
            timestamp: number;
        },
        witness_signature: string;
    },
    transactions: TronBlockTx[];
};

export interface TronBlockTx {
    ret: { contractRet: string }[];
    signature: string[];
    txID: string;
    raw_data: {
        data?: string;
        contract: {
            parameter: {
                value: {
                    amount: number;
                    owner_address: string;
                    to_address: string;
                    data?: string;
                    contract_address?: string;
                },
                type_url: string;
            },
            type: string; //TransferContract, TriggerSmartContract, others..
        }[],
        ref_block_bytes: string;
        ref_block_hash: string;
        expiration: number;
        timestamp: number;
        fee_limit?: number;
    },
    raw_data_hex: string;
};

export interface TronTxData {
    tx_type?: string;
    from_address?: string;
    to_address?: string;
    amount?: number;
    block_number?: number;
    transaction_id?: string;
    contract_address?: string;
    fee_limit?: number;
}
