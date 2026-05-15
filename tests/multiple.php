<?php

use Pebble\Database\QB;

include __DIR__ . '/bootstrap.php';

$qb = QB::create('tests');
$qb->whereInMultiple(['event', 'date'], [
    ['event' => 1, 'date' => 1],
    ['event' => 3, 'date' => 5],
]);
echo $qb->read()->__toString() . PHP_EOL;
