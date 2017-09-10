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
use Rf\AppBundle\Component\Console\Runner\EnvFiles\FileInstallRunner;
use Rf\AppBundle\Component\Console\Runner\EnvFiles\FileCleanupRunner;
use SR\Console\Output\Style\Style;
use SR\Console\Output\Style\StyleAwareTrait;
use SR\Console\Output\Style\StyleInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    use StyleAwareTrait;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->initializeEnvironment($input, $output);

        if (!$this->writeContinueConfirmation()) {
            return 255;
        }

        $this->writeCompletionSummary($result = $this->doExecute($configuration));

        return $result ?? 255;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return ConfigurationRegistry
     */
    protected function initializeEnvironment(InputInterface $input, OutputInterface $output): ConfigurationRegistry
    {
        if (!$this->io instanceof StyleInterface) {
            $this->io = new Style($input, $output);
        }

        return $this->initializeConfiguration();
    }

    /**
     * @return ConfigurationRegistry
     */
    protected function initializeConfiguration(): ConfigurationRegistry
    {
        return new ConfigurationRegistry();
    }

    abstract protected function doExecute(ConfigurationRegistry $c): int;

    /**
     * @param array  $headers
     * @param array  $rows
     * @param string $description
     */
    protected function writeConfiguration(array $headers, array $rows, string $description = null): void
    {
        if ($this->io->isDebug()) {
            $this->io->comment(sprintf('%s:', $description ?: 'Resolved runtime configuration'));
        }

        if ($this->io->isDebug()) {
            $this->io->tableVertical($headers, ...$rows);
        }
    }

    /**
     * @param int $result
     */
    protected function writeCompletionSummary(int $result): void
    {
        if (0 === $result) {
            $this->io->success('Successfully completed all operations!');
        } else {
            $this->io->warning(sprintf('Exiting with a non-zero return value "%d".', $result));
        }
    }

    /**
     * @return bool
     */
    protected function writeContinueConfirmation(): bool
    {
        if ($this->io->isDebug() && !$this->io->confirm('Continue using this configuration?', true)) {
            $this->io->critical('Stopping due to user request.');

            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    protected function userRequestedExit(int $exitCode = 255): int
    {
        $this->io->info('The user initiated a manual script termination');

        return $exitCode;
    }
}
