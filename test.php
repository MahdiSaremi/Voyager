<?php

use Rapid\Voyager\Voyager;

require_once __DIR__ . '/vendor/autoload.php';

$voy = Voyager::factory(__DIR__);

$voy->remote("http://localhost:8000/voy.php");

$voy->source('my_files/**');
