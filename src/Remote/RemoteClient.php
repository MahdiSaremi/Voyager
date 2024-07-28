<?php

namespace Rapid\Voyager\Remote;

use Rapid\Voyager\VoyagerFactory;

class RemoteClient
{

    public function __construct(
        public VoyagerFactory $voyager,
    )
    {
    }

}