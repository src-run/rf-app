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
use Rf\AppBundle\Doctrine\Entity\Interfaces\ObjectIdentityInterface;
use Rf\AppBundle\Doctrine\Entity\SearchIndexLog;
use Rf\AppBundle\Doctrine\Entity\SearchStem;
use Rf\AppBundle\Doctrine\Repository\SearchIndexLogRepository;
use Rf\AppBundle\Doctrine\Repository\SearchIndexRepository;
use Rf\AppBundle\Doctrine\Repository\SearchStemRepository;

final class SearchPruneRunner extends AbstractSearchRunner
{
    /**
     * @param EntityManagerInterface   $em
     * @param SearchStemRepository     $wordStemsRepo
     * @param SearchIndexRepository    $indexMapsRepo
     * @param SearchIndexLogRepository $indexLogsRepo
     */
    public function __construct(EntityManagerInterface $em, SearchStemRepository $wordStemsRepo, SearchIndexRepository $indexMapsRepo, SearchIndexLogRepository $indexLogsRepo)
    {
        parent::__construct($em, $wordStemsRepo, $indexMapsRepo, $indexLogsRepo);
    }

    public function run(): void
    {
        $this->io->section('Purging stems/indices');

        if ($this->isDryRun()) {
            return;
        }

        if (false === $this->purgeSearchIndexLogs() ||
            false === $this->purgeSearchIndexMaps() ||
            false === $this->purgeSearchWordStems()) {
            $this->setResult(255);
        }
    }

    /**
     * @return bool
     */
    private function purgeSearchIndexLogs(): bool
    {
        $this->io->info(['Purging search index logs']);

        return $this->purgeObjectIdentityEntries('index log', function (): \Generator {
            return $this->getMatchingSearchIndexLogs();
        });
    }

    /**
     * @return bool
     */
    private function purgeSearchIndexMaps(): bool
    {
        $this->io->info(['Purging search index maps']);

        return $this->purgeObjectIdentityEntries('index map', function (): \Generator {
            return $this->getMatchingSearchIndexMaps();
        });
    }

    /**
     * @return bool
     */
    private function purgeSearchWordStems(): bool
    {
        $this->io->info(['Purging search word stems']);

        return $this->purgeObjectIdentityEntries('word stem', function (): \Generator {
            return $this->getMatchingSearchWordStems();
        });
    }

    /**
     * @return ObjectIdentityInterface[]|\Generator
     */
    private function getMatchingSearchIndexLogs(): \Generator
    {
        $identities = 0 === count($this->uuids) ? $this->indexLogsRepo->getObjectIdentities() : $this->uuids;

        foreach ($identities as $id) {
            if (null !== $entity = $this->indexLogsRepo->findByObjectIdentity($id)) {
                yield $entity;
            }
        }
    }

    /**
     * @return SearchIndexLog[]|\Generator
     */
    private function getMatchingSearchIndexMaps(): \Generator
    {
        $identities = 0 === count($this->uuids) ? $this->indexMapsRepo->getObjectIdentities() : $this->uuids;

        foreach ($identities as $id) {
            if (null !== $entity = $this->indexMapsRepo->findByObjectIdentity($id)) {
                yield $entity;
            }
        }
    }

    /**
     * @return SearchIndexLog[]|\Generator
     */
    private function getMatchingSearchWordStems(): \Generator
    {
        foreach ($this->wordStemsRepo->getIdsWithNoIndices() as $id) {
            if (null !== $entity = $this->wordStemsRepo->findById($id)) {
                yield $entity;
            }
        }
    }

    /**
     * @param string   $action
     * @param \Closure $fetcher
     *
     * @return int
     */
    private function purgeObjectIdentityEntries(string $action, \Closure $fetcher): int
    {
        foreach ($fetcher() as $entity) {
            $this->writeActionOpen($action, $entity);
            $this->em->remove($entity);

            try {
                $this->em->flush();
            } catch (\Exception $exception) {
                $this->writeActionDone(false);

                return false;
            }

            $this->writeActionDone();
        }

        return true;
    }

    /**
     * @param string                  $action
     * @param ObjectIdentityInterface $entity
     */
    private function writeActionOpen(string $action, $entity): void
    {
        if ($this->io->isVeryVerbose()) {
            if ($entity instanceof ObjectIdentityInterface) {
                $this->io->action(vsprintf('Pruning %s "%s:%s"', [
                    $action,
                    $entity->getObjectClass(),
                    $entity->getObjectIdentity(),
                ]));
            } elseif ($entity instanceof SearchStem) {
                $this->io->action(vsprintf('Pruning %s "%s"', [
                    $action,
                    $entity->getStem(),
                ]));
            }
        } elseif ($this->io->isVerbose()) {
            $this->io->write('.');
        }
    }

    private function writeActionDone(bool $success = true): void
    {
        if ($this->io->isVeryVerbose()) {
            if ($success) {
                $this->io->actionOkay();
            } else {
                $this->io->actionFail();
            }
        } elseif ($this->io->isVerbose()) {
            $this->io->writeln([]);
        }
    }
}
