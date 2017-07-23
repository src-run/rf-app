<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Sitemap\Route;

use Rf\AppBundle\Component\Sitemap\Uri\UriCollection;
use Rf\AppBundle\Component\Sitemap\Uri\UriDefinition;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class RouteHandlerDefault implements RouteHandlerInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $name
     * @param Route  $route
     *
     * @return bool
     */
    public function isSupported(string $name, Route $route): bool
    {
        return true;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return -255;
    }

    /**
     * @param string $name
     * @param Route  $route
     *
     * @return UriCollection|null
     */
    public function handle(string $name, Route $route): ?UriCollection
    {
        $collection = new UriCollection();

        foreach ($this->getArgumentSets() as $arguments) {
            if (null !== $d = $this->handleArgumentSet($name, $route, $arguments)) {
                $collection->add($d);
            }
        }

        return $collection;
    }

    /**
     * @param string $name
     * @param Route  $route
     * @param array  $arguments
     *
     * @return null|UriDefinition
     */
    protected function handleArgumentSet(string $name, Route $route, array $arguments): ?UriDefinition
    {
        $routeArguments = isset($arguments['route_arguments']) ? $arguments['route_arguments'] : [];

        try {
            $uri = new UriDefinition($this->router->generate($name, $routeArguments, RouterInterface::ABSOLUTE_URL));
        } catch (\Exception $e) {
            return null;
        }

        $this->assignChangeFrequency($uri, $name, $route);
        $this->assignLastModified($uri, $name, $route);
        $this->assignPriority($uri, $name, $route);
        $this->assignDefinitionComment($uri, $name, $route);

        return $uri;
    }

    /**
     * @return \Generator
     */
    protected function getArgumentSets(): \Generator
    {
        yield [];
    }

    /**
     * @param UriDefinition $definition
     * @param string        $name
     * @param Route         $route
     */
    protected function assignDefinitionComment(UriDefinition $definition, string $name, Route $route): void
    {
        $definition->setComment(sprintf('route: %s (%s)', $name, get_called_class()));
    }

    /**
     * @param UriDefinition $definition
     * @param string        $name
     * @param Route         $route
     */
    protected function assignChangeFrequency(UriDefinition $definition, string $name, Route $route): void
    {
        $definition->setChangeFrequency(UriDefinition::CHANGE_FREQ_DAILY);
    }

    /**
     * @param UriDefinition $definition
     * @param string        $name
     * @param Route         $route
     */
    protected function assignLastModified(UriDefinition $definition, string $name, Route $route): void
    {
        $definition->setLastModified(new \DateTime('-1 minute'));
    }

    /**
     * @param UriDefinition $definition
     * @param string        $name
     * @param Route         $route
     */
    protected function assignPriority(UriDefinition $definition, string $name, Route $route): void
    {
        foreach (['robots', 'sitemap'] as $lowPriority) {
            if (false !== strpos($name, $lowPriority)) {
                $definition->setPriority(0.4);
                return;
            }
        }

        foreach (['articles'] as $highPriority) {
            if (false !== strpos($name, $highPriority)) {
                $definition->setPriority(0.6);
                return;
            }
        }

        $definition->setPriority(0.5);
    }
}
