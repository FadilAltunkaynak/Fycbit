<?php

namespace Database\Seeders;
use App\Model\SupportedNetwork;
use Illuminate\Database\Seeder;

class SupportedNetworkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SupportedNetwork::firstOrCreate(['slug' => 'coin_payment'], [
            'type' => COIN_PAYMENT,
            'name' => 'Coin Payment Api',
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'bitgo_api'], [
            'type' => BITGO_API,
            'name' => 'Bitgo Api',
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'bitcoin_api'], [
            'type' => BITCOIN_API,
            'name' => 'Bitcoin Fork',
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'eth_goerli'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Goerli',
            'network_type' => NETWORK_TESTNET,
            'chain_id' => 5,
            'native_currency' => 'ETH',
            'gas_limit' => '60000',
            'base_url' => 'https://goerli.etherscan.io',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'eth_sepolia'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Sepolia',
            'network_type' => NETWORK_TESTNET,
            'chain_id' => 11155111,
            'native_currency' => 'ETH',
            'gas_limit' => '60000',
            'base_url' => 'https://sepolia.etherscan.io',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'bnbtestnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Binance Testnet',
            'network_type' => NETWORK_TESTNET,
            'chain_id' => 97,
            'native_currency' => 'BNB',
            'gas_limit' => '60000',
            'base_url' => 'https://testnet.bscscan.com',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'polygon_amoy'],[
            'type' => EVM_BASE_COIN,
            'name' => 'Amoy',
            'network_type' => NETWORK_TESTNET,
            'chain_id' => 80002,
            'native_currency' => 'POL',
            'gas_limit' => '60000',
            'base_url' => 'https://amoy.polygonscan.com',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'ethereum'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Ethereum Mainnet',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 1,
            'native_currency' => 'ETH',
            'gas_limit' => '60000',
            'base_url' => 'https://etherscan.io',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'polygon'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Polygon Mainnet',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 137,
            'native_currency' => 'POL',
            'gas_limit' => '60000',
            'base_url' => 'https://polygonscan.com',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'bnbmainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'BNB Smart Chain Mainnet',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 56,
            'native_currency' => 'BNB',
            'gas_limit' => '60000',
            'base_url' => 'https://bscscan.com',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'tronmainnet'], [
            'type' => TRC20_TOKEN,
            'name' => 'Tron Mainnet',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 1,
            'native_currency' => 'TRX',
            'base_url' => 'https://tronscan.org',
            'token_endpoint' => '/#/token20',
            'address_endpoint' => '/#/address',
            'tx_endpoint' => '/#/transaction'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'tronniletestnet'], [
            'type' => TRC20_TOKEN,
            'name' => 'Tron Nile Testnet',
            'network_type' => NETWORK_TESTNET,
            'chain_id' => 3,
            'native_currency' => 'TRX',
            'base_url' => 'https://nile.tronscan.org',
            'token_endpoint' => '/#/token20',
            'address_endpoint' => '/#/address',
            'tx_endpoint' => '/#/transaction'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'tronshastatestnet'], [
            'type' => TRC20_TOKEN,
            'name' => 'Tron Shasta Testnet',
            'network_type' => NETWORK_TESTNET,
            'chain_id' => 3,
            'native_currency' => 'TRX',
            'base_url' => 'https://shasta.tronscan.org',
            'token_endpoint' => '/#/token20',
            'address_endpoint' => '/#/address',
            'tx_endpoint' => '/#/transaction'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'bchmainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Smart Bitcoin Cash',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 10000,
            'native_currency' => 'BCH',
            'base_url' => 'https://smartbch.org',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'dogemainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Dogechain Mainnet',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 2000,
            'native_currency' => 'DOGE',
            'base_url' => 'https://explorer.dogechain.dog',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);

        SupportedNetwork::firstOrCreate(['slug' => 'dogetestnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Dogechain Testnet',
            'network_type' => NETWORK_TESTNET,
            'chain_id' => 568,
            'native_currency' => 'DOGE',
            'base_url' => 'https://explorer-testnet.dogechain.dog',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'etcmainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Ethereum Classic Mainnet',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 61,
            'native_currency' => 'ETC',
            'base_url' => 'https://blockscout.com/etc/mainnet',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'shibamainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'ShibaChain',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 27,
            'native_currency' => 'SHIB',
            'base_url' => 'https://exp.shibchain.org',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'altmainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Alphabet Mainnet',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 111222333444,
            'native_currency' => 'ALT',
            'base_url' => 'https://scan.alphabetnetwork.org',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'altmainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Avalanche C-Chain',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 43114,
            'native_currency' => 'AVAX',
            'base_url' => 'https://snowtrace.io',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => 'cronosmainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Cronos Chain',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 25,
            'native_currency' => 'CRO',
            'base_url' => 'https://explorer.cronos.org',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);
        SupportedNetwork::firstOrCreate(['slug' => '7moonmainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => '7Moon Chain Mainnet',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 2066,
            'native_currency' => '7MOON',
            'base_url' => 'https://7moonchain.com',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);

        SupportedNetwork::firstOrCreate(['slug' => '7moontestnet'], [
            'type' => EVM_BASE_COIN,
            'name' => '7Moon Chain Testnet',
            'network_type' => NETWORK_TESTNET,
            'chain_id' => 54799,
            'native_currency' => '7MOON',
            'base_url' => 'https://testnet-explorer.7moonchain.com',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);

        SupportedNetwork::firstOrCreate(['slug' => 'phoenix'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Phoenix',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 13381,
            'native_currency' => 'PHX',
            'base_url' => 'https://phoenixplorer.com',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);

        SupportedNetwork::firstOrCreate(['slug' => 'zebro'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Zebro Smart Chain (ZSC)',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 786786,
            'native_currency' => 'ZEBRO',
            'base_url' => 'https://explorer.zebrocoin.app',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);

        SupportedNetwork::firstOrCreate(['slug' => 'flare'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Flare',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 14,
            'native_currency' => 'FLR',
            'base_url' => 'https://flare-explorer.flare.network',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);

        SupportedNetwork::firstOrCreate(['slug' => 'base_mainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Base Mainnet',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 8453,
            'native_currency' => 'ETH',
            'base_url' => 'https://explorer.base.org',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);

        SupportedNetwork::firstOrCreate(['slug' => 'arbitrum_mainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Arbitrum One Mainnet',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 42161,
            'native_currency' => 'ETH',
            'base_url' => 'https://arbiscan.io',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);

        SupportedNetwork::firstOrCreate(['slug' => 'avax_mainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Avalanche Network',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 43114,
            'native_currency' => 'AVAX',
            'base_url' => 'https://snowtrace.io/',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);

        SupportedNetwork::firstOrCreate(['slug' => 'op_mainnet'], [
            'type' => EVM_BASE_COIN,
            'name' => 'Optimism Mainnet',
            'network_type' => NETWORK_MAINNET,
            'chain_id' => 10,
            'native_currency' => 'ETH',
            'base_url' => 'https://optimistic.etherscan.io/',
            'token_endpoint' => '/token',
            'address_endpoint' => '/address',
            'tx_endpoint' => '/tx'
        ]);

        /* solna_mainnet typo refactor */
        $solanaMainnetNetwork = SupportedNetwork::whereIn('slug', ['solna_mainnet'])->get();
        if ($solanaMainnetNetwork->count() == 0) {
            SupportedNetwork::firstOrCreate(['slug' => 'solana_mainnet'], [
                'type' => SOLANA_BASE_COIN,
                'name' => 'Solana Mainnet',
                'network_type' => NETWORK_MAINNET,
                'chain_id' => 0,
                'native_currency' => 'SOL',
                'base_url' => 'https://solscan.io/',
                'token_endpoint' => '/token',
                'address_endpoint' => '/account',
                'tx_endpoint' => '/tx'
            ]);
        } else {
            SupportedNetwork::where(['slug' => 'solna_mainnet'])->update(['slug' => 'solana_mainnet']);
        }
        /*  */

        SupportedNetwork::firstOrCreate(['slug' => 'solana_devnet'], [
            'type' => SOLANA_BASE_COIN,
            'name' => 'Solana Devnet',
            'network_type' => NETWORK_TESTNET,
            'chain_id' => 0,
            'native_currency' => 'SOL',
            'base_url' => 'https://solscan.io/',
            'token_endpoint' => '/token',
            'address_endpoint' => '/account',
            'tx_endpoint' => '/tx'
        ]);
    }
}
