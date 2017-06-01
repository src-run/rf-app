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
use Rf\AppBundle\Tests\RouterTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ArticleListControllerTest extends WebTestCase
{
    use AutoSetupTestTrait;
    use ClientTestTrait;
    use CrawlerTestTrait;
    use KernelTestTrait;
    use RouterTestTrait;

    public function testAction()
    {
        $this->assertValidUrl($this->generateRoute('app.articles_list'));
    }

    /**
     * @return int[]
     */
    public static function dataPageProvider(): array
    {
        return array_map(function (int $page) {
            return [$page];
        }, [0, 1, 2, 3, 4]);
    }

    /**
     * @param int $page
     *
     * @dataProvider dataPageProvider
     */
    public function testActionPaginated(int $page)
    {
        $this->assertValidUrl(sprintf('%s?page=%d', $this->generateRoute('app.articles_list'), $page));
    }
}
