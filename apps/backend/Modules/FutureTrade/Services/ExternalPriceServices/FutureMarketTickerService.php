<?php

namespace Modules\FutureTrade\Services\ExternalPriceServices;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FutureMarketTickerService
{
    /**
     * @param string $symbols Examples: BTCUSDT, BTC_USDT, BTC, BTC,ETH
     * @param string|null $providerFlag Preferred provider.
     * @param bool $alwaysArray true = always list response, false = single item for single input
     *
     * @return array<string,mixed>|array<int,array<string,mixed>>
     */
    public function getPrice(string $symbols, ?string $providerFlag = null, bool $alwaysArray = false): array
    {
        $inputs = $this->splitSymbols($symbols);
        $responses = [];

        foreach ($inputs as $input) {
            $responses[] = $this->resolveOne($input, $providerFlag);
        }

        if (! $alwaysArray && count($responses) === 1) {
            return $responses[0];
        }

        return $responses;
    }

    /**
     * Backward-compatible alias.
     *
     * @return array<string,mixed>|array<int,array<string,mixed>>
     */
    public function method(string $symbols, ?string $providerFlag = null, bool $alwaysArray = false): array
    {
        return $this->getPrice($symbols, $providerFlag, $alwaysArray);
    }

    /**
     * @return array<int,string>
     */
    private function splitSymbols(string $symbols): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $symbols))));
    }

    /**
     * @return array{base:string,quote:string,input:string,pair:string}
     */
    private function normalizeSymbol(string $input): array
    {
        $clean = strtoupper(str_replace([' ', '-'], '', trim($input)));

        if (str_contains($clean, '_')) {
            [$base, $quote] = array_pad(explode('_', $clean, 2), 2, 'USDT');

            return [
                'base' => $base,
                'quote' => $quote,
                'input' => $input,
                'pair' => $base . '_' . $quote,
            ];
        }

        foreach (['USDT', 'USDC', 'USD', 'BTC', 'ETH', 'EUR'] as $quote) {
            if (Str::endsWith($clean, $quote) && strlen($clean) > strlen($quote)) {
                $base = substr($clean, 0, -strlen($quote));

                return [
                    'base' => $base,
                    'quote' => $quote,
                    'input' => $input,
                    'pair' => $base . '_' . $quote,
                ];
            }
        }

        return [
            'base' => $clean,
            'quote' => 'USDT',
            'input' => $input,
            'pair' => $clean . '_USDT',
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function resolveOne(string $input, ?string $providerFlag): array
    {
        $normalized = $this->normalizeSymbol($input);
        $providers = $this->buildProviderOrder($providerFlag);

        foreach ($providers as $provider) {
            try {
                $price = $this->getPriceByProvider($normalized['base'], $normalized['quote'], $provider);
            } catch (\Throwable $e) {
                $price = null;
            }

            if ($price !== null && $price > 0) {
                return [
                    'input' => $normalized['input'],
                    'symbol' => $normalized['pair'],
                    'provider' => $provider,
                    'price' => $price,
                ];
            }
        }

        return [
            'input' => $normalized['input'],
            'symbol' => $normalized['pair'],
            'provider' => 'all_failed',
            'price' => 0,
        ];
    }

    /**
     * @return array<int,string>
     */
    private function buildProviderOrder(?string $firstProvider): array
    {
        $providers = ['binance_futures', 'bybit_futures', 'okx_futures', 'gateio_futures'];
        shuffle($providers);

        if ($firstProvider === null || ! in_array($firstProvider, $providers, true)) {
            return $providers;
        }

        $providers = array_values(array_filter(
            $providers,
            fn (string $provider): bool => $provider !== $firstProvider
        ));
        array_unshift($providers, $firstProvider);

        return $providers;
    }

    private function getPriceByProvider(string $base, string $quote, string $provider): ?float
    {
        return match ($provider) {
            'binance_futures' => $this->fromBinanceFutures($base, $quote),
            'bybit_futures' => $this->fromBybitFutures($base, $quote),
            'okx_futures' => $this->fromOkxFutures($base, $quote),
            'gateio_futures' => $this->fromGateIoFutures($base, $quote),
            default => null,
        };
    }

    private function fromBinanceFutures(string $base, string $quote): ?float
    {
        try {
            $symbol = strtoupper($base . $quote);
            $response = Http::timeout(6)
                ->acceptJson()
                ->get('https://fapi.binance.com/fapi/v1/ticker/price', ['symbol' => $symbol]);

            if (! $response->ok()) {
                return null;
            }

            return (float) ($response->json('price') ?? 0);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fromBybitFutures(string $base, string $quote): ?float
    {
        try {
            $symbol = strtoupper($base . $quote);
            $response = Http::timeout(6)
                ->acceptJson()
                ->get('https://api.bybit.com/v5/market/tickers', [
                    'category' => 'linear',
                    'symbol' => $symbol,
                ]);

            if (! $response->ok()) {
                return null;
            }

            $price = $response->json('result.list.0.lastPrice');
            if ($price === null) {
                return null;
            }

            return (float) $price;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fromOkxFutures(string $base, string $quote): ?float
    {
        try {
            // Prefer perpetual/swap instrument to represent active futures market price.
            $instId = strtoupper($base . '-' . $quote . '-SWAP');
            $response = Http::timeout(6)
                ->acceptJson()
                ->get('https://www.okx.com/api/v5/market/ticker', ['instId' => $instId]);

            if (! $response->ok()) {
                return null;
            }

            return (float) ($response->json('data.0.last') ?? 0);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fromGateIoFutures(string $base, string $quote): ?float
    {
        try {
            if (strtoupper($quote) !== 'USDT') {
                return null;
            }

            $contract = strtoupper($base . '_' . $quote);
            $response = Http::timeout(6)
                ->acceptJson()
                ->get('https://api.gateio.ws/api/v4/futures/usdt/tickers', ['contract' => $contract]);

            if (! $response->ok()) {
                return null;
            }

            return (float) ($response->json('0.last') ?? 0);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
