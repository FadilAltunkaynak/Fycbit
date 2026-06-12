<?php

use Modules\FutureTrade\Services\CacheServices\CacheServices;

function debugLogger(mixed $message, array $context = [])
{
    futureLogEnable() &&
    logger()->debug($message, $context);
}

function errorLogger(mixed $message, array $context = [])
{
    futureLogEnable() &&
    logger()->error($message, $context);
}

function futureLogEnable(): bool
{
    return (bool) env('FUTURE_LOG_ENABLE', false);
}

function cache_service(): CacheServices
{
    return app('future-cache');
}
