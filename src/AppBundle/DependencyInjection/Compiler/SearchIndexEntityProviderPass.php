<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\DependencyInjection\Compiler;

use Rf\AppBundle\Component\Console\Runner\Search\SearchCreateRunner;
use Rf\AppBundle\Component\Search\Indexing\EntityProviderInterface;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class SearchIndexEntityProviderPass implements CompilerPassInterface
{
    private $runnerServiceId;
    private $providerTag;

    /**
     * @param string $runnerServiceId
     * @param string $providerTag
     */
    public function __construct($runnerServiceId = SearchCreateRunner::class, $providerTag = 'search_index.entity_provider')
    {
        $this->runnerServiceId = $runnerServiceId;
        $this->providerTag = $providerTag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->runnerServiceId)) {
            return;
        }

        $services = array();

        foreach ($container->findTaggedServiceIds($this->providerTag) as $id => $tags) {
            $class = $container->getParameterBag()->resolveValue($container->getDefinition($id)->getClass());

            if (!$reflection = $container->getReflectionClass($class)) {
                throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }

            if ($reflection->implementsInterface(EntityProviderInterface::class)) {
                $container->log($this, sprintf('Registering entity provider: %s', $id));
                $services[$id] = new Reference($id);
            }
        }

        $container->getDefinition($this->runnerServiceId)->addMethodCall('setProviders', [new IteratorArgument($services)]);
    }
}
