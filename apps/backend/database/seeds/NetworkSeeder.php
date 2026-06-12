<?php

namespace Database\Seeders;

use App\Enums\CoinProvider;
use App\Enums\NetworkBase;
use App\Model\Network;
use App\Model\NotifiedBlock;
use Illuminate\Database\Seeder;

class NetworkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // EXTERNAL_EXCHANGE
        $network = Network::firstOrCreate(['slug' => 'coin_payment'], [
            "name" => "Coin Payment",
            "provider_type" => CoinProvider::COIN_PAYMENT->value,
            "base_type" => COIN_PAYMENT,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'bitgo_api'], [
            "name" => "Bitgo",
            "provider_type" => CoinProvider::BITGO_API->value,
            "base_type" => BITGO_API,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        // CUSTOM_RPC_NODE
        $network = Network::firstOrCreate(['slug' => 'bitcoin_api'], [
            "name" => "Bitcoin",
            "base_type" => BITCOIN_API,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'eth_goerli'], [
            "name" => "Goerli Testnet",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://goerli.etherscan.io",
            'chain_id' => 5,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'eth_sepolia'], [
            "name" => "Sepolia Testnet",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://sepolia.etherscan.io",
            'chain_id' => 11155111,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'bnbtestnet'], [
            "name" => "Binance Testnet",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://testnet.bscscan.com",
            'chain_id' => 97,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'polygon_amoy'], [
            "name" => "Polygon Amoy Testnet",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://amoy.polygonscan.com",
            'chain_id' => 80002,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'ethereum'], [
            "name" => "Ethereum Mainnet",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://etherscan.io",
            'chain_id' => 1,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'polygon'], [
            "name" => "Polygon Mainnet",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://polygonscan.com",
            'chain_id' => 137,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'bnbmainnet'], [
            "name" => "Binance Mainnet",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://bscscan.com",
            'chain_id' => 56,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'tronmainnet'], [
            "name" => "Tron Mainnet",
            "base_type" => TRC20_TOKEN,
            "explorer_url" => "https://tronscan.org",
            'chain_id' => 728126428,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'tronniletestnet'], [
            "name" => "Tron Nile Testnet",
            "base_type" => TRC20_TOKEN,
            "explorer_url" => "https://nile.tronscan.org",
            'chain_id' => 201910292,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'tronshastatestnet'], [
            "name" => "Tron Shasta Testnet",
            "base_type" => TRC20_TOKEN,
            "explorer_url" => "https://shasta.tronscan.org",
            'chain_id' => 3,
            "status" => STATUS_ACTIVE,
        ]);
        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'cronosmainnet'], [
            "name" => "Cronos Mainnet",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://explorer.cronos.org",
            'chain_id' => 25,
            "status" => STATUS_ACTIVE,
        ]);

        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'phoenix'], [
            "name" => "Phoenix Mainnet",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://phoenixplorer.com",
            "rpc_url" => "https://rpc.phoenixplorer.com",
            'chain_id' => 13381,
            "status" => STATUS_ACTIVE,
        ]);

        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'zebro'], [
            "name" => "Zebro Smart Chain (ZSC)",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://explorer.zebrocoin.app",
            "rpc_url" => "https://rpc.zebrocoin.app",
            'chain_id' => 786786,
            "status" => STATUS_ACTIVE,
        ]);

        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'flare'], [
            "name" => "Flare Coin",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "	https://flare-explorer.flare.network",
            "rpc_url" => "https://flare-api.flare.network/ext/bc/C/rpc",
            'chain_id' => 14,
            "status" => STATUS_ACTIVE,
        ]);

        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);


        $network = Network::firstOrCreate(['slug' => 'base_mainnet'], [
            "name" => "Base Mainnet",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://explorer.base.org",
            "rpc_url" => "https://mainnet.base.org",
            'chain_id' => 8453,
            "status" => STATUS_ACTIVE,
        ]);

        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);


        $network = Network::firstOrCreate(['slug' => 'arbitrum_mainnet'], [
            "name" => "Arbitrum One",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://arbiscan.io",
            "rpc_url" => "https://arb1.arbitrum.io/rpc",
            'chain_id' => 42161,
            "status" => STATUS_ACTIVE,
        ]);

        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);


        $network = Network::firstOrCreate(['slug' => 'avax_mainnet'], [
            "name" => "Avalanche Network",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://snowtrace.io/",
            "rpc_url" => "https://api.avax.network/ext/bc/C/rpc",
            'chain_id' => 43114,
            "status" => STATUS_ACTIVE,
        ]);

        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        $network = Network::firstOrCreate(['slug' => 'op_mainnet'], [
            "name" => "Optimism",
            "base_type" => EVM_BASE_COIN,
            "explorer_url" => "https://optimistic.etherscan.io/",
            "rpc_url" => "https://mainnet.optimism.io",
            'chain_id' => 10,
            "status" => STATUS_ACTIVE,
        ]);

        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        /* solna_mainnet typo refactor */
        $solanaMainnetNetwork = Network::whereIn('slug', ['solna_mainnet'])->get();
        if ($solanaMainnetNetwork->count() == 0) {
            $network = Network::firstOrCreate(['slug' => 'solana_mainnet'], [
                "name" => "Solana Mainnet",
                "base_type" => SOLANA_BASE_COIN,
                "explorer_url" => "https://solscan.io/",
                "rpc_url" => "https://api.mainnet-beta.solana.com",
                'chain_id' => 0,
                "status" => STATUS_ACTIVE,
            ]);

            NotifiedBlock::firstOrCreate(['network_id' => $network->id]);
        } else {
            Network::where(['slug' => 'solna_mainnet'])->update(['slug' => 'solana_mainnet']);
        }
        /*  */

        $network = Network::firstOrCreate(['slug' => 'solana_devnet'], [
            "name" => "Solana Devnet",
            "base_type" => SOLANA_BASE_COIN,
            "explorer_url" => "https://solscan.io/",
            "rpc_url" => "https://api.devnet.solana.com",
            'chain_id' => 0,
            "status" => STATUS_ACTIVE,
        ]);

        NotifiedBlock::firstOrCreate(['network_id' => $network->id]);

        // Update if external network provider type is CUSTOM_RPC_NODE
        $externalNetworksBase = CoinProvider::externalGroup();
        $networks = Network::whereIn('base_type', $externalNetworksBase)
            ->where('provider_type', CoinProvider::CUSTOM_RPC_NODE->value)->get();

        foreach ($networks as $network)
            $network->update(['provider_type' => NetworkBase::tryFrom($network->base_type)->getProvider()]);

    }
}
