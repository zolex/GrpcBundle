<?php

declare(strict_types=1);

namespace Zolex\GrpcBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @package Modix\ThriftServerBundle\DependencyInjection
 * @author Andreas Linden <andreas.linden@coxautoinc.com>
 * @version 1.0
 * @since 2021-02-13
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder|void
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('modix_thrift_server');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('server')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('options')
                            ->useAttributeAsKey('name')
                            ->arrayPrototype()
                            ->performNoDeepMerging()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('clients')
                    ->useAttributeAsKey('class')
                    ->arrayPrototype()
                    ->performNoDeepMerging()
                    ->children()
                        ->scalarNode('host')->isRequired()->end()
                        ->scalarNode('port')->defaultValue("80")->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
