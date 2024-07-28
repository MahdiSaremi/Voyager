<?php

namespace Rapid\Voyager;

class Voyager
{

    public static function factory(string $root)
    {
        return new VoyagerFactory($root);
    }

}