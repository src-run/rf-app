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
use Rf\AppBundle\Component\Console\ParameterResolver;
use Rf\AppBundle\Component\Console\Registry\AbstractRegistry;
use Rf\AppBundle\Component\Console\Registry\ResolvableRegistry;
use Rf\AppBundle\Component\Console\Runner\EnvFiles\FileCleanupRunner;
use Rf\AppBundle\Component\Console\Runner\EnvFiles\FileInstallRunner;
use Rf\AppBundle\Component\DependencyInjection\ParameterResolver as DiResolver;
use SR\Console\Output\Style\StyleAwareTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class EnvironmentFilesCommand extends AbstractCommand
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
     * @var DiResolver
     */
    private $diResolver;

    /**
     * @param ActionStepper     $actionStepper
     * @param FileCleanupRunner $fileCleanupRunner
     * @param FileInstallRunner $fileInstallRunner
     * @param DiResolver        $diResolver
     */
    public function __construct(ActionStepper $actionStepper, FileCleanupRunner $fileCleanupRunner, FileInstallRunner $fileInstallRunner, DiResolver $diResolver)
    {
        parent::__construct($actionStepper);

        $this->fileCleanupRunner = $fileCleanupRunner;
        $this->fileInstallRunner = $fileInstallRunner;
        $this->diResolver = $diResolver;
    }

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
        $this->addOption('dry-run', ['d'], InputOption::VALUE_NONE,
            'Enable dry-run mode');
    }

    /**
     * @param AbstractRegistry $c
     *
     * @return int
     */
    private function doFileCleanupRunner(AbstractRegistry $c): int
    {
        $this->fileCleanupRunner->setStyle($this->io);
        $this->fileCleanupRunner->setDryRun($c->get('dry-run'));
        $this->fileCleanupRunner->setRepositoryPath($c->get('repo-path'));
        $this->fileCleanupRunner->setIgnoredFiles($c->get('ignored-files'));
        $this->fileCleanupRunner->run();

        return $this->fileCleanupRunner->getResult();
    }

    /**
     * @param AbstractRegistry $c
     *
     * @return int
     */
    private function doFileInstallRunner(AbstractRegistry $c): int
    {
        $this->fileInstallRunner->setStyle($this->io);
        $this->fileInstallRunner->setDryRun($c->get('dry-run'));
        $this->fileInstallRunner->setEnvironment($c->get('environment'));
        $this->fileInstallRunner->setRepositoryPath($c->get('repo-path'));
        $this->fileInstallRunner->setConfigurationPath($c->get('conf-path'));
        $this->fileInstallRunner->setIgnoredFiles($c->get('ignored-files'));
        $this->fileInstallRunner->setNoOverwrite($c->get('no-overwrite'));
        $this->fileInstallRunner->run();

        return $this->fileInstallRunner->getResult();
    }

    /**
     * @return AbstractRegistry
     */
    protected function initializeConfiguration(): AbstractRegistry
    {
        $c = new ResolvableRegistry(new ParameterResolver($this->io, $this->diResolver));
        $c->resolve('environment', 'environment-name');
        $c->resolveRealPath('repo-path', 'repository-root');
        $c->resolveRealPath('conf-path', 'configuration-root');
        $c->set('ignored-files', array_map(function ($file) {
            return basename($file);
        }, $this->io->getInput()->getOption('no-file')));
        $c->set('no-cleanup', $this->io->getInput()->getOption('no-cleanup'));
        $c->set('no-overwrite', $this->io->getInput()->getOption('no-overwrite'));
        $c->set('dry-run', $this->io->getInput()->getOption('dry-run'));

        $this->writeConfiguration([
            'Install Environment',
            'Repository Root',
            'Configuration Root',
            'Ignore File(s)',
            'Pre-Cleanup',
            'File Overwriting',
            'Dry Run Mode',
        ], [
            [sprintf('name=[<em>%s</>]', $c->get('environment'))],
            [sprintf('path=[%s]', $c->get('repo-path'))],
            [sprintf('path=[%s]', $c->get('conf-path'))],
            [$this->getConfigurationListMarkup($c->get('ignored-files'))],
            [$this->getConfigurationFlagMarkup(!$c->get('no-cleanup'))],
            [$this->getConfigurationFlagMarkup(!$c->get('no-overwrite'))],
            [$this->getConfigurationFlagMarkup($c->get('dry-run'))],
        ]);

        return $c;
    }

    /**
     * @param AbstractRegistry $c
     */
    protected function initializeActions(AbstractRegistry $c): void
    {
        $this->actionStepper->addActionClosure('file-cleanup', function () use ($c) {
            return $this->doFileCleanupRunner($c);
        })->setConditional(function () use ($c) {
            return false === $c->get('no-cleanup');
        })->setErrorString('Encountered a failure while running file cleanup operation.');

        $this->actionStepper->addActionClosure('file-install', function () use ($c) {
            return $this->doFileInstallRunner($c);
        })->setErrorString('Encountered a failure while running file install operation.');
    }
}
