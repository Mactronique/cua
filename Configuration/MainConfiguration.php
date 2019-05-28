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

class MainConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('main');

        // ... add node definitions to the root of the tree
        $rootNode
            ->children()
                ->arrayNode('projects')
                    ->isRequired()
                    ->normalizeKeys(false)
                    ->cannotBeEmpty()
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('persistance')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('format')->defaultValue('YamlFile')->end()
                        ->arrayNode('parameters')
                            ->defaultValue(['path'=>'./all.yaml'])
                            ->prototype('variable')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('composer_path')
                    ->defaultValue('/usr/bin/composer')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
