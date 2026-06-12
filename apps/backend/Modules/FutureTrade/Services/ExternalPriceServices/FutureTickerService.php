<?php

namespace Modules\FutureTrade\Services\ExternalPriceServices;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FutureTickerService
{
    /**
     * @param string $symbols Examples: BTCUSDT, BTC_USDT, BTC, BTC,ETH
     * @param ExternalPriceProvider|null $providerFlag Preferred provider. Null means random fallback order.
     * @param bool $alwaysArray true = always list response, false = single item for single input
     *
     * @return array<string,mixed>|array<int,array<string,mixed>>
     */
    public function method(string $symbols, ?ExternalPriceProvider $providerFlag = null, bool $alwaysArray = false): array
    {
        return $this->getPrice($symbols, $providerFlag, $alwaysArray);
    }

    /**
     * @param string $symbols Examples: BTCUSDT, BTC_USDT, BTC, BTC,ETH
     * @param ExternalPriceProvider|null $providerFlag Preferred provider. Null means random fallback order.
     * @param bool $alwaysArray true = always list response, false = single item for single input
     *
     * @return array<string,mixed>|array<int,array<string,mixed>>
     */
    public function getPrice(string $symbols, ?ExternalPriceProvider $providerFlag = null, bool $alwaysArray = false): array
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
     * @return array<int,string>
     */
    private function splitSymbols(string $symbols): array
    {
        $list = array_filter(array_map('trim', explode(',', $symbols)));

        return array_values($list);
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
    private function resolveOne(string $input, ?ExternalPriceProvider $providerFlag): array
    {
        $normalized = $this->normalizeSymbol($input);
        $providers = $this->buildProviderOrder($providerFlag);

        foreach ($providers as $candidate) {
            try {
                $price = $this->getPriceByProvider($normalized['base'], $normalized['quote'], $candidate);
            } catch (\Throwable $e) {
                $price = null;
            }

            if ($price !== null && $price > 0) {
                return [
                    'input' => $normalized['input'],
                    'symbol' => $normalized['pair'],
                    'provider' => $candidate->value,
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
     * @return array<int,ExternalPriceProvider>
     */
    private function buildProviderOrder(?ExternalPriceProvider $firstProvider): array
    {
        $providers = ExternalPriceProvider::cases();
        shuffle($providers);

        if ($firstProvider === null) {
            return $providers;
        }

        $providers = array_values(array_filter(
            $providers,
            fn (ExternalPriceProvider $provider): bool => $provider !== $firstProvider
        ));

        array_unshift($providers, $firstProvider);

        return $providers;
    }

    private function getPriceByProvider(string $base, string $quote, ExternalPriceProvider $provider): ?float
    {
        return match ($provider) {
            ExternalPriceProvider::BINANCE => $this->fromBinance($base, $quote),
            ExternalPriceProvider::COINGECKO => $this->fromCoinGecko($base),
            ExternalPriceProvider::KRAKEN => $this->fromKraken($base, $quote),
            ExternalPriceProvider::OKX => $this->fromOkx($base, $quote),
            ExternalPriceProvider::GATEIO => $this->fromGateIo($base, $quote),
            ExternalPriceProvider::KUCOIN => $this->fromKuCoin($base, $quote),
            ExternalPriceProvider::CRYPTOCOMPARE => $this->fromCryptoCompare($base, $quote),
        };
    }

    private function fromBinance(string $base, string $quote): ?float
    {
        try {
            $symbol = strtoupper($base . $quote);

            $response = Http::timeout(6)
                ->acceptJson()
                ->get('https://api.binance.com/api/v3/ticker/price', ['symbol' => $symbol]);

            if (! $response->ok()) {
                return null;
            }

            return (float) ($response->json('price') ?? 0);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fromCoinGecko(string $base): ?float
    {
        try {
            $symbol = strtolower($base);

            $response = Http::timeout(6)
                ->acceptJson()
                ->get('https://api.coingecko.com/api/v3/coins/markets', [
                    'vs_currency' => 'usd',
                    'symbols' => $symbol,
                    'order' => 'market_cap_desc',
                    'per_page' => 1,
                    'page' => 1,
                    'sparkline' => 'false',
                ]);

            if (! $response->ok()) {
                return null;
            }

            $row = $response->json('0');

            return isset($row['current_price']) ? (float) $row['current_price'] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fromKraken(string $base, string $quote): ?float
    {
        try {
            $baseMap = ['BTC' => 'XBT'];
            $mappedBase = $baseMap[$base] ?? $base;

            $pairs = [
                $base . $quote,
                $mappedBase . $quote,
                $base . '/' . $quote,
                $mappedBase . '/' . $quote,
            ];

            if ($quote === 'USD') {
                $pairs[] = 'XXBTZUSD';
            }

            if ($quote === 'USDT') {
                $pairs[] = 'XXBTZUSDT';
            }

            $response = Http::timeout(6)
                ->acceptJson()
                ->get('https://api.kraken.com/0/public/Ticker', ['pair' => implode(',', array_unique($pairs))]);

            if (! $response->ok()) {
                return null;
            }

            $result = $response->json('result');
            if (! is_array($result)) {
                return null;
            }

            foreach ($result as $ticker) {
                $lastTrade = $ticker['c'][0] ?? null;
                if ($lastTrade !== null) {
                    return (float) $lastTrade;
                }
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fromOkx(string $base, string $quote): ?float
    {
        try {
            $instId = strtoupper($base . '-' . $quote);

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

    private function fromGateIo(string $base, string $quote): ?float
    {
        try {
            $pair = strtoupper($base . '_' . $quote);

            $response = Http::timeout(6)
                ->acceptJson()
                ->get('https://api.gateio.ws/api/v4/spot/tickers', ['currency_pair' => $pair]);

            if (! $response->ok()) {
                return null;
            }

            return (float) ($response->json('0.last') ?? 0);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fromKuCoin(string $base, string $quote): ?float
    {
        try {
            $symbol = strtoupper($base . '-' . $quote);

            $response = Http::timeout(6)
                ->acceptJson()
                ->get('https://api.kucoin.com/api/v1/market/orderbook/level1', ['symbol' => $symbol]);

            if (! $response->ok()) {
                return null;
            }

            return (float) ($response->json('data.price') ?? 0);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fromCryptoCompare(string $base, string $quote): ?float
    {
        try {
            $fsym = strtoupper($base);
            $tsym = strtoupper($quote);

            $response = Http::timeout(6)
                ->acceptJson()
                ->get('https://min-api.cryptocompare.com/data/price', [
                    'fsym' => $fsym,
                    'tsyms' => $tsym,
                ]);

            if (! $response->ok()) {
                return null;
            }

            $price = $response->json($tsym);
            if ($price === null) {
                return null;
            }

            return (float) $price;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
