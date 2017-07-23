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

use Rf\AppBundle\Component\Sitemap\RecordGenerator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\VarDumper\VarDumper;

class RecordRouteGeneratorCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $serviceId;

    /**
     * @var string
     */
    private $serviceTag;

    /**
     * @param string $serviceId
     * @param string $serviceTag
     */
    public function __construct(string $serviceId = RecordGenerator::class, string $serviceTag = 'app.record_route_generator')
    {
        $this->serviceId = $serviceId;
        $this->serviceTag = $serviceTag;
    }

    public function process(ContainerBuilder $container)
    {
        if (!$container->has($this->serviceId)) {
            return;
        }

        $container->getDefinition($this->serviceId)->addMethodCall('addRouteGenerators', $this->getMatchedServiceReferences($container));
    }

    private function getMatchedServiceReferences(ContainerBuilder $container): array
    {
        $services = [];

        foreach ($container->findTaggedServiceIds($this->serviceTag) as $name => $tags) {
            $services[] = new Reference($name);
        }

        return $services;
    }

    /**
     * @param array $tags
     *
     * @return int
     */
    private function determinePriorityFromTags(array $tags): int
    {
        foreach ($tags as $t) {
            if (in_array('priority', $t)) {
                return $t['priority'];
            }
        }

        return 0;
    }
}
