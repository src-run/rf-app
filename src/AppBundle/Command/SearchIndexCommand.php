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

use Rf\AppBundle\Component\Console\Registry\ConfigurationRegistry;
use Rf\AppBundle\Component\Console\Runner\Search\SearchCreateRunner;
use Rf\AppBundle\Component\Console\Runner\Search\SearchPurgeRunner;
use SR\Console\Output\Style\Style;
use SR\Console\Output\Style\StyleAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchIndexCommand extends AbstractCommand
{
    use StyleAwareTrait;

    /**
     * @var SearchPurgeRunner
     */
    private $searchPurgeRunner;

    /**
     * @var SearchCreateRunner
     */
    private $searchCreateRunner;

    /**
     * @param SearchPurgeRunner  $searchPurgeRunner
     * @param SearchCreateRunner $searchCreateRunner
     */
    public function __construct(SearchPurgeRunner $searchPurgeRunner, SearchCreateRunner $searchCreateRunner)
    {
        parent::__construct();

        $this->searchPurgeRunner = $searchPurgeRunner;
        $this->searchCreateRunner = $searchCreateRunner;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Create search stems and reverse index for articles');
        $this->addArgument('uuid', InputArgument::OPTIONAL|InputArgument::IS_ARRAY,
            'Optional article UUIDs to use when creating index; otherwise all will be used.');
        $this->addOption('purge', ['p'], InputOption::VALUE_NONE,
            'Purge all existing search stem/index entries.');
        $this->addOption('no-cache', ['C'], InputOption::VALUE_NONE,
            'Disable caching of entity stem/index entries.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new Style($input, $output);

        $this->writeConfiguration(
            $uuids = $this->io->getInput()->getArgument('uuid'),
            $purge = $this->io->getInput()->getOption('purge'),
            $cache = $this->io->getInput()->getOption('no-cache') !== true
        );

        if ($this->io->isVeryVerbose() && !$this->io->confirm('Continue using this configuration?', true)) {
            $this->io->critical('Stopping due to user request.');
            return 255;
        }

        if (($purge && 0 !== $return = $this->doPruneRunner(...$uuids)) || 0 !== $return = $this->doIndexRunner($cache, ...$uuids)) {
            return $return;
        }

        $this->io->success('Completed all operations without error.');

        return 0;
    }

    /**
     * @param ConfigurationRegistry $c
     *
     * @return int
     */
    protected function doExecute(ConfigurationRegistry $c): int
    {
        if ((true === $c->get('purge') && 0 !== $return = $this->doPruneRunner($c)) || 0 !== $return = $this->doIndexRunner($c)) {
            return $return;
        }

        return 0;
    }

    /**
     * @param ConfigurationRegistry $c
     *
     * @return int
     */
    private function doPruneRunner(ConfigurationRegistry $c): int
    {
        $this->searchPurgeRunner->setStyle($this->io);
        $this->searchPurgeRunner->setUuids(...$c->get('uuids'));
        $this->searchPurgeRunner->run();

        if (0 !== $result = $this->searchPurgeRunner->getResult()) {
            $this->io->critical('Unable to purge search index logs, search index maps, or search word stems!');
        }

        return $result;
    }

    /**
     * @param ConfigurationRegistry $c
     *
     * @return int
     */
    private function doIndexRunner(ConfigurationRegistry $c): int
    {
        $this->searchCreateRunner->setStyle($this->io);
        $this->searchCreateRunner->setCache($c->get('cache'));
        $this->searchCreateRunner->setUuids(...$c->get('uuids'));
        $this->searchCreateRunner->run();

        if (0 !== $result = $this->searchCreateRunner->getResult()) {
            $this->io->critical('Unable to create search index logs, search index maps, or search word stems!');
        }

        return $result;
    }

    /**
     * @return ConfigurationRegistry
     */
    protected function initializeConfiguration(): ConfigurationRegistry
    {
        $c = new ConfigurationRegistry();
        $c->set('uuids', $this->io->getInput()->getArgument('uuid'));
        $c->set('purge', $this->io->getInput()->getOption('purge'));
        $c->set('cache', $this->io->getInput()->getOption('no-cache') !== true);

        $this->writeConfiguration([
            'Article Identities',
            'Purge Existing Data',
            'Cache Stem Data',
        ], [
            [sprintf('list=[%s]%s', implode(',', $c->get('uuids')) ?: '<none>',
                (count($c->get('uuids')) === 0 ? '' : sprintf(' (%d)', count($c->get('uuids')))))],
            [sprintf('enabled=[%s]', $c->get('purge') ? 'yes' : 'no')],
            [sprintf('enabled=[%s]', $c->get('cache') ? 'yes' : 'no')],
        ]);

        return $c;
    }
}
