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
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function create(string $className): \Grpc\BaseStub
    {
        $clientsConfig = $this->parameterBag->get('grpc_clients');
        $config = $clientsConfig[$className] ?? $clientsConfig['default'] ?? null;

        if (null === $config)
            throw new Exception(sprintf('No gRPC client config found for "%s"', $className));

        $credentials = match ($config['secure'] ?? false) {
            false => \Grpc\ChannelCredentials::createInsecure(),
            true => \Grpc\ChannelCredentials::createSsl(/* TODO: certs params */),
        };

        return new $className(sprintf("%s:%d", $config['host'], $config['port']), [
            'credentials' => $credentials,
        ]);
    }
}
