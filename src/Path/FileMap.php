<?php

namespace Rapid\Voyager\Path;

class FileMap
{

    public function __construct(
        public array $directories,
        public array $files,
    )
    {
    }

}