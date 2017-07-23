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

use Rf\AppBundle\Component\Registry\Metadata\MetadataRegistry;
use Rf\AppBundle\Component\Registry\Robots\RobotsRegistry;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\VarDumper\VarDumper;

class AppExtension extends Extension
{
    /**
     * @var string[]
     */
    private static $registries = [
        'robots' => RobotsRegistry::class,
        'metadata' => MetadataRegistry::class,
    ];

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $c = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(sprintf('%s/../Resources/config', __DIR__)));
        $loader->load('services.yaml');

        $this->setUpRegistries($container, $c);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function setUpRegistries(ContainerBuilder $container, array $config)
    {
        foreach (static::$registries as $name => $type) {
            if (isset($config[$name])) {
                $container->getDefinition($type)->addArgument($config[$name]);
            }
        }
    }
}
