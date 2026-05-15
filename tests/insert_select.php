<?php

use Pebble\Database\Query;
use Pebble\Database\QB;

include __DIR__ . '/bootstrap.php';

$insert = new Query("REPLACE INTO stats (created_at, total)");
$select = QB::create("logs")
    ->select('created_at')
    ->select('SUM(1)')
    ->groupBy('created_at')
    ->read();

$a = Query::insertSelect($insert, $select);

echo $a->__toString() . PHP_EOL;
