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

use Rf\AppBundle\Component\Console\InputParamResolver;
use Rf\AppBundle\Component\Console\OutputErrorHandler;
use Rf\AppBundle\Component\Console\Runner\Search\SearchCreateRunner;
use Rf\AppBundle\Component\Console\Runner\Search\SearchPurgeRunner;
use Rf\AppBundle\Component\DependencyInjection\ParameterResolver;
use SR\Console\Output\Style\Style;
use SR\Console\Output\Style\StyleAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArticleSearchIndexCommand extends Command
{
    use StyleAwareTrait;

    /**
     * @var OutputErrorHandler
     */
    private $outputErrorHandler;

    /**
     * @var InputParamResolver
     */
    private $inputParamResolver;

    /**
     * @var ParameterResolver
     */
    private $parameterResolver;

    /**
     * @var SearchPurgeRunner
     */
    private $searchPurgeRunner;

    /**
     * @var SearchCreateRunner
     */
    private $searchCreateRunner;

    /**
     * @param ParameterResolver  $parameterResolver
     * @param SearchPurgeRunner  $searchPurgeRunner
     * @param SearchCreateRunner $searchCreateRunner
     */
    public function __construct(ParameterResolver $parameterResolver, SearchPurgeRunner $searchPurgeRunner, SearchCreateRunner $searchCreateRunner)
    {
        parent::__construct();

        $this->parameterResolver = $parameterResolver;
        $this->searchPurgeRunner = $searchPurgeRunner;
        $this->searchCreateRunner = $searchCreateRunner;
    }

    protected function configure()
    {
        $this->setDescription('Create search stems and reverse index for articles');
        $this->addArgument('uuid', InputArgument::OPTIONAL|InputArgument::IS_ARRAY,
            'Optional article UUIDs to use when creating index; otherwise all will be used.');
        $this->addOption('do-not-purge', ['P'], InputOption::VALUE_NONE,
            'Disable purging of existing search stem and index data.');
        $this->addOption('do-not-cache', ['C'], InputOption::VALUE_NONE,
            'Disable caching of stem data.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initializeProperties($input, $output);

        $this->writeConfiguration(
            $uuids = $this->getUuidArguments(),
            $purge = $this->getPurgeOption(),
            $cache = $this->getCacheOption()
        );

        if (false === $this->io->confirm('Continue using this configuration?', true)) {
            return $this->userRequestedExit();
        }

        if ($purge) {
            $this->runPurge();
        }

        $this->searchCreateRunner->setCacheEnabled($cache);
        $this->runCreate();

        return 0;
    }

    private function runPurge(): void
    {
        $this->searchPurgeRunner->setStyle($this->io);
        $this->searchPurgeRunner->run();

        if (0 !== $result = $this->searchPurgeRunner->getResult()) {
            $this->outputErrorHandler->raiseCriticalAndExitImmediately('An error occurred while purging the existing search stem/index data!');
        }
    }

    private function runCreate(): void
    {
        $this->searchCreateRunner->setStyle($this->io);
        $this->searchCreateRunner->run();

        if (0 !== $result = $this->searchCreateRunner->getResult()) {
            $this->outputErrorHandler->raiseCriticalAndExitImmediately('An error occurred while creating the search stem/index data!');
        }

        $this->io->success('Completed operations');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function initializeProperties(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new Style($input, $output);
        $this->outputErrorHandler = new OutputErrorHandler($this->io);
        $this->inputParamResolver = new InputParamResolver($this->io, $this->outputErrorHandler, $this->parameterResolver);
    }

    /**
     * @return string[]
     */
    private function getUuidArguments(): array
    {
        if (false !== $uuidList = $this->inputParamResolver->resolveArgument('uuid')) {
            return $uuidList;
        }

        return [];
    }

    /**
     * @return bool
     */
    private function getPurgeOption(): bool
    {
        return !$this->inputParamResolver->resolveOption('do-not-purge');
    }

    /**
     * @return bool
     */
    private function getCacheOption(): bool
    {
        return !$this->inputParamResolver->resolveOption('do-not-cache');
    }

    /**
     * @param array $uuidList
     * @param bool  $purge
     * @param bool  $cache
     */
    private function writeConfiguration(array $uuidList, bool $purge, bool $cache): void
    {
        if ($this->io->isVeryVerbose()) {
            $this->io->comment('resolved runtime configuration:');
        }

        if ($this->io->isVerbose()) {
            $this->io->tableVertical([
                'Article Identities',
                'Purge Existing Data',
                'Cache Stem Data',
            ], ...[
                [sprintf('list=[%s]%s', implode(',', $uuidList) ?: '<none>',
                    (count($uuidList) === 0 ? '' : sprintf(' (%d)', count($uuidList))))],
                [sprintf('enabled=[%s]', $purge ? 'yes' : 'no')],
                [sprintf('enabled=[%s]', $cache ? 'yes' : 'no')],
            ]);
        }
    }

    /**
     * @return int
     */
    protected function userRequestedExit()
    {
        $this->io->success('The user initiated a manual script termination');

        return 0;
    }
}
