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

    public static function fromPath(VoyagerFactory $voyager, string $path)
    {
        $realPath = $voyager->getRoot() . '/' . $path;
        $prefixSize = strlen($voyager->getRoot()) + 1;
        if (is_dir($realPath))
        {
            $list = [];
            if (file_exists($realPath))
            {
                $list[] = $path;
            }

            $pattern = $realPath . "/*";
            while (true)
            {
                $items = glob($pattern);
                $pattern .= "/*";

                array_push($list, ...array_map(fn ($item) => substr($item, $prefixSize), $items));

                if (count($items) == 0)
                {
                    return static::from($voyager, $list);
                }
            }
        }
        elseif (file_exists($realPath))
        {
            return static::from($voyager, [$path]);
        }
        else
        {
            return static::from($voyager, []);
        }
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