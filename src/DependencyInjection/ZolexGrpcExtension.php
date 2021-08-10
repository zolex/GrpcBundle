<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\DependencyInjection;

use Spiral\GRPC\ServiceInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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

        $baseServer = $container->getDefinition('zolex.grpc.base_server');
        $baseServer->setArgument(1, $this->config['server']['options']);

        $clientFactory = $container->getDefinition('zolex.grpc.client_factory');
        $clientFactory->setArgument(0, $this->config['clients']);
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $definition) {
            $class = $definition->getClass();
            if (null !== $class && class_exists($class, false)
                && ($interfaces = class_implements($class))
                && isset($interfaces[ServiceInterface::class])) {
                $definition->addTag('zolex.grpc.service');
            }
        }
    }
}
