<?php

namespace Winata\PackageBased\Database\Providers;

use Winata\PackageBased\Database\Connection\MySqlConnection;
use Winata\PackageBased\Database\Connection\PgSqlConnection;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;

class DbServiceProvider extends ServiceProvider
{
    public function register()
    {
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });
        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new PgSqlConnection($connection, $database, $prefix, $config);
        });
    }
}
