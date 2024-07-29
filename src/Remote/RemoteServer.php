<?php

namespace Rapid\Voyager\Remote;

use Rapid\Voyager\VoyagerFactory;

class RemoteServer
{

    public function __construct(
        public VoyagerFactory $voyager,
        public ?string $url = null,
    )
    {
    }

    public function update()
    {
        if (RequestFormatter::decode(
            $this->voyager,
            file_get_contents('php://input'),
            $type,
            $args,
            $content
        ))
        {
            switch ($type)
            {
                case 'validate':
                    $this->ok();
                    break;

                case 'checkVoy':
                    if (sha1_file($this->voyager->getVoyPath()) == $content)
                    {
                        $this->ok();
                    }
                    else
                    {
                        $this->response('failed');
                    }
                    break;

                case 'uploadVoy':
                    file_put_contents($this->voyager->getVoyPath(), $content);
                    break;

                case 'getSnapshot':
                    if (file_exists('.voyager.snapshot.lock'))
                    {
                        $this->ok(json_decode(file_get_contents('.voyager.snapshot.lock'), true));
                        break;
                    }

                    $this->ok(['d' => [], 'f' => [], 'l' => []]);
                    break;

                case 'updateSnapshot':
                    file_put_contents('.voyager.snapshot.lock', json_encode($args));
                    $this->ok();
                    break;

                case 'mkdir':
                    foreach ($args as $dir)
                    {
                        @mkdir($this->voyager->convertToServerFullPath($dir), recursive: true);
                    }
                    $this->ok();
                    break;

                case 'rmdir':
                    foreach ($args as $dir)
                    {
                        @rmdir($this->voyager->convertToServerFullPath($dir));
                    }
                    $this->ok();
                    break;

                case 'unlink':
                    foreach ($args as $dir)
                    {
                        @unlink($this->voyager->convertToServerFullPath($dir));
                    }
                    $this->ok();
                    break;

                case 'link':
                    foreach ($args as $file => $target)
                    {
                        $path = $this->voyager->convertToServerFullPath($file);
                        if (file_exists($path))
                        {
                            @unlink($path);
                        }
                        @symlink($this->voyager->convertToServerFullPath($target), $path);
                    }
                    $this->ok();
                    break;

                case 'upload':
                    file_put_contents(
                        $this->voyager->convertToServerFullPath($args['path']),
                        $content,
                    );
                    $this->ok();
                    break;
            }
        }
    }

    public function response(string $type, array $args = [], string $content = '')
    {
        echo RequestFormatter::encode(
            $this->voyager,
            $type,
            $args,
            $content,
        );
        die;
    }

    public function ok(array $args = [], string $content = '')
    {
        $this->response('ok', $args, $content);
    }

}