<?php

namespace Rapid\Voyager\Remote;

use Rapid\Voyager\Console\Progress;
use Rapid\Voyager\VoyagerFactory;

class RemoteClient
{

    public function __construct(
        public VoyagerFactory $voyager,
    )
    {
    }

    public function update()
    {
        echo "\nValidating connection...";
        if (!$this->request('validate'))
        {
            throw new \Exception("Validate failed. Check the file is exists and the key is correct.");
        }

        echo "\nValidating voy.php...";
        $this->request('checkVoy', content: sha1_file($this->voyager->getVoyPath()), outType: $validateVoy);
        if ($validateVoy != 'ok')
        {
            echo "\nError: voy.php file on the server has difference with voy.php on the client!";
            echo "\n\tWarning: Updating voy.php makes some errors. Recommended to update manually.";
            echo "\n\tWould you like to update server-side file (yes/no) no ? ";
            if (!in_array(strtolower(readline()), ['y', 'ye', 'yes', '1', 'ok']))
            {
                return;
            }

            echo "\nUpdating voy.php...";
            $this->request('uploadVoy', content: file_get_contents($this->voyager->getVoyPath()));

            echo "\nValidating again...";
            if (!$this->request('validate'))
            {
                throw new \Exception("Validate failed. That's mean update failed =D");
            }
        }

        echo "\nCreate snapshots...";
        $snapshot = $this->makeNewSnapshot();
        $this->request('getSnapshot', outArgs: $serverSnapshot);

        echo "\nUpdating...";
        $updates = $this->resolveUpdates($snapshot, $serverSnapshot);
        $this->sendUpdates($updates);

        echo "\nUpdating server-side snapshot...";
        $this->request('updateSnapshot', $snapshot);

        echo "\nFinished =D\n";
    }


    protected function resolveUpdates(array $snapshot, array $serverSnapshot)
    {
        $mkdir = array_diff($snapshot['d'], $serverSnapshot['d']);
        $rmdir = array_diff($serverSnapshot['d'], $snapshot['d']);

        $uploads = [];
        $links = [];
        $deletes = [];

        foreach ($serverSnapshot['f'] as $file => $time)
        {
            if (!array_key_exists($file, $snapshot['f']))
            {
                $deletes[] = $file;
            }
        }
        foreach ($snapshot['f'] as $file => $lastTime)
        {
            if (!array_key_exists($file, $serverSnapshot['f']) || $serverSnapshot['f'][$file] < $lastTime)
            {
                $uploads[] = $file;
            }
        }

        foreach ($serverSnapshot['l'] as $file => $time)
        {
            if (!array_key_exists($file, $snapshot['l']))
            {
                $deletes[] = $file;
            }
        }
        foreach ($snapshot['l'] as $file => $target)
        {
            if (!array_key_exists($file, $serverSnapshot['l']) || $serverSnapshot['l'] != $target)
            {
                $links[$file] = $target;
            }
        }

        return [
            'mkdir' => $mkdir,
            'rmdir' => $rmdir,
            'uploads' => $uploads,
            'deletes' => $deletes,
            'links' => $links,
        ];
    }

    protected function sendUpdates(array $updates)
    {
        $changes = array_sum(array_map(count(...), $updates));

        if ($changes == 0)
        {
            echo "\nNo changes required =D";
            return;
        }

        $progress = new Progress(
            $changes,
            "Update server-side... ($changes change required)",
        );

        $progress->text = "Make new directories";
        $progress->show();
        $this->request('mkdir', $updates['mkdir']);
        $progress->value += count($updates['mkdir']);

        $progress->text = "Upload files";
        $progress->show();
        foreach ($updates['uploads'] as $path)
        {
            $progress->show();
            $this->request('upload', ['path' => $path], file_get_contents($this->voyager->getRoot() . '/' . $path));
            $progress->value++;
        }

        $progress->text = "Links";
        $progress->show();
        $this->request('link', $updates['links']);
        $progress->value += count($updates['links']);

        $progress->text = "Remove unneeded files";
        $progress->show();
        $this->request('unlink', $updates['deletes']);
        $progress->value += count($updates['deletes']);
        $progress->show();
        $this->request('rmdir', $updates['rmdir']);
        $progress->value += count($updates['rmdir']);

        $progress->finish();
    }

    protected function makeNewSnapshot()
    {
        $snapshot = [
            'd' => [],
            'f' => [],
            'l' => [],
        ];

        foreach ($this->voyager->getAllPath() as $path)
        {
            $fileMap = $path->getFileMap();

            array_push($snapshot['d'], ...$fileMap->directories);
            $snapshot['f'] += $fileMap->files;
            $snapshot['l'] += $fileMap->links;
        }

        return $snapshot;
    }




    protected function requestEmpty(string $type, array $args = [], string $content = '')
    {
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => RequestFormatter::encode($this->voyager, $type, $args, $content),
            ],
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($this->voyager->server()->url, false, $context);

        if ($response === false)
        {
            throw new \Exception("Failed to connect [{$this->voyager->server()->url}]");
        }

        return $response;
    }

    protected function request(string $type, array $args = [], string $content = '', ?string &$outType = null, ?array &$outArgs = null, ?string &$outContent = null)
    {
        return RequestFormatter::decode(
            $this->voyager,
            $this->requestEmpty($type, $args, $content),
            $outType,
            $outArgs,
            $outContent,
        );
    }

}