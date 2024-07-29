<?php

namespace Rapid\Voyager;

class Voyager
{

    public static function factory(string $voyFile, string $root, ?string $serverRoot = null)
    {
        return new VoyagerFactory($voyFile, $root, $serverRoot ?? $root);
    }

}