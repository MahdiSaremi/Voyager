<?php

namespace Rapid\Voyager\Path;

use Rapid\Voyager\VoyagerFactory;

class FileMap
{

    public function __construct(
        public VoyagerFactory $voyager,
        public array $directories,
        public array $files,
        public array $links,
    )
    {
    }

    public static function fromPath(Path $source, string $path)
    {
        $realPath = $source->voyager->getRoot($path);
        if (is_dir($realPath))
        {
            return static::from($source->voyager,
                array_merge(static::findFilesRecursive($source, $path, $realPath), $path == '.' ? [] : [$path])
            );
        }
        elseif (file_exists($realPath))
        {
            return static::from($source->voyager, $path == '.' ? [] : [$path]);
        }
        else
        {
            return static::from($source->voyager, []);
        }
    }

    private static function findFilesRecursive(Path $source, string $path, string $realPath)
    {
        $list = [];
        foreach (scandir($realPath) as $sub)
        {
            if ($sub == '.' || $sub == '..')
                continue;

            if ($path == '.')
                $path0 = $sub;
            else
                $path0 = $path . '/' . $sub;

            $realPath0 = $realPath . '/' . $sub;
            if ($source->containsJustSelf($path0))
            {
                $list[] = $path0;
                if (is_dir($realPath0))
                {
                    array_push($list, ...static::findFilesRecursive($source, $path0, $realPath0));
                }
            }
        }

        return $list;
    }

    public static function from(VoyagerFactory $voyager, array $list) : FileMap
    {
        $map = new static($voyager, [], [], []);
        $realPrefix = $voyager->getRoot();
        foreach ($list as $item)
        {
            $realPath = $realPrefix . '/' . $item;
            if (is_link($realPath))
            {
                $map->links[$item] = substr(readlink($realPath), strlen($realPrefix) + 1);
            }
            elseif (is_dir($realPath))
            {
                $map->directories[] = $item;
            }
            elseif (is_file($realPath))
            {
                $map->files[$item] = filemtime($item);
            }
        }

        return $map;
    }

}