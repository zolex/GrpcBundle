<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\GRPC;

use Spiral\GRPC\Server as BaseServer;
use Spiral\RoadRunner\Worker;

class Server
{
    public function __construct(
        private BaseServer $server,
        private Worker $worker,
    ) {
    }

    public function serve()
    {
        $this->server->serve($this->worker);
    }
}
