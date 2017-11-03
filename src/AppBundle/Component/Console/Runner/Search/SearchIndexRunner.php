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
use Rf\AppBundle\Component\Console\IO\ActionIO;
use Rf\AppBundle\Component\Console\Runner\AbstractRunner;
use Rf\AppBundle\Component\Search\Indexing\EntityProviderInterface;
use Rf\AppBundle\Component\Search\Indexing\Model\IndexableEntity;
use Rf\AppBundle\Doctrine\Entity\SearchIndex;
use Rf\AppBundle\Doctrine\Entity\SearchIndexLog;
use Rf\AppBundle\Doctrine\Entity\SearchStem;
use Rf\AppBundle\Doctrine\Repository\SearchIndexLogRepository;
use Rf\AppBundle\Doctrine\Repository\SearchIndexRepository;
use Rf\AppBundle\Doctrine\Repository\SearchStemRepository;
use SR\Cocoa\Word\Stem\Stemmer;
use SR\Cocoa\Word\Stop\Stopwords;
use SR\Console\Output\Style\StyleInterface;
use SR\Doctrine\ORM\Mapping\Entity;

final class SearchIndexRunner extends AbstractSearchRunner
{
    /**
     * @var Stemmer
     */
    private $stemWords;

    /**
     * @var Stopwords
     */
    private $stopWords;

    /**
     * @var ActionIO
     */
    private $actionIO;

    /**
     * @var EntityProviderInterface[]|iterable
     */
    private $providers;

    /**
     * @var bool
     */
    private $cache = true;

    /**
     * @param Stemmer                  $stemWords
     * @param Stopwords                $stopWords
     * @param EntityManagerInterface   $em
     * @param SearchStemRepository     $wordStemsRepo
     * @param SearchIndexRepository    $indexMapsRepo
     * @param SearchIndexLogRepository $indexLogsRepo
     */
    public function __construct(Stemmer $stemWords, Stopwords $stopWords, EntityManagerInterface $em, SearchStemRepository $wordStemsRepo, SearchIndexRepository $indexMapsRepo, SearchIndexLogRepository $indexLogsRepo)
    {
        parent::__construct($em, $wordStemsRepo, $indexMapsRepo, $indexLogsRepo);

        $this->stemWords = $stemWords;
        $this->stopWords = $stopWords;
    }

    /**
     * @param StyleInterface $style
     *
     * @return AbstractRunner
     */
    public function setStyle(StyleInterface $style): AbstractRunner
    {
        $this->actionIO = new ActionIO($style);

        return parent::setStyle($style);
    }

    /**
     * @param bool $enabled
     */
    public function setCache(bool $enabled): void
    {
        $this->cache = $enabled;
    }

    /**
     * @param iterable|EntityProviderInterface[] $providers
     */
    public function setProviders($providers): void
    {
        $this->providers = $providers;
    }

    public function run(): void
    {
        if (false === $this->createSearchEntries()) {
            $this->setResult(255);
        }
    }

    /**
     * @return bool
     */
    private function createSearchEntries(): bool
    {
        foreach ($this->providers as $provider) {
            $this->handleProvider($provider);
        }

        return true;
    }

    /**
     * @param EntityProviderInterface $provider
     *
     * @return bool
     */
    private function handleProvider(EntityProviderInterface $provider): bool
    {
        $this->io->section(sprintf('Processing "%s" Provider (%d Entities)', $provider->getName(), $provider->count()));

        foreach ($provider->forEachIndexableModels() as $i => $model) {
            if ($this->io->isVerbose()) {
                $this->io->text(sprintf('• %\'.04d. <options=bold>%s</> <fg=blue>[</><fg=blue;options=bold>%s</><fg=blue>]</>', $i + 1, $model->getClassName(), $model->getIdentity()));
            }

            if (false === $this->isDryRun() && false === $this->handleModel($model)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param IndexableEntity $model
     *
     * @return bool
     */
    private function handleModel(IndexableEntity $model): bool
    {
        if (null === $log = $this->hasSearchIndexLog($model)) {
            return $this->doModelPersist($model);
        }

        return $this->doModelSkipped($model, $log);
    }

    /**
     * @param IndexableEntity $model
     * @param SearchIndexLog  $log
     *
     * @return bool
     */
    private function doModelSkipped(IndexableEntity $model, SearchIndexLog $log): bool
    {
        if ($log->getObjectHash() === $model->getHash()) {
            $explains = sprintf('content unchanged: object hash match "%s"', $log->getObjectHash());
        } else {
            $interval = $this->getLogOldestValidDateTime()->diff($log->getUpdated());
            $explains = vsprintf('indexed on %s: fresh for another %d hours and %d minutes', [
                $log->getUpdated()->format('Y/m/d @ H:i:s'),
                $interval->format('%R%h'),
                $interval->format('%R%i'),
            ]);
        }

        if ($this->io->isDebug()) {
            $this->actionIO->actionSingle(sprintf('skipping entry (%s)', $explains));
        }

        return true;
    }

    /**
     * @param IndexableEntity $model
     *
     * @return bool
     */
    private function doModelPersist(IndexableEntity $model): bool
    {
        $wordStems = $this->handleModelStems($model);
        $this->doEntityDetach(...$wordStems);

        $indexMaps = $this->handleModelIndices($model, ...$wordStems);
        $this->doEntityDetach(...$indexMaps);

        $this->doEntityFlush(true);

        return 0 !== count($indexMaps) && $this->handleModelLog($model);
    }

    /**
     * @param IndexableEntity $model
     *
     * @return array|SearchStem[]
     */
    private function handleModelStems(IndexableEntity $model): array
    {
        $this->actionIO->action('stemming entity   ', true);
        $this->actionIO->enumeration(strlen($model->getStemableImploded()), 'chars');
        $stems = $this->stemWords->stem($model->getStemableImploded());
        $this->actionIO->okay();

        $this->actionIO->action('persisting stems  ');
        $this->actionIO->enumeration(count($stems));
        $stems = $this->findOrPersistStems($stems);
        $this->actionIO->okay();

        return $stems;
    }

    /**
     * @param IndexableEntity $model
     * @param SearchStem[]    ...$stems
     *
     * @return SearchIndex[]
     */
    private function handleModelIndices(IndexableEntity $model, SearchStem ...$stems): array
    {
        $this->actionIO->action('persisting indices');
        $this->actionIO->enumeration(count($stems));
        $indices = $this->findOrPersistIndices($model, ...$stems);
        $this->actionIO->okay();

        $this->actionIO->action('cleaning indices  ');
        $stale = $this->removeStaleIndices($model, ...$indices);
        $this->actionIO->enumeration(count($stale));
        $this->actionIO->okay();

        return $indices;
    }

    /**
     * @param IndexableEntity $model
     *
     * @return bool
     */
    private function handleModelLog(IndexableEntity $model): bool
    {
        $this->actionIO->action('checking index log');
        $this->actionIO->status(sprintf('(%s)', hash('crc32b', $model->getHash()).substr(hash('adler32', $model->getHash()), 0, 4)));

        if (null !== $log = $this->indexLogsRepo->findByObjectClassAndId($model->getClassName(), $model->getIdentity())) {
            if ($log->getObjectHash() === $model->getHash()) {
                try {
                    $this->doEntityPersist($log);
                    $this->doEntityFlush(true);
                    $this->doEntityDetach($log);
                } catch (ORMException $exception) {
                    return false;
                } finally {
                    $this->actionIO->done('updated', true);

                    return true;
                }
            }

            if ($log->getUpdated() > $this->getLogOldestValidDateTime()) {
                $this->actionIO->done('exists', true);

                return true;
            }

            try {
                $this->doEntityDeletes($log);
                $this->doEntityFlush(true);
                $this->doEntityDetach($log);
            } catch (ORMException $exception) {
                return false;
            } finally {
                $this->actionIO->done('removed-stale', true, false);

                return true;
            }
        }

        $log = new SearchIndexLog();
        $log->setObjectClass($model->getClassName());
        $log->setObjectIdentity($model->getIdentity());
        $log->setObjectHash($model->getHash());
        $log->setSuccess(true);

        try {
            $this->doEntityPersist($log);
            $this->doEntityFlush(true);
            $this->doEntityDetach($log);
        } catch (ORMException $exception) {
            return false;
        } finally {
            $this->actionIO->done('persisted', true);

            return true;
        }
    }

    /**
     * @param IndexableEntity $model
     *
     * @return null|SearchIndexLog
     */
    private function hasSearchIndexLog(IndexableEntity $model): ? SearchIndexLog
    {
        if (null === $log = $this->indexLogsRepo->findByObjectClassAndId($model->getClassName(), $model->getIdentity())) {
            return null;
        }

        if ($log->getObjectHash() !== $model->getHash() && $log->getUpdated() <= $this->getLogOldestValidDateTime()) {
            return null;
        }

        return $log->isSuccess() ? $log : null;
    }

    /**
     * @return \DateTime
     */
    private function getLogOldestValidDateTime() : \DateTime
    {
        return new \DateTime('-12 hours');
    }

    /**
     * @param string[] $stems
     *
     * @return string[]
     */
    private function findOrPersistStems(array $stems): array
    {
        $stems = array_map(function (string $stem) {
            return strtolower($stem);
        }, $stems);

        $stems = array_filter($stems, function (string $stem): bool {
            return null !== $stem && 2 < strlen($stem) && 1 === preg_match('{^[a-z].*[a-z]$}i', $stem);
        });

        $entities = [];
        $position = -1;
        $size = count($stems);

        foreach ($stems as $i => $stem) {
            $entities[$i] = $this->getSearchStemEntity($stem);
            $this->actionIO->progress(++$position, $size);
        }

        return $entities;
    }

    /**
     * @param IndexableEntity $model
     * @param SearchStem[]    ...$stems
     *
     * @return SearchIndex[]
     */
    private function findOrPersistIndices(IndexableEntity $model, SearchStem ...$stems): array
    {
        $entities = [];
        $size = count($stems);

        foreach ($stems as $position => $stem) {
            $entities[] = $this->getSearchIndexEntity($model, $stem, $position);
            $this->actionIO->progress($position, $size);

            if (0 === $position % 1000) {
                $this->doEntityFlush(true);
                $this->doEntityClear();
                $detaches = [];
            }
        }

        $this->doEntityFlush(true);
        $this->doEntityClear();

        return $entities;
    }

    /**
     * @param IndexableEntity $model
     * @param SearchIndex[]   ...$indices
     *
     * @return SearchIndex[]
     */
    private function removeStaleIndices(IndexableEntity $model, SearchIndex ...$indices): array
    {
        $staleIndices = $this
            ->indexMapsRepo
            ->findByObjectAndNotInSet($model->getClassName(), $model->getIdentity(), ...$indices);

        try {
            $this->doEntityDeletesDetach(...$staleIndices);
            $this->doEntityFlush(true);
        } catch (ORMException $exception) {
            // ignore exception
        } finally {
            return $staleIndices;
        }
    }

    /**
     * @param string $stem
     *
     * @return SearchStem
     */
    private function getSearchStemEntity(string $stem): SearchStem
    {
        if (null === $entity = $this->wordStemsRepo->findByStem($stem)) {
            $entity = new SearchStem();
            $entity->setStem($stem);

            $this->doEntityPersist($entity);
            $this->doEntityFlush(true);
        }

        return $entity;
    }

    /**
     * @param IndexableEntity $model
     * @param SearchStem      $stem
     * @param int             $position
     *
     * @return SearchIndex
     */
    private function getSearchIndexEntity(IndexableEntity $model, SearchStem $stem, int $position): SearchIndex
    {
        if (null === $entity = $this->indexMapsRepo->findByObjectAndStemAndPosition($model->getClassName(), $model->getIdentity(), $stem, $position)) {
            list($stem) = $this->doEntityMerges($stem);

            $entity = new SearchIndex();
            $entity->setObjectClass($model->getClassName());
            $entity->setObjectIdentity($model->getIdentity());
            $entity->setStem($stem);
            $entity->setPosition($position);

            $this->doEntityPersist($entity, $stem);
        }

        return $entity;
    }

    /**
     * @param Entity[] ...$entities
     */
    private function doEntityPersist(Entity ...$entities): void
    {
        foreach ($entities as $entity) {
            $this->em->persist($entity);
        }

        $this->doEntityFlush();
    }

    /**
     * @param Entity[] ...$entities
     */
    private function doEntityPersistDetach(Entity ...$entities): void
    {
        $this->doEntityPersist(...$entities);
        $this->doEntityFlush();
        $this->doEntityDetach(...$entities);
    }

    /**
     * @param Entity[] ...$entities
     */
    private function doEntityDeletes(Entity ...$entities): void
    {
        foreach ($entities as $entity) {
            $this->em->remove($entity);
        }

        $this->doEntityFlush();
    }

    /**
     * @param Entity[] ...$entities
     */
    private function doEntityDeletesDetach(Entity ...$entities): void
    {
        $this->doEntityDeletes(...$entities);
        $this->doEntityDetach(...$entities);
    }

    /**
     * @param Entity[] ...$entities
     */
    private function doEntityDetach(Entity ...$entities): void
    {
        foreach ($entities as $entity) {
            $this->em->detach($entity);
        }

        $this->doEntityFlush();
    }

    /**
     * @param bool $force
     */
    private function doEntityFlush(bool $force = false): void
    {
        if ($force || $this->isUnitOfWorkMoreThan()) {
            $this->em->flush();
        }
    }

    /**
     * @param Entity[] ...$entities
     *
     * @return Entity[]
     */
    private function doEntityMerges(Entity ...$entities): array
    {
        $entities = array_map(function (Entity $entity) {
            return $this->em->contains($entity) ? $entity : $this->em->merge($entity);
        }, $entities);

        $this->doEntityFlush();

        return $entities;
    }

    private function doEntityClear(): void
    {
        $this->em->clear();
    }

    /**
     * @param int $size
     *
     * @return bool
     */
    private function isUnitOfWorkMoreThan(int $size = 1000): bool
    {
        $u = $this->em->getUnitOfWork();
        $s = 0;

        foreach (['EntityInsertions', 'EntityUpdates', 'EntityDeletions', 'CollectionUpdates', 'CollectionDeletions'] as $type) {
            $s += count(call_user_func([$u, sprintf('getScheduled%s', $type)]));
        }

        return $s > $size;
    }
}
