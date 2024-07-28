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

}