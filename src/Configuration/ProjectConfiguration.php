<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
 * @copyright 2016-2019 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace Mactronique\CUA\Configuration;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ProjectConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('main');

        // ... add node definitions to the root of the tree
        $rootNode
            ->children()
                ->arrayNode('projects')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('path')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('lock_path')
                                ->defaultValue('./composer.lock')
                            ->end()
                            ->booleanNode('check_dependencies')
                                ->defaultTrue()
                            ->end()
                            ->booleanNode('check_security')
                                ->defaultFalse()
                            ->end()
                            ->scalarNode('php_path')
                                ->defaultValue('php')
                            ->end()
                            ->arrayNode('private_dependencies')
                                ->prototype('scalar')
                                ->end()
                            ->end()
                            ->enumNode('private_dependencies_strategy')
                                ->defaultValue('remove')
                                ->values(array('remove', 'hash'))
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
