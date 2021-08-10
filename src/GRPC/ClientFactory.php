<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\GRPC;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class ClientFactory
 *
 * @package App\GRPC
 */
class ClientFactory
{
    public function __construct(private array $clientsConfig = [])
    {
    }

    public function create(string $className): \Grpc\BaseStub
    {
        $config = $this->clientsConfig[$className] ?? $this->clientsConfig['default'] ?? null;
        if (null === $config) {
            throw new Exception(sprintf('No gRPC client config found for "%s" and no default config exists.', $className));
        }

        $credentials = match ($config['secure'] ?? false) {
            false => \Grpc\ChannelCredentials::createInsecure(),
            true => \Grpc\ChannelCredentials::createSsl(/* TODO: certs params */),
        };

        return new $className(sprintf("%s:%d", $config['host'], $config['port']), [
            'credentials' => $credentials,
        ]);
    }
}
