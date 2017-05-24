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

trait KernelTestTrait
{
    private function autoSetUp001Kernel()
    {
        static::bootKernel([
            'environment' => 'test',
            'debug' => false,
        ]);
    }
}
