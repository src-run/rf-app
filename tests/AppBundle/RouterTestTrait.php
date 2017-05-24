<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Tests;

use Symfony\Component\Routing\Router;

trait RouterTestTrait
{
    /**
     * @var Router
     */
    private $router;

    private function autoSetUp100Router()
    {
        $this->router = static::$kernel->getContainer()
            ->get('router');
    }
}
