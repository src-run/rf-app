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
use Rf\AppBundle\Component\Console\Runner\AbstractRunner;
use Rf\AppBundle\Component\Search\Indexing\EntityProviderInterface;
use Rf\AppBundle\Component\Search\Indexing\Model\IndexableEntityModel;
use Rf\AppBundle\Doctrine\Entity\SearchIndex;
use Rf\AppBundle\Doctrine\Entity\SearchIndexLog;
use Rf\AppBundle\Doctrine\Entity\SearchStem;
use Rf\AppBundle\Doctrine\Repository\SearchIndexLogRepository;
use Rf\AppBundle\Doctrine\Repository\SearchIndexRepository;
use Rf\AppBundle\Doctrine\Repository\SearchStemRepository;
use SR\Cocoa\Word\Stem\Stemmer;
use SR\Cocoa\Word\Stop\Stopwords;
use SR\Doctrine\ORM\Mapping\Entity;

class SearchCreateRunner extends AbstractRunner
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
     * @var EntityProviderInterface[]|iterable
     */
    private $providers;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SearchStemRepository
     */
    private $wordStemsRepo;

    /**
     * @var SearchIndexRepository
     */
    private $indexMapsRepo;

    /**
     * @var SearchIndexLogRepository
     */
    private $indexLogsRepo;

    /**
     * @var bool
     */
    private $cache = true;

    /**
     * @var string[]
     */
    private $uuids = [];

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
        parent::__construct(null);

        $this->stemWords = $stemWords;
        $this->stopWords = $stopWords;
        $this->em = $em;
        $this->wordStemsRepo = $wordStemsRepo;
        $this->indexMapsRepo = $indexMapsRepo;
        $this->indexLogsRepo = $indexLogsRepo;
    }

    /**
     * @param string[] ...$uuids
     */
    public function setUuids(string ...$uuids): void
    {
        $this->uuids = $uuids;
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

    /**
     * @return void
     */
    public function run(): void
    {
        $this->io->section('Creating stems/indices');

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
        foreach ($provider->getModels() as $model) {
            $this->io->info([sprintf('Processing "%s:%s"', $model->getObjectClass(), $model->getObjectIdentity())]);

            if (false === $this->handleModel($model)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param IndexableEntityModel $model
     *
     * @return bool
     */
    private function handleModel(IndexableEntityModel $model): bool
    {
        if (null === $log = $this->hasSearchIndexLog($model)) {
            return $this->doModelPersist($model);
        }

        return $this->doModelSkipped($model, $log);
    }

    /**
     * @param IndexableEntityModel $model
     * @param SearchIndexLog       $log
     *
     * @return bool
     */
    private function doModelSkipped(IndexableEntityModel $model, SearchIndexLog $log): bool
    {
        if ($log->getObjectHash() === $model->getObjectHash()) {
            $explains = sprintf('content unchanged: object hash match "%s"', $log->getObjectHash());
        } else {
            $interval = $this->getLogOldestValidDateTime()->diff($log->getUpdated());
            $explains = vsprintf('indexed on %s: fresh for another %d hours and %d minutes', [
                $log->getUpdated()->format('Y/m/d @ H:i:s'),
                $interval->format('%R%h'),
                $interval->format('%R%i'),
            ]);
        }

        $this->io->comment([sprintf('skipping entity (%s)', $explains)]);

        return true;
    }

    /**
     * @param IndexableEntityModel $model
     *
     * @return bool
     */
    private function doModelPersist(IndexableEntityModel $model): bool
    {
        $wordStems = $this->handleModelStems($model);
        $this->doEntityDetach(...$wordStems);

        $indexMaps = $this->handleModelIndices($model, ...$wordStems);
        $this->doEntityDetach(...$indexMaps);

        $this->doEntityFlush(true);

        return 0 !== count($indexMaps) && $this->handleModelLog($model);
    }

    /**
     * @param IndexableEntityModel $model
     *
     * @return array|SearchStem[]
     */
    private function handleModelStems(IndexableEntityModel $model): array
    {
        $this->io->action('stemming model contents');
        $this->io->write(sprintf('(%d chars) ', strlen($model->getStemmableString())));
        $stems = $this->stemWords->stem($model->getStemmableString());
        $this->io->actionOkay();

        $this->io->action('persisting word stems');
        $this->io->write(sprintf('(%d items) ', count($stems)));
        $stems = $this->findOrPersistStems($stems);
        $this->io->actionOkay();

        return $stems;
    }

    /**
     * @param IndexableEntityModel $model
     * @param SearchStem[]         ...$stems
     *
     * @return SearchIndex[]
     */
    private function handleModelIndices(IndexableEntityModel $model, SearchStem ...$stems): array
    {
        $this->io->action('persisting indices');
        $this->io->write(sprintf('(%d items) ', count($stems)));
        $indices = $this->findOrPersistIndices($model, ...$stems);
        $this->io->actionOkay();

        $this->io->action('cleaning up indices');
        $stale = $this->removeStaleIndices($model, ...$indices);
        $this->io->write(sprintf('(%d items) ', count($stale)));
        $this->io->actionOkay();

        return $indices;
    }

    /**
     * @param IndexableEntityModel $model
     *
     * @return bool
     */
    private function handleModelLog(IndexableEntityModel $model): bool
    {
        $this->io->action('checking for index log entry');

        if (null !== $log = $this->indexLogsRepo->findByObjectClassAndId($model->getObjectClass(), $model->getObjectIdentity())) {
            if ($log->getObjectHash() === $model->getObjectHash()) {
                try {
                    $this->doEntityPersist($log);
                    $this->doEntityFlush(true);
                    $this->doEntityDetach($log);
                } catch (ORMException $exception) {
                    return false;
                } finally {
                    $this->io->write('(updated expiration) ');
                    $this->io->actionOkay();

                    return true;
                }
            }

            if ($log->getUpdated() > $this->getLogOldestValidDateTime()) {
                $this->io->write('(leaving existing) ');
                $this->io->actionOkay();

                return true;
            }

            try {
                $this->doEntityDeletes($log);
                $this->doEntityFlush(true);
                $this->doEntityDetach($log);
            } catch (ORMException $exception) {
                return false;
            } finally {
                $this->io->write('(removing stale) ');
                $this->io->actionFail('RM');

                return true;
            }

        }

        $log = new SearchIndexLog();
        $log->setObjectClass($model->getObjectClass());
        $log->setObjectIdentity($model->getObjectIdentity());
        $log->setObjectHash($model->getObjectHash());
        $log->setSuccess(true);

        try {
            $this->doEntityPersist($log);
            $this->doEntityFlush(true);
            $this->doEntityDetach($log);
        } catch (ORMException $exception) {
            return false;
        } finally {
            $this->io->write('(persisting new) ');
            $this->io->actionOkay();

            return true;
        }
    }

    /**
     * @param IndexableEntityModel $model
     *
     * @return null|SearchIndexLog
     */
    private function hasSearchIndexLog(IndexableEntityModel $model): ?SearchIndexLog
    {
        if (null === $log = $this->indexLogsRepo->findByObjectClassAndId($model->getObjectClass(), $model->getObjectIdentity())) {
            return null;
        }

        if ($log->getObjectHash() !== $model->getObjectHash() && $log->getUpdated() <= $this->getLogOldestValidDateTime()) {
            return null;
        }

        return $log->isSuccess() ? $log : null;
    }

    /**
     * @return \DateTime
     */
    private function getLogOldestValidDateTime(): \DateTime
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
            $this->writeProgress(++$position, $size);
        }

        return $entities;
    }

    /**
     * @param IndexableEntityModel $model
     * @param SearchStem[]         ...$stems
     *
     * @return SearchIndex[]
     */
    private function findOrPersistIndices(IndexableEntityModel $model, SearchStem ...$stems): array
    {
        $entities = [];
        $size = count($stems);

        foreach ($stems as $position => $stem) {
            $entities[] = $this->getSearchIndexEntity($model, $stem, $position);
            $this->writeProgress($position, $size);

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
     * @param int $position
     * @param int $size
     */
    private function writeProgress(int $position, int $size): void
    {
        if (!$this->io->isVerbose()) {
            return;
        }

        if ($position === 0) {
            $this->io->write('(');
        }

        if ($position === ($size - 1)) {
            $this->io->write(sprintf('..%d) ', $size));
        }
        elseif (0 === $position % 1000) {
            $this->io->write($position);
        }
        elseif (0 === $position % 200) {
            $this->io->write('.');
        }
    }

    /**
     * @param IndexableEntityModel $model
     * @param SearchIndex[]        ...$indices
     *
     * @return SearchIndex[]
     */
    private function removeStaleIndices(IndexableEntityModel $model, SearchIndex ...$indices): array
    {
        $staleIndices = $this
            ->indexMapsRepo
            ->findByObjectAndNotInSet($model->getObjectClass(), $model->getObjectIdentity(), ...$indices);

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
     * @param IndexableEntityModel $model
     * @param SearchStem           $stem
     * @param int                  $position
     *
     * @return SearchIndex
     */
    private function getSearchIndexEntity(IndexableEntityModel $model, SearchStem $stem, int $position): SearchIndex
    {
        if (null === $entity = $this->indexMapsRepo->findByObjectAndStemAndPosition($model->getObjectClass(), $model->getObjectIdentity(), $stem, $position)) {
            list($stem) = $this->doEntityMerges($stem);

            $entity = new SearchIndex();
            $entity->setObjectClass($model->getObjectClass());
            $entity->setObjectIdentity($model->getObjectIdentity());
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
     *
     * @return void
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

    /**
     * @return void
     */
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
