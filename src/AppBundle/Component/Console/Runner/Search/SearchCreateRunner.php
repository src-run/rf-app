<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Component\Console\Runner\Search;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Psr\Cache\CacheItemPoolInterface;
use Rf\AppBundle\Component\Console\Runner\AbstractRunner;
use Rf\AppBundle\Doctrine\Entity\Article;
use Rf\AppBundle\Doctrine\Entity\SearchIndex;
use Rf\AppBundle\Doctrine\Entity\SearchStem;
use Rf\AppBundle\Doctrine\Repository\ArticleRepository;
use Rf\AppBundle\Doctrine\Repository\SearchIndexRepository;
use Rf\AppBundle\Doctrine\Repository\SearchStemRepository;
use SR\Cocoa\Word\Stem\Stemmer;
use SR\Cocoa\Word\Stop\Stopwords;
use SR\Console\Output\Style\StyleInterface;
use SR\Doctrine\ORM\Mapping\Entity;

class SearchCreateRunner extends AbstractRunner
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    /**
     * @var SearchStemRepository
     */
    private $searchStemRepository;

    /**
     * @var SearchIndexRepository
     */
    private $searchIndexRepository;

    /**
     * @var Stemmer
     */
    private $stemmer;

    /**
     * @var Stopwords
     */
    private $stopwords;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var bool
     */
    private $cacheEnabled = true;

    /**
     * @param EntityManagerInterface $em
     * @param ArticleRepository      $articleRepository
     * @param SearchStemRepository   $searchStemRepository
     * @param SearchIndexRepository  $searchIndexRepository
     * @param Stemmer                $stemmer
     * @param Stopwords              $stopwords
     */
    public function __construct(EntityManagerInterface $em, ArticleRepository $articleRepository, SearchStemRepository $searchStemRepository, SearchIndexRepository $searchIndexRepository, Stemmer $stemmer, Stopwords $stopwords, CacheItemPoolInterface $cache)
    {
        parent::__construct(null);

        $this->em = $em;
        $this->articleRepository = $articleRepository;
        $this->searchStemRepository = $searchStemRepository;
        $this->searchIndexRepository = $searchIndexRepository;
        $this->stemmer = $stemmer;
        $this->stopwords = $stopwords;
        $this->cache = $cache;
    }

    /**
     * @param bool $cacheEnabled
     */
    public function setCacheEnabled(bool $cacheEnabled): void
    {
        $this->cacheEnabled = $cacheEnabled;
    }

    public function run()
    {
        $this->io->section('Resolving Article Stems and Indexes');

        $articleList = $this->articleRepository->findAllOrderByCreated();
        $articleSize = count($articleList);

        for ($i = 0; $i < $articleSize; $i++) {
            $this->io->subSection(sprintf("%'.04d. Processing Article %s (%s)", $i + 1, $articleList[$i]->getUuid(), $articleList[$i]->getTitle()));
            $this->persistIndexes($articleList[$i], ...$this->persistStems($articleList[$i]));
        }
    }

    /**
     * @param Article $article
     *
     * @return array
     */
    private function stemArticle(Article $article): array
    {
        $item = $this->cache->getItem(vsprintf('%s_%s_%s', [
            spl_object_id($this),
            hash('sha256', $article->getSlug().$article->getCreated()->format('r')),
            hash('sha256', $article->getContent())
        ]));

        if ($this->cacheEnabled && $item->isHit()) {
            $this->io->write('...');
            return $item->get();
        }

        $stems = $this->stemmer->stem(
            preg_replace('{[^^0-9a-z\' ]}i', '', $article->getContent())
        );

        $this->io->write(sprintf('(%d) ...', count($stems)));

        $stems = array_map(function(string $s) {
            return strtolower($s);
        }, $stems);

        $stems = array_filter($stems, function (string $s) {
            return strlen($s) > 2 && 1 === preg_match('{^[a-z]+$}', $s);
        });

        $stems = array_map(function (string $name) {
            return $this->resolveSearchStemEntity($name);
        }, $this->stopwords->sanitize($stems));

        $item->set($stems);
        $this->cache->save($item);

        return array_filter($stems, function (SearchStem $stem) {
            return null !== $stem->getStem();
        });
    }

    /**
     * @param Article $article
     *
     * @return SearchStem[]
     */
    private function persistStems(Article $article): array
    {
        $this->io->write('- Stemming the content ');

        $stems = $this->stemArticle($article);

        $this->emFlush(...$stems);
        $this->io->writeln([' OK']);

        return $stems;
    }

    /**
     * @param Article      $article
     * @param SearchStem[] ...$stems
     */
    private function persistIndexes(Article $article, SearchStem ...$stems)
    {
        $this->io->write(sprintf('- Creating the indexes (%d) ...', count($stems)));

        $work = [];
        foreach ($stems as $position => $stem) {
            $this->io
                ->environment(StyleInterface::VERBOSITY_VERY_VERBOSE)
                ->write('.');

            if (null !== $this->searchIndexRepository->findByArticleStemPosition($article, $stem, $position)) {
                continue;
            }

            $index = new SearchIndex();
            $index->setArticle($article);
            $index->setStem($stem);
            $index->setPosition($position);

            try {
                $this->em->persist($index);
            } catch (\Exception $e) {
                $this->io->warning(sprintf('Could not persist index: %s', var_export($index, true)));
            }

            if ($position % 100 === 0) {
                $this->emFlush(...$work);
                $work = [];
            } else {
                $work[] = $index;
            }
        }

        $this->emFlush($article);
        $this->io->writeln([' OK']);
    }

    /**
     * @param Entity[] ...$entities
     */
    private function emFlush(Entity ...$entities): void
    {
        try {
            $this->em->flush();
        } catch (ORMException $e) {
            $this->io->warning(sprintf('Could not flush entity manager: %s', $e->getMessage()));
        }
    }

    /**
     * @param string $name
     *
     * @return SearchStem
     */
    private function resolveSearchStemEntity(string $name): SearchStem
    {
        $this->io
            ->environment(StyleInterface::VERBOSITY_VERY_VERBOSE)
            ->write('.');

        $item = $this->cache->getItem(sprintf('stem-entity-%s', hash('sha256', $name)));

        if (!$item->isHit()) {
            if (null === $stem = $this->searchStemRepository->findByStem($name)) {
                $stem = new SearchStem();
                $stem->setStem($name);
                $this->em->persist($stem);
            }

            $item->set($stem);
            $item->expiresAfter(new \DateInterval('PT1M'));
            $this->cache->save($item);
        }

        return $item->get();
    }
}
