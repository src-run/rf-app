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

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpKernel\Kernel;

trait ClientTestTrait
{
    /**
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     *
     * @return Client
     */
    private static function createTestClient(array $options = array(), array $server = array())
    {
        /** @var Kernel $kernel */
        $kernel = static::bootKernel($options);

        /** @var Client $client */
        $client = $kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }
}
