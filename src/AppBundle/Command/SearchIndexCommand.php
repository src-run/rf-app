<?php

/*
 * This file is part of the `src-run/rf-app` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Rf\AppBundle\Command;

use Rf\AppBundle\Component\Console\ActionStepper\ActionStepper;
use Rf\AppBundle\Component\Console\Registry\AbstractRegistry;
use Rf\AppBundle\Component\Console\Registry\StaticRegistry;
use Rf\AppBundle\Component\Console\Runner\Search\SearchIndexRunner;
use Rf\AppBundle\Component\Console\Runner\Search\SearchPruneRunner;
use SR\Console\Output\Style\StyleAwareTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SearchIndexCommand extends AbstractCommand
{
    use StyleAwareTrait;

    /**
     * @var SearchPruneRunner
     */
    private $searchPruneRunner;

    /**
     * @var SearchIndexRunner
     */
    private $searchIndexRunner;

    /**
     * @param ActionStepper     $actionStepper
     * @param SearchPruneRunner $searchPruneRunner
     * @param SearchIndexRunner $searchIndexRunner
     */
    public function __construct(ActionStepper $actionStepper, SearchPruneRunner $searchPruneRunner, SearchIndexRunner $searchIndexRunner)
    {
        parent::__construct($actionStepper);

        $this->searchPruneRunner = $searchPruneRunner;
        $this->searchIndexRunner = $searchIndexRunner;
    }

    protected function configure(): void
    {
        $this->setDescription('Create search stems and reverse index for articles');
        $this->addArgument('uuid', InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Optional article UUIDs to use when creating index; otherwise all will be used.');
        $this->addOption('purge', ['p'], InputOption::VALUE_NONE,
            'Purge all existing search stem/index entries.');
        $this->addOption('no-cache', ['C'], InputOption::VALUE_NONE,
            'Disable caching of entity stem/index entries.');
        $this->addOption('dry-run', ['d'], InputOption::VALUE_NONE,
            'Enable dry-run mode');
    }

    /**
     * @param AbstractRegistry $c
     */
    protected function initializeActions(AbstractRegistry $c): void
    {
        $this->actionStepper->addActionClosure('search-prune', function () use ($c) {
            return $this->doSearchPruneRunner($c);
        })->setConditional(function () use ($c) {
            return true === $c->get('purge');
        })->setErrorString('Unable to purge search index logs, search index maps, or search word stems!');

        $this->actionStepper->addActionClosure('search-index', function () use ($c) {
            return $this->doSearchIndexRunner($c);
        })->setErrorString('Unable to create search index logs, search index maps, or search word stems!');
    }

    /**
     * @param AbstractRegistry $c
     *
     * @return int
     */
    private function doSearchPruneRunner(AbstractRegistry $c): int
    {
        $this->searchPruneRunner->setStyle($this->io);
        $this->searchPruneRunner->setDryRun($c->get('dry-run'));
        $this->searchPruneRunner->setUuids(...$c->get('uuids'));
        $this->searchPruneRunner->run();

        return $this->searchPruneRunner->getResult();
    }

    /**
     * @param AbstractRegistry $c
     *
     * @return int
     */
    private function doSearchIndexRunner(AbstractRegistry $c): int
    {
        $this->searchIndexRunner->setStyle($this->io);
        $this->searchIndexRunner->setDryRun($c->get('dry-run'));
        $this->searchIndexRunner->setCache($c->get('cache'));
        $this->searchIndexRunner->setUuids(...$c->get('uuids'));
        $this->searchIndexRunner->run();

        return $this->searchIndexRunner->getResult();
    }

    /**
     * @return AbstractRegistry
     */
    protected function initializeConfiguration(): AbstractRegistry
    {
        $c = new StaticRegistry();

        $c->set('uuids', $this->io->getInput()->getArgument('uuid'));
        $c->set('purge', $this->io->getInput()->getOption('purge'));
        $c->set('cache', $this->io->getInput()->getOption('no-cache') !== true);
        $c->set('dry-run', $this->io->getInput()->getOption('dry-run'));

        $this->writeConfiguration([
            'Entity Identities',
            'Purge Existing Data',
            'Cache Stem Data',
            'Dry Run Mode',
        ], [
            [$this->getConfigurationListMarkup($c->get('uuids'))],
            [$this->getConfigurationFlagMarkup($c->get('purge'))],
            [$this->getConfigurationFlagMarkup($c->get('cache'))],
            [$this->getConfigurationFlagMarkup($c->get('dry-run'))],
        ]);

        return $c;
    }
}
