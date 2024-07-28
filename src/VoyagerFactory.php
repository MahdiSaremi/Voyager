<?php

namespace Rapid\Voyager;

use Rapid\Voyager\Path\Path;
use Rapid\Voyager\Path\SourcePath;

class VoyagerFactory
{

    protected Remote\RemoteServer $server;
    protected Remote\RemoteClient $client;

    public function __construct(
        protected string $root,
    )
    {
        $this->server = new Remote\RemoteServer($this);
        $this->client = new Remote\RemoteClient($this);
    }

    /**
     * Get root path
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    protected array $path = [];

    /**
     * Add path object
     *
     * @param Path $path
     * @return void
     */
    public function addPath(Path $path)
    {
        $this->path[] = $path;
    }

    /**
     * Add source path
     *
     * @param string $path
     * @return SourcePath
     */
    public function source(string $path)
    {
        $object = new SourcePath($this, $path);
        $this->addPath($object);

        return $object;
    }

    public function server()
    {
        return $this->server;
    }

    public function client()
    {
        return $this->client;
    }

    public function remote(string $url)
    {
        $this->server->url = $url;
        return $this;
    }

}