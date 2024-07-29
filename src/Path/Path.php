<?php

namespace Rapid\Voyager\Path;

use Rapid\Voyager\VoyagerFactory;

abstract class Path
{

    public function __construct(
        public VoyagerFactory $voyager,
        public readonly string $path,
        public readonly string $serverPath,
    )
    {
    }

    public function getPath() : string
    {
        return $this->voyager->isClient ? $this->path : $this->serverPath;
    }

    public function getRealPath() : string
    {
        return $this->voyager->getRoot() . '/' . $this->getPath();
    }

    protected FileMap $_fileMap;

    public function getFileMap() : FileMap
    {
        return $this->_fileMap ??= FileMap::fromPath($this->voyager, $this->getPath());
    }

}