<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder
            ->root('app')
            ->children()
                ->arrayNode('metadata')
                    ->children()
                        ->scalarNode('title')
                            ->info('Define a title for the website')
                            ->isRequired()
                        ->end()
                        ->scalarNode('description')
                            ->info('Define a description for the website, used in leu of an explicit one set per page')
                            ->isRequired()
                        ->end()
                        ->arrayNode('keywords')
                            ->info('Define a list of keywords for the website, used in leu of explicit ones set per page.')
                            ->defaultValue([])
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('entities')
                    ->arrayPrototype()
                        ->children()
                            ->integerNode('listing_max')
                                ->info('Define the maximum listing count for this entity (per-page)')
                                ->defaultValue(10)
                                ->min(1)
                                ->max(100)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
