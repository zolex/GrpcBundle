<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\DependencyInjection;

use Grpc\BaseStub;
use Grpc\ChannelCredentials;
use Spiral\GRPC\ServiceInterface as LegacyServiceInterface;
use Spiral\RoadRunner\GRPC\ServiceInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zolex\GrpcBundle\GRPC\Exception;

class ZolexGrpcExtension extends Extension implements CompilerPassInterface
{
    private array $config;

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $this->config = $this->processConfiguration($configuration, $configs);
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $server = $container->getDefinition('zolex.grpc.base_server');
        foreach ($container->getDefinitions() as $id => $definition) {
            $class = $definition->getClass();
            if (null === $class || !class_exists($class, false)) {
                continue;
            }

            // add grpc-clients as symfony services in the DI container
            if (is_subclass_of($class, BaseStub::class)) {
                $config = $this->config['clients'][$class] ?? $this->config['clients']['default'] ?? null;
                if (null === $config) {
                    $container->removeDefinition($id);
                    trigger_error(sprintf('No gRPC client config found for "%s" and no default config exists.', $class), E_USER_WARNING);
                    continue;
                }

                $credentials = new Definition(ChannelCredentials::class);
                if ($config['secure'] ?? false) {
                    $credentials->setFactory([ChannelCredentials::class, 'createSsl'], [/* TODO: certs params */]);
                } else {
                    $credentials->setFactory([ChannelCredentials::class, 'createInsecure']);
                }
                $credentialsServiceId = $class . '_credentials';
                $container->setDefinition($credentialsServiceId, $credentials);

                $definition->addTag('zolex.grpc.client');
                $definition->setArgument(0, sprintf("%s:%d", $config['host'], $config['port']));
                $definition->setArgument(1, [
                    'credentials' => new Reference($credentialsServiceId),
                ]);

            // register grpc-services in the grpc base server
            } else if (($interfaces = class_implements($class))) {
                if (isset($interfaces[ServiceInterface::class]) || isset($interfaces[LegacyServiceInterface::class])) {
                    if (true === $this->config['server']['enabled']) {
                        $definition->addTag('zolex.grpc.service');
                        foreach ($interfaces as $interface) {
                            if (is_subclass_of($interface, ServiceInterface::class)) {
                                $server->addMethodCall('registerService', [
                                    $interface,
                                    $definition,
                                ]);
                                break;
                            }
                        }
                    } else {
                        $container->removeDefinition($id);
                    }
                }
            }
        }

        if (true === $this->config['server']['enabled']) {
            $baseServer = $container->getDefinition('zolex.grpc.base_server');
            $baseServer->setArgument(1, $this->config['server']['options']);
        } else {
            $container->removeDefinition('zolex.grpc.base_server');
            $container->removeDefinition('zolex.grpc.server');
        }
    }
}
