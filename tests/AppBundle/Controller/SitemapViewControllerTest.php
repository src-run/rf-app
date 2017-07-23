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

use Rf\AppBundle\Doctrine\Entity\Article;
use Rf\AppBundle\Doctrine\Repository\ArticleRepository;
use Rf\AppBundle\Tests\AutoSetupTestTrait;
use Rf\AppBundle\Tests\ClientTestTrait;
use Rf\AppBundle\Tests\CrawlerTestTrait;
use Rf\AppBundle\Tests\KernelTestTrait;
use Rf\AppBundle\Tests\RepositoryTestTrait;
use Rf\AppBundle\Tests\RouterTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\VarDumper\VarDumper;

class SitemapViewControllerTest extends WebTestCase
{
    use AutoSetupTestTrait;
    use ClientTestTrait;
    use CrawlerTestTrait;
    use KernelTestTrait;
    use RepositoryTestTrait;
    use RouterTestTrait;

    /**
     * @return \Generator
     */
    public static function provideActionViewSitemapRoute(): \Generator
    {
        yield ['app.sitemap_view_txt'];
        yield ['app.sitemap_view_xml'];
    }

    /**
     * @dataProvider provideActionViewSitemapRoute
     *
     * @param string $routeName
     */
    public function testActionViewSitemap(string $routeName)
    {
        $client = static::createTestClient();
        $client->request('GET', $this->generateRoute($routeName));

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        foreach ($this->getArticleLinks() as $link) {
            $this->assertContains($link, $client->getResponse()->getContent());
        }
    }

    /**
     * @return string[]
     */
    private function getArticleLinks(): array
    {
        $articles = $this->getArticles();
        $normLink = array_map(function (Article $article) {
            return $this->generateRoute('app.articles_view', [
                'year' => $article->getCreated()->format('Y'),
                'month' => $article->getCreated()->format('m'),
                'day' => $article->getCreated()->format('d'),
                'slug' => $article->getSlug(),
            ]);
        }, $articles);

        $permLink = array_map(function (Article $article) {
            return $this->generateRoute('app.articles_permalink', [
                'uuid' => $article->getUuid(),
            ]);
        }, $articles);

        return array_merge($normLink, $permLink);
    }

    /**
     * @return Article[]
     */
    private function getArticles()
    {
        $kernel = static::bootKernel();

        /** @var ArticleRepository $repository */
        $repository = $kernel
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Article::class);

        return $repository->findAll();
    }
}
