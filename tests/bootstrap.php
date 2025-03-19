<?php

use Pebble\Database\QB;

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');

setlocale(LC_ALL, 'fr_FR.utf8');
setlocale(LC_NUMERIC, 'C');
ini_set('date.timezone', 'Europe/Paris');

require __DIR__ . '/../vendor/autoload.php';

foreach (glob(__DIR__ . '/ressources/*.php') as $file) {
    require $file;
}

$qb = QB::create('tests');
$qb->whereInMultiple(['event', 'date'], [
    ['event' => 1, 'date' => 1],
    ['event' => 3, 'date' => 5],
]);
echo $qb->read()->__toString();
