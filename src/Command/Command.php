<?php

namespace Rapid\Voyager\Command;

abstract class Command
{

    public abstract function execute() : string|false;

}