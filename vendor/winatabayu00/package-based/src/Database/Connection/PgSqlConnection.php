<?php

namespace Winata\PackageBased\Database\Connection;

use Winata\PackageBased\Database\Blueprints\Blueprint;
use Illuminate\Database\PostgresConnection as BaseConnection;
//use Illuminate\Database\Schema\PostgresBuilder as BaseBuilder;

class PgSqlConnection extends BaseConnection
{
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        $builder = parent::getSchemaBuilder();
        $builder->blueprintResolver(function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        return $builder;
    }
}
