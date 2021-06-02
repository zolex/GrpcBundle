<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\Goridge;

use Spiral\Goridge\RelayInterface;
use Spiral\Goridge\SendPackageRelayInterface;
use Spiral\Goridge\StreamRelay;

/**
 * StreamRealy exprects two resources as constructor arguments. The service container
 * can not dump resource arguments, so we simply wrap it in a service.
 */
class RelayWrapper implements RelayInterface, SendPackageRelayInterface
{
    private StreamRelay $relay;

    public function __construct()
    {
        $this->relay = new StreamRelay(STDIN, STDOUT);
    }

    public function send(string $payload, ?int $flags = null)
    {
        return $this->relay->send($payload, $flags);
    }

    public function receiveSync(?int &$flags = null)
    {
        return $this->relay->receiveSync($flags);
    }

    public function sendPackage(string $headerPayload, ?int $headerFlags, string $bodyPayload, ?int $bodyFlags = null)
    {
        return $this->relay->sendPackage($headerPayload, $headerFlags, $bodyPayload, $bodyFlags);
    }
}
