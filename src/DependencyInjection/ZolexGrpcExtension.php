<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\DependencyInjection;

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

        $grpcServer = $container->getDefinition('zolex.grpc.server');
        $grpcServer->setArgument(3, $this->config['server']['interface_namespace']);
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
                && isset($interfaces['Spiral\GRPC\ServiceInterface'])) {
                $definition->addTag('zolex.grpc.service');
            }
        }
    }
}
