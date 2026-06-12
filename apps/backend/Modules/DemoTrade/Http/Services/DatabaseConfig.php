<?php

namespace Modules\DemoTrade\Http\Services;

use PDO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class DatabaseConfig {
    static function setDatabase()
    {
        Config::set('database.connections.DemoTradeMysql',[
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DemoTradeDB_HOST', '127.0.0.1'),
            'port' => env('DemoTradeDB_PORT', '3306'),
            'database' => env('DemoTradeDB_DATABASE', 'forge'),
            'username' => env('DemoTradeDB_USERNAME', 'forge'),
            'password' => env('DemoTradeDB_PASSWORD', ''),
            'unix_socket' => env('DemoTradeDB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);
    }
}