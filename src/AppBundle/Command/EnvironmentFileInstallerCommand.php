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
use Rf\AppBundle\Component\Console\Runner\EnvInstallFileRunner;
use Rf\AppBundle\Component\Console\Runner\EnvRemoveFileRunner;
use Rf\AppBundle\Component\DependencyInjection\ParameterResolver;
use SR\Console\Output\Style\Style;
use SR\Console\Output\Style\StyleAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EnvironmentFileInstallerCommand extends Command
{
    use StyleAwareTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var OutputErrorHandler
     */
    private $outputErrorHandler;

    /**
     * @var InputParamResolver
     */
    private $inputParamResolver;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct();

        $this->container = $container;
    }

    protected function configure()
    {
        $this->setName('sr:environment:file-installer');
        $this->setDescription('Installs a collection of files (such as "dot files") into the repository root depending on environment.');
        $this->setAliases([
            'env:file-installer',
        ]);

        $this->addArgument(
            'environment-name',
            InputArgument::OPTIONAL,
            'The name of the environment',
            'default'
        );

        $this->addOption(
            'repository-root', ['r'],
            InputOption::VALUE_REQUIRED,
            'The repository root directory path',
            '%kernel.root_dir%/../'
        );
        $this->addOption(
            'configuration-root', ['c'],
            InputOption::VALUE_REQUIRED,
            'Environment config file root path',
            '%kernel.root_dir%/../.env/'
        );
        $this->addOption(
            'no-overwrite', ['L'],
            InputOption::VALUE_NONE,
            'Disables overwriting existing files'
        );
        $this->addOption(
            'no-file', ['l'],
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Disables overwriting specificed files',
            []
        );
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
            $environment = $this->getEnvironmentArgument(),
            $repoDirectory = $this->getRepoDirectoryOption(),
            $confDirectory = $this->getConfDirectoryOption(),
            $listManualIgnore = $this->getManualIgnoreOption(),
            $listNotOverwrite = $this->getNotOverwriteOption()
        );

        if (false === $this->io->confirm('Continue using this configuration?', true)) {
            return $this->userRequestedExit();
        }

        if (!$listNotOverwrite) {
            $runner = new EnvRemoveFileRunner($this->io, $repoDirectory, $listManualIgnore);
            $runner->run();
        }

        $runner = new EnvInstallFileRunner($this->io, $environment, $repoDirectory, $confDirectory, $listManualIgnore);
        $runner->run();

        if ($result = $runner->getResult()) {
            $this->io->success('Completed operations');
        }

        return $result;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function initializeProperties(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new Style($input, $output);
        $this->outputErrorHandler = new OutputErrorHandler($this->io);
        $this->inputParamResolver = new InputParamResolver($this->io, $this->outputErrorHandler, new ParameterResolver($this->container));
    }

    /**
     * @return string
     */
    private function getEnvironmentArgument(): string
    {
        if (false === $environment = $this->inputParamResolver->resolveArgument('environment-name')) {
            $this->outputErrorHandler->raiseCritical('You must provide a valid environment name.');
        }

        return $environment;
    }

    /**
     * @return string
     */
    private function getRepoDirectoryOption(): string
    {
        if (false === $realRepoPath = realpath($repoDirectory = $this->inputParamResolver->resolveOption('repository-root'))) {
            $this->outputErrorHandler->raiseCritical('Path "%s" does not exist.', $repoDirectory);
        }

        return $realRepoPath;
    }

    /**
     * @return string
     */
    private function getConfDirectoryOption(): string
    {
        if (false === $realConfPath = realpath($confDirectory = $this->inputParamResolver->resolveOption('configuration-root'))) {
            $this->outputErrorHandler->raiseCritical('Path "%s" does not exist.', $confDirectory);
        }

        return $realConfPath;
    }

    /**
     * @return string[]
     */
    private function getManualIgnoreOption(): array
    {
        return array_map(function ($file) {
            return basename($file);
        }, $this->io->getInput()->getOption('no-file'));
    }

    /**
     * @return bool
     */
    private function getNotOverwriteOption(): bool
    {
        return $this->io->getInput()->getOption('no-overwrite');
    }

    /**
     * @param string $environment
     * @param string $repoDirectory
     * @param string $confDirectory
     * @param array  $listManualIgnore
     * @param array  $listNotOverwrite
     */
    private function writeConfiguration(string $environment, string $repoDirectory, string $confDirectory, array $listManualIgnore, bool $listNotOverwrite)
    {
        if ($this->io->isVeryVerbose()) {
            $this->io->comment('resolved runtime configuration:');
        }

        if ($this->io->isVerbose()) {
            $this->io->tableVertical([
                'Environment',
                'Repository Root',
                'Configuration Root',
                'Ignored Files',
                'Disable Overwrites',
            ], ...[
                [sprintf('name=[<em>%s</>]', $environment)],
                [sprintf('path=[%s]', $repoDirectory)],
                [sprintf('path=[%s]', $confDirectory)],
                [sprintf('list=[%s] (%d)', implode(',', array_map(function (string $file) {
                    return sprintf('"%s"', $file);
                }, $listManualIgnore)) ?: '<none>', count($listManualIgnore))],
                [sprintf('flag=[%s]', $listNotOverwrite ? '<fg=green>true</>' : '<fg=red>false</>')],
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
