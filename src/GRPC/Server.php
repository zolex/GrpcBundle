<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\GRPC;

use Spiral\RoadRunner\GRPC\Server as BaseServer;
use Spiral\RoadRunner\Worker;

class Server
{
    public function __construct(
        private BaseServer $server,
        private Worker $worker,
    ) {
    }

    public function serve(): void
    {
        $this->server->serve($this->worker);
    }
}
