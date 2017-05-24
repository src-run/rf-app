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
use Rf\AppBundle\Tests\KernelTestTrait;
use Rf\AppBundle\Tests\RepositoryTestTrait;
use Rf\AppBundle\Tests\RouterTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class ArticleViewControllerTest extends WebTestCase
{
    use AutoSetupTestTrait;
    use ClientTestTrait;
    use KernelTestTrait;
    use RepositoryTestTrait;
    use RouterTestTrait;

    public function testActionNotFound()
    {
        $uri = $this->router->getGenerator()->generate('app.article_view', [
            'year' => '1200',
            'month' => '01',
            'day' => '01',
            'slug' => 'does-not-exist',
        ], RouterInterface::RELATIVE_PATH);

        $client = static::createTestClient();
        $client->request('GET', $uri);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     */
    public function testActionMissingRequestAttributes()
    {
        $uri = $this->router->getGenerator()->generate('app.article_view', [], RouterInterface::RELATIVE_PATH);

        $client = static::createTestClient();
        $client->request('GET', $uri);
        $client->getResponse();
    }

    public function testAction()
    {
        foreach ($this->repository->findAll() as $entity) {
            $this->assertEntityRouteExists($entity);
        }
    }

    /**
     * @param Article $article
     */
    private function assertEntityRouteExists(Article $article)
    {
        $uri = $this->router->getGenerator()->generate('app.article_view', [
            'year' => $article->getCreatedOn()->format('Y'),
            'month' => $article->getCreatedOn()->format('m'),
            'day' => $article->getCreatedOn()->format('d'),
            'slug' => $article->getSlug(),
        ], RouterInterface::RELATIVE_PATH);

        $this->assertPageIsValid($uri);
    }

    /**
     * @param string $uri
     */
    private function assertPageIsValid(string $uri)
    {
        $client = static::createTestClient();

        $crawler = $client->request('GET', $uri);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(2, $crawler->filter('h1'));
        $this->assertCount(5, $crawler->filter('h2'));
        $this->assertCount(1, $crawler->filter('h3'));
    }
}
