<?php

namespace Rapid\Voyager\Path;

class ExcludePath extends Path
{

    public function getFileMap() : FileMap
    {
        return $this->_fileMap ??= new FileMap($this->voyager, [], [], []);
    }

}