<?php

namespace Rapid\Voyager\Path;

use Rapid\Voyager\VoyagerFactory;

abstract class Path
{

    public function __construct(
        public VoyagerFactory $voyager,
        public readonly string $path,
    )
    {
    }

    public function getRealPath()
    {
        return $this->voyager->getRoot() . '/' . $this->path;
    }

    public function getContainsFiles()
    {
        $path = $this->getRealPath();

        if (!str_contains($this->path, '**'))
        {
            return glob($path);
        }
        elseif (substr_count($this->path, '**') > 1)
        {
            throw new \InvalidArgumentException("Can't parse two double star ** in path [$path]");
        }
        else
        {
            $list = [];
            [$prefix, $suffix] = explode('**', $path, 2);
            $stars = '*';
            while (true)
            {
                $new = glob("{$prefix}{$stars}{$suffix}");

                if (!$new) return $list;

                array_push($list, ...$new);

                $stars .= '/*';
            }
        }
    }

}