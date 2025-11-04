<?php
require_once __DIR__ . '/src/utils/Autoload.php';

use netvod\utils\Autoload;
use netvod\dispatcher\Dispatcher;

Autoload::register();

$dispatcher = new Dispatcher();
$dispatcher->run();
