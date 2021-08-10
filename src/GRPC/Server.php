<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\GRPC;

use Spiral\GRPC\Server as BaseServer;
use Spiral\GRPC\ServiceInterface;
use Spiral\RoadRunner\Worker;

class Server
{
    /**
     * @var iterable<ServiceInterface> $services
     */
    public function __construct(
        private BaseServer $server,
        private Worker $worker,
        private iterable $services
    ) {
    }

    /**
     * gRPC services must be rigistered with their generated interfaces.
     * Automatically picks the right interface for registration.
     *
     * @throws \ReflectionException
     */
    private function registerServices()
    {
        foreach ($this->services as $service) {
            $reflection = new \ReflectionClass($service);
            $interfaces = $reflection->getInterfaceNames();
            foreach ($interfaces as $interface) {
                if (is_subclass_of($interface, ServiceInterface::class)) {
                    $this->server->registerService($interface, $service);
                    break;
                }
            }
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function serve()
    {
        $this->registerServices();
        $this->server->serve($this->worker);
    }
}
