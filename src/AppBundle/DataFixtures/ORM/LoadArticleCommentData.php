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

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Rf\AppBundle\Doctrine\Entity\Article;
use Rf\AppBundle\Doctrine\Entity\ArticleComment;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadArticleCommentData implements DependentFixtureInterface, ContainerAwareInterface
{
    use DataFixturesFakerTrait;

    /**
     * @var int
     */
    private static $loadMaxCount = 20;

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
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            LoadArticleContentData::class,
        ];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getArticles($manager) as $article) {
            for ($i = 0; $i < mt_rand(0, static::$loadMaxCount); ++$i) {
                $comment = $this->createArticleComment($article);
                $manager->persist($comment);
            }
        }

        $manager->flush();

        foreach ($this->getArticleComments($manager) as $comment) {
            $manager->persist($this->updateArticleComment($comment));
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     * @return \Generator|Article
     */
    private function getArticles(ObjectManager $manager): \Generator
    {
        $repository = $manager->getRepository(Article::class);
        $uuids = $repository->getUuids();

        foreach ($uuids as $uuid) {
            yield $repository->findByUuid($uuid);
        }
    }

    /**
     * @param ObjectManager $manager
     *
     * @return \Generator|ArticleComment
     */
    private function getArticleComments(ObjectManager $manager): \Generator
    {
        $repository = $manager->getRepository(ArticleComment::class);
        $uuids = $repository->getUuids();

        foreach ($uuids as $uuid) {
            yield $repository->findByUuid($uuid);
        }
    }

    /**
     * @param Article $article
     *
     * @return ArticleComment
     */
    private function createArticleComment(Article $article): ArticleComment
    {
        $comment = new ArticleComment();
        $comment->setTitle(ucwords(trim($this->getGenerator()->sentence(mt_rand(3, 20)), '.')));
        $comment->setContent($this->createArticleContent());
        $comment->setAuthorEmail($this->getGenerator()->email);
        $comment->setAuthorName($this->getGenerator()->name);
        $comment->setArticle($article);

        return $comment;
    }

    /**
     * @param ArticleComment $comment
     *
     * @return ArticleComment
     */
    private function updateArticleComment(ArticleComment $comment): ArticleComment
    {
        $comment->setCreated($this->getGenerator()->dateTimeBetween('-2 years', 'now'));

        return $comment;
    }

    /**
     * @return string
     */
    private function createArticleContent(): string
    {
        $lines = [];

        $this->fakeSentences($lines, mt_rand(1, 8), 0.25);
        $this->fakeUnorderedList($lines, mt_rand(5, 12), mt_rand(2, 4));
        if (0 === mt_rand(0, 10000) % 5) {
            $this->fakeCodeBlock($lines);
        }
        $this->fakeSentences($lines, mt_rand(1, 8), 0.25);

        return implode("\n", $lines);
    }
}
