<?php

use Rapid\Voyager\Console\Progress;

require_once __DIR__ . '/vendor/autoload.php';

$progress = new Progress(250, 'Updating windows...');

for ($i = 0; $i <= 250; $i++)
{
    $progress->text = match (true) {
        $i < 50 => 'Bootstrapping...',
        $i < 100 => 'Starting...',
        $i < 150 => 'Downloading...',
        $i < 200 => 'Validating...',
        default => 'Installing...',
    };

    $progress->value = $i;
    $progress->show();
    usleep(100000);
}
