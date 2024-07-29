<?php

use Rapid\Voyager\Voyager;

require_once __DIR__ . '/vendor/autoload.php';

$voy = Voyager::factory(__FILE__, __DIR__, __DIR__ . '/server');

$voy->remote("http://localhost:8000/voy.php", 'My-Key');

$voy->source('my_files', 'her_files');

$voy->start();
