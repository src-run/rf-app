<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Rf\AppBundle\Doctrine\Entity\Article;
use Rf\AppBundle\Doctrine\Repository\ArticleRepository;
use Rf\AppBundle\Subscriber\DoctrineLoggableSubscriber;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LoadArticleContentData implements FixtureInterface, ContainerAwareInterface
{
    use DataFixturesFakerTrait;

    /**
     * @var int
     */
    private static $loadCount = 80;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->setupLoggableUsernameListener();
    }

    private function setupLoggableUsernameListener(): void
    {
        $kernel = $this->container->get('http_kernel');
        $request = new Request();
        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        /** @var DoctrineLoggableSubscriber $subscriber */
        $subscriber = $this->container->get('Rf\AppBundle\Subscriber\DoctrineLoggableSubscriber');
        $subscriber->setDefaultUser('system:cli');
        $subscriber->assignActiveUserToLogListener($event);
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < static::$loadCount; ++$i) {
            $article = $this->createArticle();
            $manager->persist($article);
        }

        $manager->flush();

        for ($i = 0; $i < static::$loadCount; $i = (int) $i + mt_rand(0, static::$loadCount / 10)) {
            $article = $this->updateArticle($i);
            $manager->persist($article);
        }

        $manager->flush();
    }

    /**
     * @return Article[]
     */
    private function getAllArticles(): array
    {
        return $this->getArticleRepository()->findAll();
    }

    /**
     * @return ArticleRepository
     */
    private function getArticleRepository(): ArticleRepository
    {
        return $this->container
            ->get('doctrine')
            ->getManager()
            ->getRepository(Article::class);
    }

    /**
     * @param int $index
     *
     * @return Article
     */
    private function updateArticle(int $index): Article
    {
        static $articleCollection = null;

        if ($articleCollection === null) {
            $articleCollection = $this->getAllArticles();
        }

        $article = $articleCollection[$index];
        $article->setTitle(ucwords(trim($this->getGenerator()->sentence(8), '.')));
        $article->setContent($this->createArticleContent());

        return $article;
    }

    /**
     * @return Article
     */
    private function createArticle(): Article
    {
        $article = new Article();
        $article->setTitle(ucwords(trim($this->getGenerator()->sentence(8), '.')));
        $article->setContent($this->createArticleContent());

        return $article;
    }

    /**
     * @return string
     */
    private function createArticleContent(): string
    {
        $lines = [];

        $this->fakeHeader($lines, 1);
        $this->fakeSentences($lines, mt_rand(8, 14));

        $this->fakeHeader($lines, 2);
        $this->fakeSentences($lines, mt_rand(2, 4), mt_rand(1, 2));
        $this->fakeUnorderedList($lines, mt_rand(5, 12), mt_rand(2, 4));
        $this->fakeSentences($lines, mt_rand(2, 3), mt_rand(1, 3));

        $this->fakeHeader($lines, 2);
        $this->fakeSentences($lines, mt_rand(4, 8), mt_rand(1, 6));
        $this->fakeCodeBlock($lines);
        $this->fakeSentences($lines, mt_rand(1, 2));

        $this->fakeHeader($lines, 2);
        $this->fakeSentences($lines, mt_rand(1, 2), mt_rand(2, 3));
        $this->fakeOrderedList($lines, mt_rand(10, 20), 3);
        $this->fakeSentences($lines, mt_rand(4, 6), mt_rand(1, 2));
        $this->fakeUnorderedList($lines, mt_rand(4, 8), 1);
        $this->fakeSentences($lines, mt_rand(4, 6), mt_rand(1, 2));

        $this->fakeHeader($lines, 3);
        $this->fakeSentences($lines, mt_rand(4, 6), mt_rand(1, 2));
        $this->fakeUnorderedList($lines, mt_rand(4, 8), 1);
        $this->fakeSentences($lines, mt_rand(1, 2), mt_rand(2, 3));
        $this->fakeOrderedList($lines, mt_rand(10, 20), 3);
        $this->fakeSentences($lines, mt_rand(4, 6), mt_rand(1, 2));

        $this->fakeHeader($lines, 2);
        $this->fakeSentences($lines, mt_rand(20, 100), mt_rand(1, 2));

        return implode("\n", $lines);
    }
}
