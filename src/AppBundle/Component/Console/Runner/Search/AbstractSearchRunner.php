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
use Rf\AppBundle\Component\Console\Runner\AbstractRunner;
use Rf\AppBundle\Doctrine\Repository\SearchIndexLogRepository;
use Rf\AppBundle\Doctrine\Repository\SearchIndexRepository;
use Rf\AppBundle\Doctrine\Repository\SearchStemRepository;

abstract class AbstractSearchRunner extends AbstractRunner
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var SearchStemRepository
     */
    protected $wordStemsRepo;

    /**
     * @var SearchIndexRepository
     */
    protected $indexMapsRepo;

    /**
     * @var SearchIndexLogRepository
     */
    protected $indexLogsRepo;

    /**
     * @var string[]
     */
    protected $uuids = [];

    /**
     * @param EntityManagerInterface   $em
     * @param SearchStemRepository     $wordStemsRepo
     * @param SearchIndexRepository    $indexMapsRepo
     * @param SearchIndexLogRepository $indexLogsRepo
     */
    public function __construct(EntityManagerInterface $em, SearchStemRepository $wordStemsRepo, SearchIndexRepository $indexMapsRepo, SearchIndexLogRepository $indexLogsRepo)
    {
        parent::__construct(null);

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
}
