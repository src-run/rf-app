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
     * @var ActionStepper
     */
    protected $actionStepper;

    /**
     * @param ActionStepper $actionStepper
     */
    public function __construct(ActionStepper $actionStepper)
    {
        parent::__construct();

        $this->actionStepper = $actionStepper;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initializeEnvironment($input, $output);

        if (!$this->writeContinueConfirmation()) {
            return 255;
        }

        $this->writeCompletionSummary($result = $this->actionStepper->run());

        return $result ?? 255;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initializeEnvironment(InputInterface $input, OutputInterface $output): void
    {
        if (!$this->io instanceof StyleInterface) {
            $this->io = new Style($input, $output);
        }

        $this->actionStepper->setStyle($this->io);
        $this->initializeActions($this->initializeConfiguration());
    }

    /**
     * @return AbstractRegistry
     */
    abstract protected function initializeConfiguration(): AbstractRegistry;

    /**
     * @param AbstractRegistry $c
     */
    abstract protected function initializeActions(AbstractRegistry $c): void;

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
     * @param int $exitCode
     *
     * @return int
     */
    protected function userRequestedExit(int $exitCode = 255): int
    {
        $this->io->info('The user initiated a manual script termination');

        return $exitCode;
    }

    /**
     * @param bool $state
     *
     * @return string
     */
    protected function getConfigurationFlagMarkup(bool $state): string
    {
        return sprintf('flag=[%s]', $state ? '<fg=green>enabled</>' : '<fg=red>disabled</>');
    }

    /**
     * @param array $list
     *
     * @return string
     */
    protected function getConfigurationListMarkup(array $list): string
    {
        return sprintf('list=[%s]%s', implode(',', $list) ?: '<none>', (count($list) === 0 ? '' : sprintf(' (%d)', count($list))));
    }
}
