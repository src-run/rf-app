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
use Rf\AppBundle\Component\Console\Registry\ConfigurationRegistry;
use Rf\AppBundle\Component\Console\Runner\EnvFiles\FileCleanupRunner;
use Rf\AppBundle\Component\Console\Runner\EnvFiles\FileInstallRunner;
use Rf\AppBundle\Component\DependencyInjection\ParameterResolver;
use SR\Console\Output\Style\Style;
use SR\Console\Output\Style\StyleAwareTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EnvFilesCommand extends AbstractCommand
{
    use StyleAwareTrait;

    /**
     * @var FileCleanupRunner
     */
    private $fileCleanupRunner;

    /**
     * @var FileInstallRunner
     */
    private $fileInstallRunner;

    /**
     * @var ParameterResolver
     */
    private $parameterResolver;

    /**
     * @var InputParamResolver
     */
    private $inputParamResolver;

    /**
     * @param FileCleanupRunner $fileCleanupRunner
     * @param FileInstallRunner $fileInstallRunner
     * @param ParameterResolver $parameterResolver
     */
    public function __construct(FileCleanupRunner $fileCleanupRunner, FileInstallRunner $fileInstallRunner, ParameterResolver $parameterResolver)
    {
        parent::__construct();

        $this->fileCleanupRunner = $fileCleanupRunner;
        $this->fileInstallRunner = $fileInstallRunner;
        $this->parameterResolver = $parameterResolver;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Install environment configuration files into repository root for a given environment');
        $this->addArgument('environment-name', InputArgument::OPTIONAL,
            'Enviornment name to install', 'default');
        $this->addOption('repository-root', ['r'], InputOption::VALUE_REQUIRED,
            'Repository root directory path', '%kernel.root_dir%/../');
        $this->addOption('configuration-root', ['c'], InputOption::VALUE_REQUIRED,
            'Configuration root directory path', '%kernel.root_dir%/../.env/');
        $this->addOption('no-cleanup', ['u'], InputOption::VALUE_NONE,
            'Disable cleanup of repository dot files before install');
        $this->addOption('no-overwrite', ['o'], InputOption::VALUE_NONE,
            'Disable overwriting any existing file');
        $this->addOption('no-file', ['l'], InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Disable overwriting (ignore) specified file', []);
    }

    /**
     * @param ConfigurationRegistry $c
     *
     * @return int
     */
    protected function doExecute(ConfigurationRegistry $c): int
    {
        if ((true !== $c->get('no-cleanup') && 0 !== $return = $this->doFileCleanupRunner($c)) || 0 !== $return = $this->doFileInstallRunner($c)) {
            return $return;
        }

        return 0;
    }

    /**
     * @param ConfigurationRegistry $c
     *
     * @return int
     */
    private function doFileCleanupRunner(ConfigurationRegistry $c): int
    {
        $this->fileCleanupRunner->setStyle($this->io);
        $this->fileCleanupRunner->setRepositoryPath($c->get('repo-path'));
        $this->fileCleanupRunner->setIgnoredFiles($c->get('ignored-files'));
        $this->fileCleanupRunner->run();

        if (0 !== $result = $this->fileCleanupRunner->getResult()) {
            $this->io->critical('Encountered a failure while running file cleanup operation.');
        }

        return $result;
    }

    /**
     * @param ConfigurationRegistry $c
     *
     * @return int
     */
    private function doFileInstallRunner(ConfigurationRegistry $c): int
    {
        $this->fileInstallRunner->setStyle($this->io);
        $this->fileInstallRunner->setEnvironment($c->get('environment'));
        $this->fileInstallRunner->setRepositoryPath($c->get('repo-path'));
        $this->fileInstallRunner->setConfigurationPath($c->get('conf-path'));
        $this->fileInstallRunner->setIgnoredFiles($c->get('ignored-files'));
        $this->fileInstallRunner->setNoOverwrite($c->get('no-overwrite'));
        $this->fileInstallRunner->run();

        if (0 !== $result = $this->fileInstallRunner->getResult()) {
            $this->io->critical('Encountered a failure while running file install operation.');
        }

        return $result;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return ConfigurationRegistry
     */
    protected function initializeEnvironment(InputInterface $input, OutputInterface $output): ConfigurationRegistry
    {
        $this->io = new Style($input, $output);
        $this->inputParamResolver = new InputParamResolver($this->io, new OutputErrorHandler($this->io), $this->parameterResolver);

        return parent::initializeEnvironment($input, $output);
    }

    /**
     * @return ConfigurationRegistry
     */
    protected function initializeConfiguration(): ConfigurationRegistry
    {
        $c = new ConfigurationRegistry();
        $c->set('environment', $this->getArgumentEnvironment());
        $c->set('repo-path', $this->getOptionRepositoryPath());
        $c->set('conf-path', $this->getOptionConfigurationPath());
        $c->set('ignored-files', $this->getOptionIgnoredFiles());
        $c->set('no-cleanup', $this->io->getInput()->getOption('no-cleanup'));
        $c->set('no-overwrite', $this->io->getInput()->getOption('no-overwrite'));

        $this->writeConfiguration([
            'Install Environment',
            'Repository Root',
            'Configuration Root',
            'Ignore File(s)',
            'Cleanup Disabled',
            'Overwriting Disabled',
        ], [
            [sprintf('name=[<em>%s</>]', $c->get('environment'))],
            [sprintf('path=[%s]', $c->get('repo-path'))],
            [sprintf('path=[%s]', $c->get('conf-path'))],
            [sprintf('list=[%s] (%d)', implode(',', array_map(function (string $file) {
                return sprintf('"%s"', $file);
            }, $c->get('ignored-files'))) ?: '<none>', count($c->get('ignored-files')))],
            [sprintf('flag=[%s]', $c->get('no-cleanup') ? '<fg=green>true</>' : '<fg=red>false</>')],
            [sprintf('flag=[%s]', $c->get('no-overwrite') ? '<fg=green>true</>' : '<fg=red>false</>')],
        ]);

        return $c;
    }

    /**
     * @return string
     */
    private function getArgumentEnvironment(): string
    {
        if (false === $environment = $this->inputParamResolver->resolveArgument('environment-name')) {
            $this->io->critical('You must provide a valid environment name.');
            exit(255);

        }

        return $environment;
    }

    /**
     * @return string
     */
    private function getOptionRepositoryPath(): string
    {
        if (false === $realRepoPath = realpath($repoDirectory = $this->inputParamResolver->resolveOption('repository-root'))) {
            $this->io->critical(sprintf('Path "%s" does not exist.', $repoDirectory));
            exit(255);
        }

        return $realRepoPath;
    }

    /**
     * @return string
     */
    private function getOptionConfigurationPath(): string
    {
        if (false === $realConfPath = realpath($confDirectory = $this->inputParamResolver->resolveOption('configuration-root'))) {
            $this->io->critical(sprintf('Path "%s" does not exist.', $confDirectory));
            exit(255);
        }

        return $realConfPath;
    }

    /**
     * @return string[]
     */
    private function getOptionIgnoredFiles(): array
    {
        return array_map(function ($file) {
            return basename($file);
        }, $this->io->getInput()->getOption('no-file'));
    }
}
