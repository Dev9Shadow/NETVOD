<?php
require_once __DIR__ . '/vendor/Autoload.php';

use netvod\dispatcher\Dispatcher;

$dispatcher = new Dispatcher();
$dispatcher->run();