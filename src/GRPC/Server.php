<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\GRPC;

use Spiral\GRPC\Server as BaseServer;
use Spiral\GRPC\ServiceInterface;
use Spiral\RoadRunner\Worker;

class Server
{
    private BaseServer $server;
    private Worker $worker;

    /**
     * @var ServiceInterface[]
     */
    private iterable $services;
    private string $interfaceNamespace;

    /**
     * @param BaseServer $server
     * @param Worker $worker
     * @param ServiceInterface[] $services
     * @param string $interfaceNamespace
     */
    public function __construct(BaseServer $server, Worker $worker, iterable $services, string $interfaceNamespace)
    {
        $this->server = $server;
        $this->worker = $worker;
        $this->services = $services;
        $this->interfaceNamespace = $interfaceNamespace;
    }

    /**
     * @throws \ReflectionException
     */
    private function registerServices()
    {
        foreach ($this->services as $service) {
            $reflection = new \ReflectionClass($service);
            $interfaces = $reflection->getInterfaceNames();
            foreach ($interfaces as $interface) {
                if (str_contains($interface, $this->interfaceNamespace)) {
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
