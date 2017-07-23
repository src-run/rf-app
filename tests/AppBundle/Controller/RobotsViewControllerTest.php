<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Tests\Controller;

use Rf\AppBundle\Tests\AutoSetupTestTrait;
use Rf\AppBundle\Tests\ClientTestTrait;
use Rf\AppBundle\Tests\CrawlerTestTrait;
use Rf\AppBundle\Tests\KernelTestTrait;
use Rf\AppBundle\Tests\RepositoryTestTrait;
use Rf\AppBundle\Tests\RouterTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RobotsViewControllerTest extends WebTestCase
{
    use AutoSetupTestTrait;
    use ClientTestTrait;
    use CrawlerTestTrait;
    use KernelTestTrait;
    use RepositoryTestTrait;
    use RouterTestTrait;

    public function testActionViewRobots()
    {
        $client = static::createTestClient();
        $client->request('GET', $this->generateRoute('app.robots_view'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testActionViewRobotsRoot()
    {
        $client = static::createTestClient();
        $client->request('GET', $this->generateRoute('app.robots_root'));

        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }
}
