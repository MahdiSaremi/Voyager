<?php

namespace Rapid\Voyager\Path;

class SourcePath extends Path
{

    public function getFileMap() : FileMap
    {
        return $this->_fileMap ??= FileMap::fromPath($this, $this->getPath());
    }

}