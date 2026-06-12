export interface EvmTxData {
    tx_hash?: string;
    block_hash?: string;
    from_address?: string;
    to_address: string;
    amount?: string;
    block_number?: number;
    gas?: number;
    gas_price?: string;
    input?: string;
    nonce?: number;
    transactionIndex?: number;
    value?: string;
    type?: string;
    chain_id?: string;
}