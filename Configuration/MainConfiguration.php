<?php

/**
 * This file is part of Composer Update Analyser package.
 *
 * @author Jean-Baptiste Nahan <jean-baptiste.nahan@inextenso.fr>
 * @copyright 2016 - Jean-Baptiste Nahan
 * @license MIT
 */
namespace InExtenso\CUA\Configuration;

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
