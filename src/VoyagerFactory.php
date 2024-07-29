<?php

namespace Rapid\Voyager;

use Rapid\Voyager\Command\ShellCommand;
use Rapid\Voyager\Path\ExcludePath;
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
     * @param string $path
     * @return string
     */
    public function getRoot(string $path = '.')
    {
        return ($this->isClient ? $this->root : $this->serverRoot) . ($path == '.' ? '' : '/' . $path);
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
     * @param string|array $path
     * @param string|null  $serverPath
     * @return SourcePath|void
     */
    public function source(string|array $path, string $serverPath = null)
    {
        if (is_array($path))
        {
            foreach ($path as $a => $b)
            {
                if (is_int($a))
                {
                    $this->source($b);
                }
                else
                {
                    $this->source($a, $b);
                }
            }

            return;
        }

        $object = new SourcePath($this, $path, $serverPath ?? $path);
        $this->addPath($object);

        return $object;
    }

    /**
     * Add root folder as source
     *
     * @return SourcePath
     */
    public function sourceRoot()
    {
        return $this->source('.');
    }

    /**
     * Add exclude path
     *
     * @param string|array $path
     * @return ExcludePath|void
     */
    public function exclude(string|array $path)
    {
        if (is_array($path))
        {
            foreach ($path as $a)
            {
                $this->exclude($a);
            }

            return;
        }

        $object = new ExcludePath($this, $path, $path);
        $this->addPath($object);

        return $object;
    }

    /**
     * Move $sendPath to the $path (or $serverPath) in the server.
     * And $path will not sent.
     *
     * Example of laravel public path:
     *
     * `$this->instead('public/index.php', 'public/index.server.php', '../public_html/index.php')`
     *
     * @param string      $path
     * @param string      $sendPath
     * @param string|null $serverPath
     * @return void
     */
    public function instead(string $path, string $sendPath, string $serverPath = null)
    {
        $this->exclude($path);
        $this->source($sendPath, $serverPath ?? $path);
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

    public function resolveWhatPathIs(string $path) : ?Path
    {
        $bestSelectionScore = null;
        $bestSelectionResult = null;
        foreach ($this->path as $p)
        {
            if ($p->path == $path)
            {
                return $p;
            }
            elseif ($p->path == '.')
            {
                if (is_null($bestSelectionScore))
                {
                    $bestSelectionScore = 0;
                    $bestSelectionResult = $p;
                }
            }
            elseif (str_starts_with($path, $p->path . '/') || str_starts_with($path, $p->path . '\\'))
            {
                $score = strlen($p->path);
                if ($score > $bestSelectionScore)
                {
                    $bestSelectionScore = $score;
                    $bestSelectionResult = $p;
                }
            }
        }

        return $bestSelectionResult;
    }

    public function convertToServerPath(string $path)
    {
        $best = $this->resolveWhatPathIs($path);

        if ($best === null)
        {
            return $path;
        }
        elseif ($best->path == $path)
        {
            return $best->serverPath;
        }
        else
        {
            return $best->serverPath . '/' . $path;
        }
    }

    public function convertToServerFullPath(string $path)
    {
        return $this->serverRoot . '/' . $this->convertToServerPath($path);
    }

}