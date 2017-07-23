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
use Symfony\Component\Routing\Route;

interface RouteHandlerInterface
{
    /**
     * @param string $name
     * @param Route  $route
     *
     * @return UriCollection|null
     */
    public function handle(string $name, Route $route): ?UriCollection;

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @param string $name
     * @param Route  $route
     *
     * @return bool
     */
    public function isSupported(string $name, Route $route): bool;
}
