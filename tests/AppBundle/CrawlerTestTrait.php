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

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Client;

trait CrawlerTestTrait
{
    /**
     * @param string $url
     *
     * @return Crawler
     */
    private function getClientRequestCrawler(string $url): Crawler
    {
        return static::createTestClient()->request('GET', $url);
    }

    /**
     * @param string $url
     */
    private function assertValidUrl(string $url)
    {
        $client = static::createTestClient();
        $client->request('GET', $url);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @param array $options
     * @param array $server
     *
     * @return Client
     */
    abstract static function createTestClient(array $options = array(), array $server = array());
}
