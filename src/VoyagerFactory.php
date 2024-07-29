<?php

namespace Rapid\Voyager;

use Rapid\Voyager\Command\ShellCommand;
use Rapid\Voyager\Path\Path;
use Rapid\Voyager\Path\SourcePath;

class VoyagerFactory
{

    protected Remote\RemoteServer $server;
    protected Remote\RemoteClient $client;

    public function __construct(
        protected string $voyFile,
        protected string $root,
        protected string $serverRoot,
    )
    {
        $this->server = new Remote\RemoteServer($this);
        $this->client = new Remote\RemoteClient($this);
    }

    public bool $isClient;

    /**
     * Get root path
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->isClient ? $this->root : $this->serverRoot;
    }

    /**
     * @var Path[]
     */
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
     * Get the `voy.php` file path
     *
     * @return string
     */
    public function getVoyPath()
    {
        return $this->voyFile;
    }

    /**
     * Add source path
     *
     * @param string      $path
     * @param string|null $serverPath
     * @return SourcePath
     */
    public function source(string $path, string $serverPath = null)
    {
        $object = new SourcePath($this, $path, $serverPath ?? $path);
        $this->addPath($object);

        return $object;
    }

    /**
     * Get list of path
     *
     * @return Path[]
     */
    public function getAllPath()
    {
        return $this->path;
    }

    /**
     * Get server configuration
     *
     * @return Remote\RemoteServer
     */
    public function server()
    {
        return $this->server;
    }

    /**
     * Get client configuration
     *
     * @return Remote\RemoteClient
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * Set remote url & key on server-side
     *
     * @param string $url
     * @param string $key
     * @return $this
     */
    public function remote(string $url, string $key)
    {
        $this->server->url = $url;
        $this->key = $key;
        return $this;
    }

    protected string $key;

    /**
     * Hash value using key
     *
     * @param string $value
     * @return string
     */
    public function hash(string $value)
    {
        return md5(md5($this->key) . $value);
    }

    protected array $commandAlways = [];
    protected array $commands = [];

    /**
     * Register a command to run after each update
     *
     * @param string $command
     * @return $this
     */
    public function commandAlways(string $command)
    {
        $this->commandAlways[] = $command;
        return $this;
    }

    /**
     * Register a command to run when you run `php voy.php [group]`
     *
     * @param string $group
     * @param string $command
     * @return $this
     */
    public function command(string $group, string $command)
    {
        @$this->commands[$group][] = new ShellCommand($command);
        return $this;
    }

    /**
     * Get always commands
     *
     * @return Command\Command[]
     */
    public function getAlwaysCommands() : array
    {
        return $this->commandAlways;
    }

    /**
     * Get group commands
     *
     * @param string $group
     * @return Command\Command[]
     */
    public function getGroup(string $group) : array
    {
        return $this->commands[$group] ?? [];
    }


    /**
     * Start voyager
     *
     * This function is used for both side (server & client)
     *
     * @return void
     */
    public function start()
    {
        $content = file_get_contents('php://input', length: 2);
        if ($content)
        {
            $this->isClient = false;
            $this->server->update();
        }
        else
        {
            try
            {
                $this->isClient = true;
                echo "Voyager starting...\n";

                echo "\nDo you want update (yes/no) yes ? ";
                if (in_array(strtolower(readline()), ['y', 'ye', 'yes', '1', 'ok', '']))
                {
                    $this->client->update();
                }
            }
            catch (\Exception $e)
            {
                echo "\nError: {$e->getMessage()}\n";
            }
        }
    }

    public function convertToServerPath(string $path)
    {
        foreach ($this->path as $p)
        {
            if ($p->path == $path)
            {
                return $p->serverPath;
            }
            elseif (str_starts_with($path, $p->path . '/') || str_starts_with($path, $p->path . '\\'))
            {
                return $p->serverPath . substr($path, strlen($p->path));
            }
        }

        return $path;
    }

    public function convertToServerFullPath(string $path)
    {
        return $this->serverRoot . '/' . $this->convertToServerPath($path);
    }

}