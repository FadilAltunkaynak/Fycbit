<?php

namespace Modules\DemoTrade\Http\Services;


use Illuminate\Support\Facades\DB;

class DBService
{
    /**
     * Begin transaction for multiple DB connection
     */
    public static function beginTransaction()
    {
        DB::connection("DemoTradeMysql")->beginTransaction();
    }

    /**
     * Rollback transaction for multiple DB connection
     */
    public static function rollBack()
    {
        DB::connection("DemoTradeMysql")->rollBack();
    }

    /**
     * Commit transaction for multiple DB connection
     */
    public static function commit()
    {
        DB::connection("DemoTradeMysql")->commit();
    }
}
