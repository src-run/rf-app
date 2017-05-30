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
use Rf\AppBundle\Tests\KernelTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArticleListControllerTest extends WebTestCase
{
    use AutoSetupTestTrait;
    use ClientTestTrait;
    use KernelTestTrait;

    public function testAction()
    {
        $this->assertPageIsValid('/article/list');
    }

    public function testActionPaginated()
    {
        foreach (range(0, 4) as $page) {
            $this->assertPageIsValid(sprintf('/article/list?page=%d', $page));
        }
    }

    /**
     * @param string $uri
     */
    private function assertPageIsValid(string $uri)
    {
        $client = static::createTestClient();

        $crawler = $client->request('GET', $uri);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(10, $crawler->filter('ul.article-list li'));
    }
}
