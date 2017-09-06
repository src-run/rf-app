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
use Rf\AppBundle\Doctrine\Entity\ArticleTag;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadArticleTagData implements DependentFixtureInterface, ContainerAwareInterface
{
    use DataFixturesFakerTrait;

    /**
     * @var int
     */
    private static $loadCount = 200;

    /**
     * @var int
     */
    private static $addMaxCount = 20;

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
            LoadArticleContentData::class
        ];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < static::$loadCount; $i++) {
            $tag = $this->createArticleTag();
            $manager->persist($tag);
        }

        $manager->flush();

        foreach ($this->getArticles($manager) as $article) {
            $article->setTags($this->getRandomTags($manager, mt_rand(1, static::$addMaxCount)));
            $manager->persist($article);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     * @return \Generator|Article[]
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
     * @return ArticleTag
     */
    private function createArticleTag(): ArticleTag
    {
        $nameWords = array_map(function (string $word) {
            return ucfirst($word);
        }, $this->getGenerator()->unique()->words(mt_rand(1, 3)));

        $name = implode(' ', $nameWords);

        $tag = new ArticleTag();
        $tag->setName($name);
        $tag->setDescription($this->getGenerator()->unique()->sentence);

        return $tag;
    }

    /**
     * @param ObjectManager $manager
     * @param int           $count
     *
     * @return ArticleTag[]
     */
    private function getRandomTags(ObjectManager $manager, int $count): array
    {
        $repo = $manager->getRepository(ArticleTag::class);
        $slugs = $repo->getSlugs();
        array_shift($slugs);
        $slugs = array_slice($slugs, 0, $count);

        return array_map(function (string $slug) use ($repo) {
            return $repo->findBySlug($slug);
        }, $slugs);
    }
}
