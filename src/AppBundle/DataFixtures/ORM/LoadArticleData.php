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
use Rf\AppBundle\Doctrine\Repository\ArticleRepository;
use SR\Util\Transform\StringTransform;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\VarDumper\VarDumper;

class LoadArticleData implements FixtureInterface, ContainerAwareInterface
{
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
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < static::$loadCount; $i++) {
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
     * @return Generator
     */
    private function getGenerator(): Generator
    {
        static $generator = null;

        if ($generator === null) {
            $generator = Factory::create();
        }

        return $generator;
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

        $this->addArticleHeader($lines, 1);
        $this->addArticleSentences($lines, mt_rand(8, 14));

        $this->addArticleHeader($lines, 2);
        $this->addArticleSentences($lines, mt_rand(2, 4), mt_rand(1, 2));
        $this->addArticleUnorderedList($lines, mt_rand(5, 12), mt_rand(2, 4));
        $this->addArticleSentences($lines, mt_rand(2, 3), mt_rand(1, 3));

        $this->addArticleHeader($lines, 2);
        $this->addArticleSentences($lines, mt_rand(4, 8), mt_rand(1, 6));
        $this->addArticleCodeBlock($lines);
        $this->addArticleSentences($lines, mt_rand(1, 2));

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
        foreach (range(0, $count) as $i) {
            $lines[] = $this->getGenerator()->sentence(mt_rand(20 * $multiplier, 100 * $multiplier));
            $this->addArticleEmptyLine($lines);
        }
    }

    /**
     * @param array $lines
     * @param int   $count
     * @param int   $multiplier
     */
    private function addArticleUnorderedList(array &$lines, $count, $multiplier = 1)
    {
        foreach (range(1, $count) as $i) {
            $lines[] = sprintf('- %s', $this->getGenerator()->sentence(5 * $multiplier, 15 * $multiplier));
        }

        $this->addArticleEmptyLine($lines);
    }

    /**
     * @param array $lines
     * @param int   $count
     * @param int   $multiplier
     */
    private function addArticleOrderedList(array &$lines, $count, $multiplier = 1)
    {
        foreach (range(1, $count) as $i) {
            $lines[] = sprintf('%d. %s', $count, $this->getGenerator()->sentence(8 * $multiplier, 24 * $multiplier));
        }

        $this->addArticleEmptyLine($lines);
    }

    /**
     * @param array $lines
     * @param int   $level
     */
    private function addArticleHeader(array &$lines, $level = 1)
    {
        $this->addArticleEmptyLine($lines);
        $lines[] = vsprintf('%s %s', [
            str_repeat('#', $level),
            ucwords(trim($this->getGenerator()->sentence(mt_rand(4, 20)), '.')),
        ]);
        $this->addArticleEmptyLine($lines);
    }

    /**
     * @param array $lines
     */
    private function addArticleCodeBlock(array &$lines)
    {
        $this->addArticleEmptyLine($lines);

        $finder = Finder::create()
            ->in(__DIR__.'/../../')
            ->name('*.php')
            ->files();

        $collection = [];
        foreach ($finder as $f) {
            $collection[] = $f;
        }

        /** @var SplFileInfo $file */
        $file = $collection[mt_rand(0, count($collection)-1)];

        $lines = array_merge($lines, [
            '```php',
            sprintf('# %s', $file->getRealPath()),
            ''
        ], explode(PHP_EOL, file_get_contents($file->getRealPath())), [
            '````'
        ]);

        $this->addArticleEmptyLine($lines);
    }

    private function addArticleEmptyLine(array &$lines)
    {
        $lines[] = '';
    }
}
