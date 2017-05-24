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

trait AutoSetupTestTrait
{
    protected function setUp()
    {
        foreach ($this->getAutoMethodReflections() as $m) {
            $m->setAccessible(true);
            $m->invoke($this);
        }
    }

    /**
     * @return \ReflectionMethod[]
     */
    private function getAutoMethodReflections(): array
    {
        $methods = array_filter((new \ReflectionObject($this))->getMethods(), function (\ReflectionMethod $method) {
            return strpos($method->getShortName(), 'autoSetUp') === 0;
        });

        usort($methods, function (\ReflectionMethod $a, \ReflectionMethod $b) {
            return $a > $b;
        });

        return $methods;
    }
}
