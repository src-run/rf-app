<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Sitemap;

use Doctrine\ORM\EntityManager;
use Psr\Cache\CacheItemPoolInterface;
use Rf\AppBundle\Component\Environment\EnvironmentInterface;
use Rf\AppBundle\Component\Sitemap\Route\RouteGeneratorInterface;
use Rf\AppBundle\Component\Sitemap\Uri\UriCollection;
use Rf\AppBundle\Component\Sitemap\Uri\UriDefinition;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\VarDumper\VarDumper;

class RecordGenerator
{
    /**
     * @var string
     */
    private static $ignoreRouteNamesRegex = '{app\.(redirect_|robots_)}i';

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * @var RouteGeneratorInterface[]
     */
    private $routeGenerators = [];

    /**
     * @var Route[]
     */
    private static $routesS = [];

    /**
     * @var Route[]
     */
    private static $routesV = [];

    /**
     * @param RouterInterface        $router
     * @param EntityManager          $em
     * @param CacheItemPoolInterface $cache
     * @param EnvironmentInterface   $environment
     */
    public function __construct(RouterInterface $router, EntityManager $em, CacheItemPoolInterface $cache, EnvironmentInterface $environment)
    {
        $this->router = $router;
        $this->em = $em;
        $this->cache = $cache;
        $this->environment = $environment;
    }

    /**
     * @param RouteGeneratorInterface[] ...$routeGenerators
     */
    public function addRouteGenerators(RouteGeneratorInterface ...$routeGenerators): void
    {
        foreach ($routeGenerators as $g) {
            if (!in_array($g, $this->routeGenerators)) {
                $this->routeGenerators[] = $g;
            }
        }

        usort($this->routeGenerators, function (RouteGeneratorInterface $a, RouteGeneratorInterface $b) {
            return $a->getPriority() < $b->getPriority();
        });
    }

    /**
     * @return UriCollection
     */
    public function generate(): UriCollection
    {
        $item = $this->cache->getItem($this->getCacheKey());

        if ($this->environment->isDevelopment() || !$item->isHit()) {
            $collection = new UriCollection();

            foreach ($this->getCollectionGroups() as $c) {
                $collection->merge($c);
            }

            $collection->sort(function (UriDefinition $a, UriDefinition $b) {
                return $a->getPriority() < $b->getPriority();
            });

            $item->set($collection);
            $item->expiresAfter(new \DateInterval('P1D'));
            $this->cache->save($item);
        }

        return $item->get();
    }

    /**
     * @return UriCollection[]|\Generator
     */
    private function getCollectionGroups(): \Generator
    {
        foreach ($this->getApplicationRoutes() as $name => $route) {
            if (null !== $d = $this->getCollectionForRoute($name, $route)) {
                yield $d;
            }
        }
    }

    /**
     * @param string $name
     * @param Route  $route
     *
     * @return UriCollection|null
     */
    private function getCollectionForRoute(string $name, Route $route): ?UriCollection
    {
        foreach ($this->routeGenerators as $generator) {
            if (!$generator->isSupported($name, $route)) {
                continue;
            }

            try {
                return $generator->handle($name, $route);
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * @return Route[]
     */
    private function getApplicationRoutes(): array
    {
        return array_filter($this->router->getRouteCollection()->all(), function (string $name) {
            return 0 === strpos($name, 'app.') && 0 === preg_match(static::$ignoreRouteNamesRegex, $name);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return string
     */
    private function getCacheKey(): string
    {
        return preg_replace('{[^0-9a-z_-]}i', '_', strtolower(vsprintf('_%s----%s----%s', [
            __METHOD__,
            realpath(__DIR__),
            __DIR__,
        ])));
    }
}
