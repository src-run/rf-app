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

class ArticleViewControllerTest extends WebTestCase
{
    use AutoSetupTestTrait;
    use ClientTestTrait;
    use CrawlerTestTrait;
    use KernelTestTrait;
    use RepositoryTestTrait;
    use RouterTestTrait;

    public function testActionNotFound()
    {
        $client = static::createTestClient();
        $client->request('GET', $this->generateRoute('app.articles_view', [
            'year' => '1200',
            'month' => '01',
            'day' => '01',
            'slug' => 'does-not-exist',
        ]));

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     */
    public function testActionMissingRequestAttributes()
    {
        $client = static::createTestClient();
        $client->request('GET', $this->generateRoute('app.articles_view'));
        $client->getResponse();
    }

    public static function provideArticleData()
    {
        $kernel = static::bootKernel();

        /** @var ArticleRepository $repository */
        $repository = $kernel
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Article::class);

        return array_map(function (Article $article) {
            return [$article];
        }, $repository->findAll());
    }

    /**
     * @param Article $article
     *
     * @dataProvider provideArticleData
     */
    public function testAction(Article $article)
    {
        $this->assertEntityRouteExists($article);
    }

    /**
     * @param Article $article
     *
     * @dataProvider provideArticleData
     */
    public function testPermalinkAction(Article $article)
    {
        $this->assertEntityRouteExists($article);
    }

    /**
     * @param Article $article
     */
    private function assertEntityRouteExists(Article $article)
    {
        $this->assertValidUrl($this->generateRoute('app.articles_view', [
            'year' => $article->getCreated()->format('Y'),
            'month' => $article->getCreated()->format('m'),
            'day' => $article->getCreated()->format('d'),
            'slug' => $article->getSlug(),
        ], RouterInterface::RELATIVE_PATH));
    }

    /**
     * @param Article $article
     */
    private function assertEntityPermalinkRouteExists(Article $article)
    {
        $this->assertValidUrl($this->generateRoute('app.articles_permalink', [
            'uuid' => $article->getIdentity(),
        ], RouterInterface::RELATIVE_PATH));
    }
}
