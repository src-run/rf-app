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

class RouteGeneratorSimple implements RouteGeneratorInterface
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
        return 0;
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

        foreach ($this->getWorkingArgumentSets() as $arguments) {
            if (null !== $d = $this->handleWorkingArgumentSet($name, $route, $arguments)) {
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
    protected function handleWorkingArgumentSet(string $name, Route $route, array $arguments): ?UriDefinition
    {
        try {
            $a = isset($arguments['route_arguments']) ? $arguments['route_arguments'] : [];
            $d = new UriDefinition($this->router->generate($name, $a, RouterInterface::ABSOLUTE_URL));
        } catch (\Exception $e) {
            return null;
        }

        $this->assignChangeFrequency($d, $name, $route);
        $this->assignLastModified($d, $name, $route);
        $this->assignPriority($d, $name, $route);
        $this->assignDefinitionComment($d, $name, $route);

        return $d;
    }

    /**
     * @return \Generator
     */
    protected function getWorkingArgumentSets(): \Generator
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
