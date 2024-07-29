<?php

namespace Rapid\Voyager\Command;

class ShellCommand extends Command
{

    public function __construct(
        protected string $command,
    )
    {
    }

    public function execute() : string|false
    {
        exec($this->command, $output);
        return implode("\n", $output);
    }

}