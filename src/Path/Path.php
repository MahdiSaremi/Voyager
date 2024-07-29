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

    public function contains(string $path)
    {
        return $path == $this->path || str_starts_with($path, $this->path . '/') || str_starts_with($path, $this->path . '\\');
    }

    public function containsJustSelf(string $path)
    {
        return $this->voyager->resolveWhatPathIs($path) === $this;
    }

    protected FileMap $_fileMap;
    public abstract function getFileMap() : FileMap;

}