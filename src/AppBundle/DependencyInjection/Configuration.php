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
use Symfony\Component\VarDumper\VarDumper;

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
                ->arrayNode('robots')
                    ->info('Roboto access rule definitions.')
                    ->useAttributeAsKey('user_agent')
                    ->arrayPrototype()
                        ->info('Define robot access rules per user-agent.')
                        ->children()
                            ->scalarNode('user_agent')
                                ->info('A search engine user agent string, for example "GoogleBot" or "Bingbot".')
                                ->defaultValue('*')
                                ->treatNullLike('*')
                            ->end()
                            ->arrayNode('allow')
                                ->info('An array of allowed paths for robots.')
                                ->scalarPrototype()
                                    ->isRequired()
                                ->end()
                            ->end()
                            ->arrayNode('disallow')
                                ->info('An array of dis-allowed (restricted) paths for robots.')
                                ->scalarPrototype()
                                    ->isRequired()
                                ->end()
                            ->end()
                            ->booleanNode('merge_default')
                                ->info('Should default rules be merged.')
                                ->defaultTrue()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('metadata')
                    ->info('Define default site metadata attribute values.')
                    ->variablePrototype()->end()
                ->end()
                ->arrayNode('entities')
                    ->info('Custom configuration properties for specific entities.')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')
                                ->info('The name of the entity.')
                            ->end()
                            ->arrayNode('view')
                                ->info('Change entity view behavior.')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->booleanNode('paginate')
                                        ->info('Should the entity listing be paginated?')
                                        ->defaultTrue()
                                    ->end()
                                    ->integerNode('page_count')
                                        ->info('The number of entries per page when paginated.')
                                        ->defaultValue(20)
                                        ->min(1)
                                        ->max(100)
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('repo')
                                ->info('Change entity repository behavior.')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->booleanNode('cache_enabled')
                                        ->info('Should entity fetches be cached?')
                                        ->defaultTrue()
                                    ->end()
                                    ->integerNode('cache_ttl')
                                        ->info('Number of seconds to cache entity.')
                                        ->defaultValue(600)
                                    ->end()
                                    ->scalarNode('cache_service')
                                        ->info('A custom service to store the cache.')
                                        ->defaultNull()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('attr')
                                ->info('Any additional key value attributes.')
                                ->variablePrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
