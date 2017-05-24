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
use Faker\Factory;
use Faker\Generator;
use Rf\AppBundle\Doctrine\Entity\Article;
use SR\Util\Transform\StringTransform;

class LoadArticleData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        foreach (range(0, mt_rand(100, 200)) as $i) {
            $article = $this->createArticle();
            $manager->persist($article);
        }

        $manager->flush();
    }

    /**
     * @return Article
     */
    private function createArticle(): Article
    {
        $generator = Factory::create();

        $article = new Article();
        $article->setTitle(ucwords(trim($generator->sentence(8), '.')));
        $article->setSlug($this->createArticleSlug($article));
        $article->setCreatedOn($date = $generator->dateTimeBetween('-1 year', 'now'));
        $article->setUpdatedOn($date);
        $article->setContent($this->createArticleContent($generator));

        return $article;
    }

    /**
     * @param Article $article
     *
     * @return string
     */
    private function createArticleSlug(Article $article)
    {
        return (new StringTransform($article->getTitle()))->toAlphanumericAndSpacesToDashes()->toLower()->get();
    }

    /**
     * @param Generator $generator
     *
     * @return string
     */
    private function createArticleContent(Generator $generator): string
    {
        $lines = [];
        $this->addArticleHeader($lines, 1);
        $this->addArticleSentences($lines, mt_rand(8, 14));

        $this->addArticleHeader($lines, 2);
        $this->addArticleSentences($lines, mt_rand(2, 4), mt_rand(1, 2));
        $this->addArticleUnorderedList($lines, mt_rand(5, 12), mt_rand(2, 4));
        $this->addArticleSentences($lines, mt_rand(2, 3), mt_rand(1, 3));

        $this->addArticleHeader($lines, 2);
        $this->addArticleSentences($lines, mt_rand(4, 8), mt_rand(1, 6));

        $this->addArticleHeader($lines, 2);
        $this->addArticleSentences($lines, mt_rand(1, 2), mt_rand(2, 3));
        $this->addArticleOrderedList($lines, mt_rand(10, 20), 3);
        $this->addArticleSentences($lines, mt_rand(4, 6), mt_rand(1, 2));
        $this->addArticleUnorderedList($lines, mt_rand(4, 8), 1);
        $this->addArticleSentences($lines, mt_rand(4, 6), mt_rand(1, 2));

        $this->addArticleHeader($lines, 3);
        $this->addArticleSentences($lines, mt_rand(4, 6), mt_rand(1, 2));
        $this->addArticleUnorderedList($lines, mt_rand(4, 8), 1);
        $this->addArticleSentences($lines, mt_rand(1, 2), mt_rand(2, 3));
        $this->addArticleOrderedList($lines, mt_rand(10, 20), 3);
        $this->addArticleSentences($lines, mt_rand(4, 6), mt_rand(1, 2));

        $this->addArticleHeader($lines, 2);
        $this->addArticleSentences($lines, mt_rand(20, 100), mt_rand(1, 2));

        return implode("\n", $lines);
    }

    /**
     * @param array $lines
     * @param int   $count
     * @param int   $multiplier
     */
    private function addArticleSentences(array &$lines, $count, $multiplier = 1)
    {
        $generator = Factory::create();

        foreach (range(0, $count) as $i) {
            $lines[] = $generator->sentence(mt_rand(20 * $multiplier, 100 * $multiplier));
            $lines[] = '';
        }
    }

    /**
     * @param array $lines
     * @param int   $count
     * @param int   $multiplier
     */
    private function addArticleUnorderedList(array &$lines, $count, $multiplier = 1)
    {
        $generator = Factory::create();

        foreach (range(1, $count) as $i) {
            $lines[] = sprintf('- %s', $generator->sentence(5 * $multiplier, 15 * $multiplier));
        }

        $lines[] = '';
    }

    /**
     * @param array $lines
     * @param int   $count
     * @param int   $multiplier
     */
    private function addArticleOrderedList(array &$lines, $count, $multiplier = 1)
    {
        $generator = Factory::create();

        foreach (range(1, $count) as $i) {
            $lines[] = sprintf('%d. %s', $count, $generator->sentence(8 * $multiplier, 24 * $multiplier));
        }

        $lines[] = '';
    }

    /**
     * @param array $lines
     * @param int   $level
     */
    private function addArticleHeader(array &$lines, $level = 1)
    {
        $generator = Factory::create();

        $lines[] = '';
        $lines[] = vsprintf('%s %s', [
            str_repeat('#', $level),
            ucwords(trim($generator->sentence(mt_rand(4, 20)), '.')),
        ]);
        $lines[] = '';
    }
}
